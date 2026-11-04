<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        $month = $request->query('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $budgets = Budget::where('user_id', $user->id)->whereDate("month_start", $monthStart)->with('category:id,name,type,color')->orderBy('id', 'desc')->get();

        $data = $budgets->map(function ($budget) use ($user, $monthStart, $monthEnd) {
            $spent = (float)Transaction::where('user_id', $user->id)->where('category_id', $budget->category_id)->where('type', 'expense')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');

            $amount = (float)$budget->amount;
            $remaining = $amount - $spent;
            $percent = $amount > 0 ? round(($spent / $amount) * 100, 1) : 0;

            if ($percent <= 80) {
                $status = 'under';
            } else if ($percent <= 100) {
                $status = 'near';
            } else {
                $status = 'over';
            }

            return [
                'id' => $budget->id,
                'category_id' => $budget->category_id,
                'amount' => $amount,
                'month_start' => $monthStart,
                'spent' => $spent,
                'remaining' => $remaining,
                'percent' => $percent,
                'status' => $status,
                'category' => $budget->category,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Budgets fetched successfully",
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'month_start' => 'date|required',
        ]);

        if ($validated['amount'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Budget must be greater than Zero',
            ], 403);
        }

        $user = auth('sanctum')->user();

        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', true);
        })->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to user"
            ], 403);
        }

        if ($category->type !== 'expense') {
            return response()->json([
                'success' => false,
                'message' => "Budgets can only be set for expense categories",
            ], 422);
        }

        $monthStart = Carbon::parse($validated['month_start'])->startOfMonth()->toDateString();

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'month_start' => $monthStart,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget is added successfully',
            'data' => $budget,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        $user = auth('sanctum')->user();

        if ($budget->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission to view this budget"
            ], 403);
        }

        return response()->json([
            "success" => true,
            "message" => "Budget fetched successfully",
            "data" => $budget->load('category:id,name,type,color'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'category_id' => "required|exists:categories,id",
            'amount' => "required|numeric|min:0.01",
            'month_start' => "required|date"
        ]);

        $user = auth('sanctum')->user();

        if ($user->id !== $budget->user_id) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission for this request"
            ], 403);
        }

        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', true);
        })->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to user"
            ], 403);
        }

        if ($category->type !== 'expense') {
            return response()->json([
                'success' => false,
                'message' => "Budgets can only be set for expense categories",
            ], 422);
        }

        $validated['month_start'] = Carbon::parse($validated['month_start'])->startOfMonth()->toDateString();

        $budget->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Budget updated successfully",
            'data' => $budget->fresh()->load('user:id,name', 'category:id,name,type'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $user = auth('sanctum')->user();

        if ($budget->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission for this request",
            ], 403);
        }

        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => "Budget deleted successfully"
        ], 200);
    }
}
