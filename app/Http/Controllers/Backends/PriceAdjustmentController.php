<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\Session;
use App\Models\PriceAdjustment;
use App\Models\Room;
use App\Http\Requests\StorePriceAdjustmentRequest;
use App\Http\Requests\UpdatePriceAdjustmentRequest;

class PriceAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        if(!auth()->user()->can('view discount')){
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $query = PriceAdjustment::with('room');
            if ($request->has('room_id') && $request->room_id) {
                $query->where('room_id', $request->room_id);
            }

            if($request->has('discount_type') && $request->discount_type){
                $query->where('discount_type', $request->discount_type);
            }

            if($request->has('status') && $request->status){
                $query->where('status', $request->status);
            }

            $priceAdjustments = $query->get();

            return response()->json(['data' => $priceAdjustments]);
        }

        $rooms = Room::all();
        $usedRoomIds = PriceAdjustment::where('status', 'active')->pluck('room_id')->toArray();
        $availableRooms = Room::whereNotIn('id', $usedRoomIds)->get();
        return view('backends.price_adjustment.index', compact('rooms','availableRooms'));
    }

    public function edit($id)
    {
        $adjustment = PriceAdjustment::findOrFail($id);
        $rooms = Room::all();

        return view('backends.price_adjustment.edit', compact('adjustment', 'rooms'));
    }


    public function store(Request $request)
    {
        $priceAdjustment = PriceAdjustment::create($request->all());

        Session::flash('success', __('Price adjustment added successfully.'));
        return redirect()->route('price_adjustments.index');
    }

    public function show($id)
    {
        $priceAdjustment = PriceAdjustment::findOrFail($id);

        return view('backends.price_adjustment.show', compact('priceAdjustment'));
    }

    public function update(Request $request, $id)
    {
        $priceAdjustment = PriceAdjustment::findOrFail($id);
        $priceAdjustment->update($request->all());

        Session::flash('success', __('Price adjustment updated successfully.'));
        return redirect()->route('price_adjustments.index');
    }

    public function destroy($id)
    {
        try {
            $priceAdjustment = PriceAdjustment::findOrFail($id);
            $priceAdjustment->delete();

            Session::flash('success', __('Price adjustment deleted successfully.'));
        } catch (\Exception $e) {
            Session::flash('error', __('Failed to delete price adjustment.'));
        }

        return redirect()->route('price_adjustments.index');
    }
}
