<?php

namespace App\Http\Controllers\Backends;

use Exception;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Amenity;
use App\Models\Payment;
use App\Models\UtilityRate;
use App\Models\MonthlyUsage;
use App\Models\UserContract;
use App\Http\Requests\Request;
use App\Models\PaymentAmenity;
use App\Models\PaymentUtility;
use App\Models\PriceAdjustment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;

class PaymentController extends Controller
{
    /**
     * Reuturn payment list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = Payment::with(['userContract.user', 'userContract.room']);

            if ($request->has('user_id') && $request->user_id) {
                $query->whereHas('userContract.user', function ($q) use ($request) {
                    $q->where('id', $request->user_id);
                });
            }
            if ($request->has('payment_status') && $request->payment_status) {
                $query->where('payment_status', $request->payment_status);
            }
            if ($request->has('payment_type') && $request->payment_type) {
                $query->where('type', $request->payment_type);
            }
            if ($request->has('year_paid') && $request->year_paid) {
                $query->where('year_paid', $request->year_paid);
            }

            $payments       = $query->latest()->get();
            $totalPayment   = $payments->sum('total_amount');
            $amountPaid     = $payments->sum('amount');
            $totalDueAmount = $payments->sum('total_due_amount');

            return response()->json([
                'data'             => $payments,
                'total_payment'    => $totalPayment,
                'amount_paid'      => $amountPaid,
                'total_due_amount' => $totalDueAmount,
            ]);
        }

        $rooms = Room::all();
        $users = User::all();
        $payment_using_for_modals = Payment::all();
        $contracts = UserContract::all();
        return view('backends.payment.index', compact('rooms', 'payment_using_for_modals', 'contracts', 'users'));
    }

    /**
     * Get the total room price for a specific contract with monthly utility data
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalRoomPrice($id)
    {
        $monthPaid = request()->input('month_paid');
        $yearPaid = request()->input('year_paid');

        $contract = UserContract::with([
            'room.roomPricing' => function ($query) {
                $query->latest()->first();
            },
            'room.amenities'
        ])->findOrFail($id);

        $room_price_befor_discount = $contract->room->roomPricing->first()?->base_price ?? 0;
        $basePrice = $contract->room->roomPricing->first()?->base_price ?? 0;
        $amenityIds = DB::table('room_amenity')
            ->whereIn('room_id', [$contract->room_id])
            ->pluck('amenity_id')
            ->toArray();

        $amenity_prices = Amenity::whereIn('id', $amenityIds)->sum('additional_price');
        $total_room_price_before_discount = $room_price_befor_discount + $amenity_prices;
        $amenities = Amenity::whereIn('id', $amenityIds)->get(['id', 'name', 'additional_price']);
        $discount = PriceAdjustment::where('room_id', $contract->room_id)->where('status', 'active')->first();

        if (@$discount->discount_type == 'amount') {
            $basePrice = $basePrice - $discount->discount_value;
        } elseif (@$discount->discount_type == 'percentage') {
            $basePrice = $basePrice - ($basePrice * $discount->discount_value / 100);
        } else {
            $basePrice;
        }

        // Get utility usage for specific month and year if provided
        $utilityQuery = MonthlyUsage::where('room_id', $contract->room_id);

        if ($monthPaid && $yearPaid) {
            $utilityQuery->where('month', $monthPaid)
                ->where('year', $yearPaid);
        }

        $utility = $utilityQuery->latest()->first();
        $utilityUsage = [];
        $totalCost = 0;

        if ($utility) {
            foreach ($utility->utilityTypes as $type) {
                $utilityUsage[] = [
                    'utility_type_id' => $type->id,
                    'utility_type' => $type->type,
                    'usage' => $type->pivot->usage,
                ];
            }
        } else {
            $utilityUsage[] = ['message' => 'not found'];
        }

        $utilityRates = UtilityRate::where('status', 1)->get();
        foreach ($utilityUsage as $usageData) {
            if (isset($usageData['utility_type_id'])) {
                $rate = $utilityRates->firstWhere('utility_type_id', $usageData['utility_type_id']);
                if ($rate) {
                    $totalCost += $usageData['usage'] * $rate->rate_per_unit;
                }
            }
        }

        $totalPrice = $basePrice + $amenity_prices;
        $totalRoomPrice = $totalCost + $totalPrice;

        return response()->json([
            'price' => $totalRoomPrice,
            'discount' => $discount,
            'amenities' => $amenities,
            'amenity_prices' => $amenity_prices,
            'utilityUsage' => $utilityUsage,
            'totalCost' => $totalCost,
            'utilityRates' => $utilityRates,
            'total_room_price_before_discount' => $total_room_price_before_discount,
            'room_price' => $room_price_befor_discount,
        ]);
    }

    /**
     * Get the total room price for a specific contract
     * @param int $contractId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomPrice($contractId)
    {
        $contract = UserContract::with([
            'room.roomPricing' => function ($query) {
                $query->latest()->first();
            },
            'room.amenities'
        ])->findOrFail($contractId);

        $basePrice  = $contract->room->roomPricing->first()?->base_price ?? 0;
        $amenityIds = DB::table('room_amenity')
            ->whereIn('room_id', [$contract->room_id])
            ->pluck('amenity_id')
            ->toArray();

        $amenity_prices = Amenity::whereIn('id', $amenityIds)->sum('additional_price');
        $discount       = PriceAdjustment::where('room_id', $contract->room_id)->where('status', 'active')->first();

        if (@$discount->discount_type == 'amount') {
            $basePrice = $basePrice - $discount->discount_value;
        } elseif (@$discount->discount_type == 'percentage') {
            $basePrice = $basePrice - ($basePrice * $discount->discount_value / 100);
        } else {
            $basePrice;
        }
        $totalPrice      = $basePrice + $amenity_prices;
        $additionalPrice = $contract->room->amenities->sum('additional_price');
        $totalPrice      = $basePrice + $additionalPrice;
        return response()->json([
            'price' => $totalPrice
        ]);
    }

    /**
     * Get the total utility amount for a specific contract
     * @param int $contractId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUtilityAmount($contractId)
    {
        $contract = UserContract::with([
            'room.monthlyUsages.details.utilityType.utilityrates'
        ])->findOrFail($contractId);

        $monthlyUsages = $contract->room->monthlyUsages;

        if (!$monthlyUsages || $monthlyUsages->isEmpty()) {
            return response()->json([
                'price'   => 0,
                'message' => 'No monthly usage details found.'
            ]);
        }

        $monthlyUsageDetails = $monthlyUsages->flatMap(function ($usage) {
            return $usage->details;
        });

        if ($monthlyUsageDetails->isEmpty()) {
            return response()->json([
                'price'   => 0,
                'message' => 'No monthly usage details found.'
            ]);
        }

        $totalUtilityPrice = $monthlyUsageDetails->reduce(function ($carry, $detail) {
            $activeRate = $detail->utilityType->utilityrates->where('status', '1')->first();

            if ($activeRate) {
                $ratePerUnit = $activeRate->rate_per_unit ?? 0;
                return $carry + ($detail->usage * $ratePerUnit);
            }

            return $carry;
        }, 0);

        return response()->json([
            'price' => $totalUtilityPrice
        ]);
    }

    /**
     * Get the total amount for a specific contract
     * @param int $contractId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalAmount($contractId)
    {
        $contract = UserContract::with([
            'room.roomPricing' => function ($query) {
                $query->latest()->first();
            },
            'room.amenities',
            'room.monthlyUsages.details.utilityType.utilityrates'
        ])->findOrFail($contractId);

        $basePrice       = $contract->room->roomPricing->first()?->base_price ?? 0;
        $additionalPrice = $contract->room->amenities->sum('additional_price');
        $roomPrice       = $basePrice + $additionalPrice;

        $monthlyUsages   = $contract->room->monthlyUsages;
        $utilityPrice    = $monthlyUsages?->flatMap(fn($usage) => $usage->details)
            ->reduce(function ($carry, $detail) {
                $activeRate = $detail->utilityType->utilityrates->where('status', '1')->first();
                return $carry + (($detail->usage ?? 0) * ($activeRate?->rate_per_unit ?? 0));
            }, 0) ?? 0;

        $totalAmount = $roomPrice + $utilityPrice;

        return response()->json([
            'totalAmount'  => $totalAmount,
            'roomPrice'    => $roomPrice,
            'utilityPrice' => $utilityPrice
        ]);
    }

    /**
     * Return form for creating a new payment
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $payments = Payment::orderBy('id', 'desc')->get();
        $contracts = UserContract::latest()->where('status', 'active')->get();
        return view('backends.payment.create_payment', compact('payments', 'contracts'));
    }

    /**
     * Store payment
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $totalPayments = Payment::count();
            $invoiceNo = 'INV' . str_pad($totalPayments + 1, 5, '0', STR_PAD_LEFT);
            $data = [
                'user_contract_id'      => $request->user_contract_id,
                'room_price'            => $request->room_price,
                'total_amount_amenity'  => $request->total_amount_amenity,
                'total_utility_amount'  => $request->total_utility_amount,
                'total_amount'          => $request->total_amount,
                'amount'                => $request->amount,
                'payment_date'          => $request->payment_date,
                'month_paid'            => $request->month_paid,
                'year_paid'             => $request->year_paid,
                'type'                  => $request->type,
                'total_discount'        => $request->discount_value,
                'discount_type'         => $request->discount_type,
                'invoice_no'            => $invoiceNo,
            ];

            $total_amount = $request->total_amount;
            $total_paid   = $request->amount;
            $total_due    = ($total_amount > $total_paid) ? ($total_amount - $total_paid) : 0;
            $status       = ($total_amount <= $total_paid) ? 'completed' : 'partial';

            $data['total_due_amount'] = $total_due;
            $data['payment_status'] = $status;

            if ($request->has('form_date') && $request->has('to_date')) {
                $data['start_date'] = $request->form_date;
                $data['end_date'] = $request->to_date;
            }
            $payment = Payment::create($data);

            if ($request->has('amenity_ids') && is_array($request->amenity_ids)) {
                foreach ($request->amenity_ids as $index => $amenityId) {
                    PaymentAmenity::create([
                        'payment_id'     => $payment->id,
                        'amenity_id'     => $amenityId,
                        'amenity_price'  => $request->amenity_prices[$index] ?? 0,
                    ]);
                }
            }

            if ($request->has('utility_ids') && is_array($request->utility_ids)) {
                foreach ($request->utility_ids as $index => $utilityId) {
                    $rateValue = isset($request->utility_rates[$index])
                        ? $this->formatCurrencyToDecimal($request->utility_rates[$index])
                        : 0;

                    $totalValue = isset($request->utility_totals[$index])
                        ? $this->formatCurrencyToDecimal($request->utility_totals[$index])
                        : 0;

                    PaymentUtility::create([
                        'payment_id'        => $payment->id,
                        'utility_id'        => $utilityId,
                        'usage'             => $request->utility_usages[$index] ?? 0,
                        'rate_per_unit'     => $rateValue,
                        'total_amount'      => $totalValue,
                        'month_paid'        => $request->month_paid,
                        'year_paid'         => $request->year_paid,
                    ]);
                }
            }

            if ($request->type === 'all_paid') {
                $userContract = UserContract::find($request->user_contract_id);
                if ($userContract) {
                    $payment->update(['payment_status' => 'completed']);
                }
            }

            DB::commit();
            Session::flash('success', __('Payment created successfully.'));
            return redirect()->route('payments.index');
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('error', __('Failed to create payment: ') . $e->getMessage());
            return redirect()->route('payments.index');
        }
    }

    /**
     * Format currency string to decimal
     * @param string $value
     * @return float
     */
    private function formatCurrencyToDecimal($value)
    {
        if (is_null($value) || empty($value)) {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleanValue = preg_replace('/[^0-9.]/', '', $value);

        if (empty($cleanValue)) {
            return 0;
        }

        return (float) $cleanValue;
    }

    /**
     * Get the utility by payment
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUitilityPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $contract = UserContract::where('room_id', $payment->userContract->room_id)->first();
        return view('backends.payment.partial.payment_utility', compact('payment', 'contract'));
    }

    /**
     * Get the utility by payment
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function advanceUtilityPayment($id)
    {
        $month_paid = request()->input('month_paid');
        $contract = UserContract::with([
            'room.roomPricing' => function ($query) {
                $query->latest()->first();
            },
            'room.amenities'
        ])->findOrFail($id);
        $utility = MonthlyUsage::where('room_id', $contract->room_id)->where('month', $month_paid)->latest()->first();
        $utilityUsage = [];
        $totalCost = 0;

        if ($utility) {
            foreach ($utility->utilityTypes as $type) {
                $utilityUsage[] = [
                    'utility_type_id' => $type->id,
                    'utility_type' => $type->type,
                    'usage' => $type->pivot->usage,
                ];
            }
        } else {
            $utilityUsage[] = ['message' => 'not found'];
        }
        $utilityRates = UtilityRate::where('status', 1)->get();
        foreach ($utilityUsage as $usageData) {
            if (isset($usageData['utility_type_id'])) {
                $rate = $utilityRates->firstWhere('utility_type_id', $usageData['utility_type_id']);
                if ($rate) {
                    $totalCost += $usageData['usage'] * $rate->rate_per_unit;
                }
            }
        }

        return response()->json([
            'utility_usage' => $utilityUsage,
            'total_cost' => $totalCost,
            'utilityRates' => $utilityRates,
        ]);
    }

    /**
     * Store advance utility payment
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAdvanceUtilityPayment(Request $request)
    {
        try {
            $payment_id     = $request->input('payment_id');
            $month_paid     = $request->input('month_paid');
            $year_paid      = $request->input('year_paid');
            $utilityIds     = $request->input('utility_ids');
            $utilityUsages  = $request->input('utility_usages');
            $utilityRates   = $request->input('utility_rates');
            $utilityTotals  = $request->input('utility_totals');

            $payment = Payment::find($payment_id);
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'msg' => __('Payment not found'),
                ], 404);
            }

            $start_date = Carbon::parse($payment->start_date);
            $end_date   = Carbon::parse($payment->end_date);

            foreach ($utilityIds as $index => $utilityId) {
                $utilityDate = Carbon::create($year_paid, $month_paid, 1);
                if ($utilityDate->lt($start_date) || $utilityDate->gt($end_date)) {
                    return response()->json([
                        'success' => false,
                        'msg' => __('The utility payment date is outside the allowed payment period.')
                    ], 422);
                }
                $exists = PaymentUtility::where('payment_id', $payment_id)
                    ->where('utility_id', $utilityId)
                    ->where('month_paid', $month_paid)
                    ->where('year_paid', $year_paid)
                    ->exists();
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'msg' => __('The utility payment of this month is paid already.')
                    ], 422);
                }
                $rateValue = isset($utilityRates[$index])
                    ? $this->formatCurrencyToDecimal($utilityRates[$index])
                    : 0;

                $totalValue = isset($utilityTotals[$index])
                    ? $this->formatCurrencyToDecimal($utilityTotals[$index])
                    : 0;

                PaymentUtility::create([
                    'payment_id'        => $payment_id,
                    'utility_id'        => $utilityId,
                    'usage'             => $utilityUsages[$index],
                    'rate_per_unit'     => $rateValue,
                    'total_amount'      => $totalValue,
                    'month_paid'        => $month_paid,
                    'year_paid'         => $year_paid,
                ]);

                $payment->update([
                    'total_utility_amount' => $payment->total_utility_amount + $totalValue,
                    'total_amount'         => $payment->total_amount + $totalValue,
                ]);
            }
            return response()->json([
                'success' => true,
                'msg' => __('Utility payment added successfully.')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('Something went wrong: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete utility advance payment
     * @param Request $payment_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUtilityAdvancePayment(Request $request, $payment_id)
    {
        try {
            $payment_id = $payment_id ?? $request->input('payment_id');
            $month_paid = $request->input('month_paid');
            $year_paid  = $request->input('year_paid');
            $paymentUtilities = PaymentUtility::where('payment_id', $payment_id)
                ->where('month_paid', $month_paid)
                ->where('year_paid', $year_paid)
                ->get();
            if ($paymentUtilities->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Utility payments not found'], 404);
            }

            $payment = Payment::find($payment_id);

            if (!$payment) {
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }
            $totalUtilityAmount = $paymentUtilities->sum('total_amount');

            $payment->total_utility_amount -= $totalUtilityAmount;
            $payment->total_amount -= $totalUtilityAmount;
            $payment->save();

            PaymentUtility::where('payment_id', $payment_id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Utility payments deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    /**
     * Return edit payment form
     * @param Payment $payment
     * @return \Illuminate\view\View
     */
    public function edit(Payment $payment)
    {
        $contracts = UserContract::all();
        $rooms     = Room::latest()->where('status', 'active')->get();
        $users     = User::all();

        if (request()->ajax()) {
            return view('backends.payment.modal.edit_payment', compact('payment', 'contracts', 'rooms', 'users'));
        }

        return view('backends.payment.edit_payment', compact('payment', 'contracts', 'rooms', 'users'));
    }

    /**
     * Handle due payment update
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDueAmount(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            $befor_paid_amount = $request->input('befor_paid_amount', 0);
            $total_amount_paid = $request->input('total_amount_paid', $payment->total_amount);
            $paid_due_amount   = $request->input('paid_due_amount', 0);
            $update_due_amount = $befor_paid_amount + $paid_due_amount;
            $last_due_amount   = $total_amount_paid - $update_due_amount;

            $status = ($update_due_amount >= $total_amount_paid) ? 'completed' : 'partial';
            $type   = ($update_due_amount >= $total_amount_paid) ? 'all_paid' : $request->input('type');

            $payment->update([
                'amount'           => $update_due_amount,
                'total_due_amount' => $last_due_amount,
                'payment_status'   => $status,
                'type'             => $type,
                'payment_date'     => now(),
            ]);
            Session::flash('success', __('Payment update successfully.'));
            return redirect()->route('payments.index');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update payment: ') . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update payment
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            $data = [
                'user_contract_id'      => $request->user_contract_id ?? $payment->user_contract_id,
                'month_paid'            => $request->month_paid ?? $payment->month_paid,
                'year_paid'             => $request->year_paid ?? $payment->year_paid,
                'room_price'            => $request->room_price ?? $payment->room_price,
                'total_discount'        => $request->total_discount ?? $payment->total_discount,
                'discount_type'         => $request->discount_type ?? $payment->discount_type,
                'total_amount_amenity'  => $request->total_amount_amenity ?? $payment->total_amount_amenity,
                'total_utility_amount'  => $request->total_utility_amount ?? $payment->total_utility_amount,
                'total_amount'          => $request->total_amount ?? $payment->total_amount,
                'amount'                => $request->amount ?? $payment->amount,
                'type'                  => $request->type ?? $payment->type,
                'payment_date'          => $request->payment_date ?? $payment->payment_date
            ];

            // Calculate payment status and due amount
            if ($request->type != $payment->type) {
                $total_amount = $request->total_amount;
                $total_paid   = $request->amount;
                $total_due    = ($total_amount > $total_paid) ? ($total_amount - $total_paid) : 0;
                $status       = ($total_amount <= $total_paid) ? 'completed' : 'partial';

                $data['total_due_amount'] = $total_due;
                $data['payment_status']   = $status;
            } else {
                $total_amount = $payment->total_amount;
            }

            $payment->update($data);

            if ($request->has('amenity_data') && is_array($request->amenity_data)) {
                foreach ($request->amenity_data as $amenityId => $amenityData) {
                    $amenityPayment = PaymentAmenity::find($amenityId);
                    if ($amenityPayment && $amenityPayment->payment_id == $payment->id) {
                        $amenityPayment->update([
                            'amenity_price' => $amenityData['price'] ?? $amenityPayment->amenity_price,
                        ]);
                    }
                }
            }

            if ($request->has('utility_data') && is_array($request->utility_data)) {
                foreach ($request->utility_data as $utilityId => $utilityData) {
                    $utilityPayment = PaymentUtility::where('payment_id', $payment->id)
                        ->where('utility_id', $utilityId)
                        ->first();

                    if ($utilityPayment) {
                        $utilityPayment->update([
                            'usage' => $utilityData['usage'] ?? $utilityPayment->usage,
                            'rate_per_unit' => $utilityData['rate'] ?? $utilityPayment->rate_per_unit,
                            'total_amount' => $utilityData['total'] ?? $utilityPayment->total_amount,
                        ]);
                    }
                }
            }

            Session::flash('success', __('Payment updated successfully.'));
            return redirect()->route('payments.index');
        } catch (Exception $e) {
            Session::flash('error', __('Failed to update payment: ') . $e->getMessage());
            return redirect()->route('payments.index');
        }
    }
    /**
     * Delete payment
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Payment $payment)
    {
        try {
            $payment->paymentamenities()->delete();
            $payment->paymentutilities()->delete();
            $payment->delete();

            Session::flash('success', __('Payment deleted successfully.'));
        } catch (Exception $e) {
            Session::flash('error', __('Failed to delete payment.'));
        }

        return redirect()->route('payments.index');
    }
}
