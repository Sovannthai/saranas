<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UtilityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!auth()->user()->can('view utilitytype')){
            abort(403,'Unauthorized action.');
        }
        $utilityTypes = UtilityType::orderBy('id','asc')->get();
        return view('backends.utilitie_type.index', compact('utilityTypes'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'type' => 'required|unique:utility_types|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            UtilityType::create($request->all());
            
            $utilityTypes = UtilityType::orderBy('id','asc')->get();
            $view = view('backends.utilitie_type.utility_type_list', compact('utilityTypes'))->render();
            
            return response()->json([
                'status' => 'success',
                'message' => __('Utility Type added successfully.'),
                'html' => $view
            ]);
        }

        $request->validate([
            'type' => 'required|unique:utility_types|max:50',
        ]);

        UtilityType::create($request->all());

        Session::flash('success', __('Utility Type added successfully.'));
        return redirect()->route('utilities_type.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $utilityType = UtilityType::findOrFail($id);
            $validationRules = [
                'type' => 'required|max:50|unique:utility_types,type,'.$id,
            ];
            
            if ($request->ajax()) {
                $validator = Validator::make($request->all(), $validationRules);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                $utilityType->update($request->all());
                
                $utilityTypes = UtilityType::orderBy('id','asc')->get();
                $view = view('backends.utilitie_type.utility_type_list', compact('utilityTypes'))->render();
                
                return response()->json([
                    'status' => 'success',
                    'message' => __('Utility Type updated successfully.'),
                    'html' => $view
                ]);
            }
            
            // For regular form submission
            $request->validate($validationRules);
            $utilityType->update($request->all());

            Session::flash('success', __('Utility Type updated successfully.'));
            return redirect()->route('utilities_type.index');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Failed to update Utility Type.')
                ], 500);
            }
            
            Session::flash('error', __('Failed to update Utility Type.'));
            return redirect()->route('utilities_type.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $utilityType = UtilityType::findOrFail($id);
            $existingUtility = $utilityType->utilityrates()->count();
            if ($existingUtility > 0) {
                Session::flash('error', __('This Utility Type has utilities. Please delete them first.'));
                return redirect()->route('utilities_type.index');
            }
            $utilityType->delete();

            Session::flash('success', __('Utility Type deleted successfully.'));
        } catch (\Exception $e) {
            Session::flash('error', __('Failed to delete Utility Type.'));
        }

        return redirect()->route('utilities_type.index');
    }
}
