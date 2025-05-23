<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\ExpenseCategory;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        if(!auth()->user()->can('view expense category')){
            abort(403,'Unauthorized action.');
        }

        $expenseCategories = ExpenseCategory::all();

        return view('backends.expense_category.index', compact('expenseCategories'));
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        ExpenseCategory::create($request->validated());

        Session::flash('success', __('Expense category added successfully.'));

        return redirect()->route('expense_categories.index');
    }


    public function update(UpdateExpenseCategoryRequest $request, $id)
    {
        $expenseCategory = ExpenseCategory::findOrFail($id);

        $expenseCategory->update($request->validated());

        Session::flash('success', __('Expense category updated successfully.'));

        return redirect()->route('expense_categories.index');
    }

    public function destroy($id)
    {
        try {
            $expenseCategory   = ExpenseCategory::findOrFail($id);
            $expenseCategories = $expenseCategory->transactions()->count();
            if ($expenseCategories > 0) {
                $output = [
                    'error'=> ('This expense category has transactions. Please delete them first.'),
                ];
                return redirect()->route('expense_categories.index')->with($output);
            }
            $expenseCategory->delete();

            Session::flash('success', __('Expense category deleted successfully.'));
        } catch (\Exception $e) {
            Session::flash('error', __('Failed to delete expense category.'));
        }

        return redirect()->route('expense_categories.index');
    }
}
