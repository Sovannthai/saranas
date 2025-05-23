<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Room;
use App\Models\UtilityType;
use App\Models\MonthlyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreMonthlyUsageRequest;
use App\Http\Requests\UpdateMonthlyUsageRequest;
use App\Models\MonthlyUsageDetail;
use App\Models\Payment;

class MonthlyUsageController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        $monthlyUsages = MonthlyUsage::with(['room', 'utilityTypes'])->get();
        return view('backends.monthly_usages.index', compact('rooms', 'monthlyUsages'));
    }

    public function show($roomId)
    {
        if (!auth()->user()->can('view usagedetails')) {
            abort(403, 'Unauthorized action.');
        }

        $room = Room::findOrFail($roomId);
        $monthlyUsages = MonthlyUsage::with('utilityTypes')
            ->where('room_id', $roomId)
            ->get();
        // dd($monthlyUsages);
        $utilityTypes = Cache::remember('utility_types', now()->addHours(1), function () {
            return UtilityType::all();
        });

        $rooms = Cache::remember('rooms', now()->addHours(1), function () {
            return Room::all();
        });
        return view('backends.monthly_usages.show', compact('room', 'monthlyUsages', 'utilityTypes', 'rooms'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:9999',
            'utility_type_id' => 'required|array',
            'utility_type_id.*' => 'required|exists:utility_types,id',
            'usage' => 'required|array',
            'usage.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $monthlyUsage = MonthlyUsage::create([
                    'room_id' => $validated['room_id'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ]);

                foreach ($validated['utility_type_id'] as $index => $utilityTypeId) {
                    $monthlyUsage->utilityTypes()->attach($utilityTypeId, [
                        'usage' => $validated['usage'][$index],
                    ]);
                }
            });

            Session::flash('success', __('Monthly usage added successfully.'));
        } catch (Exception $e) {
            dd($e);
            Log::error('Failed to store monthly usage: ' . $e->getMessage());
            Session::flash('error', __('Failed to add monthly usage.'));
        }

        return redirect()->route('monthly_usages.show', $validated['room_id']);
    }

    public function update(Request $request, MonthlyUsage $monthlyUsage)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:9999',
            'utility_type_id' => 'required|array',
            'utility_type_id.*' => 'required|exists:utility_types,id',
            'usage' => 'required|array',
            'usage.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($monthlyUsage, $validated) {
                $monthlyUsage->update([
                    'room_id' => $validated['room_id'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ]);
                $pivotData = [];
                foreach ($validated['utility_type_id'] as $index => $utilityTypeId) {
                    $pivotData[$utilityTypeId] = ['usage' => $validated['usage'][$index]];
                }

                $monthlyUsage->utilityTypes()->sync($pivotData);
            });

            Session::flash('success', __('Monthly usage updated successfully.'));
        } catch (Exception $e) {
            Log::error('Failed to update monthly usage: ' . $e->getMessage());
            Session::flash('error', __('Failed to update monthly usage.'));
        }

        return redirect()->route('monthly_usages.show', $monthlyUsage->room_id);
    }

    public function destroy(MonthlyUsage $monthlyUsage)
    {
        try {
            $roomId = $monthlyUsage->room_id;
            $existingContract = $monthlyUsage->room->userContracts()->where('status', 'inactive')->first();
            if ($existingContract != null) {
                Session::flash('error', __('Cannot delete monthly usage with Inactive user contracts.'));
                return redirect()->route('monthly_usages.show', $roomId);
            }

            $contract = $monthlyUsage->room->userContracts()->where('status', 'active')->first();
            $monthlyUsageDetails = MonthlyUsageDetail::where('monthly_usage_id', $monthlyUsage->id)->get();

            $utilityTypeIds = $monthlyUsageDetails->pluck('utility_type_id')->toArray();
            $existingUsages = Payment::where('user_contract_id', $contract->id)
                ->whereHas('paymentutilities', function ($query) use ($utilityTypeIds) {
                    $query->whereIn('utility_id', $utilityTypeIds);
                })
                ->where('month_paid', $monthlyUsage->month)
                ->where('year_paid', $monthlyUsage->year)
                ->count();
            if ($existingUsages > 0) {
                Session::flash('error', __('Cannot delete monthly usage with existing payment records.'));
                return redirect()->route('monthly_usages.show', $roomId);
            }
            if ($existingUsages != 0) {
                Session::flash('error', __('Cannot delete monthly usage with existing recorde.'));
                return redirect()->route('monthly_usages.show', $roomId);
            }

            $monthlyUsage->delete();
            Session::flash('success', __('Monthly usage deleted successfully.'));
        } catch (\Exception $e) {
            dd($e);
            Session::flash('error', __('Failed to delete monthly usage.'));
        }
        return redirect()->route('monthly_usages.show', $roomId);
    }
}
