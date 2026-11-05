<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
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

        $budgets = Budget::where('user_id', $user->id)->whereDate("month_start", $monthStart)->with('account:id,name,type')->orderBy('id', 'desc')->get();

        $data = $budgets->map(function ($budget) use ($user, $monthStart, $monthEnd) {
            $query =  Transaction::where('user_id', $user->id)->where('type', 'expense')->whereBetween('transaction_date', [$monthStart, $monthEnd]);

            if ($budget->type === 'account') {
                $query->where('account_id', $budget->account_id);
            }

            $spent = (float)$query->sum('amount');

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
                'account_id' => $budget->account_id,
                'type' => $budget->type,
                'amount' => $amount,
                'month_start' => $monthStart,
                'spent' => $spent,
                'remaining' => $remaining,
                'percent' => $percent,
                'status' => $status,
                'account' => $budget->account,
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
            'type' => 'required|in:account,overall',
            'account_id' => 'required_if:type,account|nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'month_start' => 'date|required',
        ]);

        $user = auth('sanctum')->user();
        $monthStart = Carbon::parse($validated['month_start'])->startOfMonth()->toDateString();
        $accountId = $validated['type'] === 'overall' ? null : $validated['account_id'];

        $exists = Budget::where('user_id', $user->id)->where('type', $validated['type'])->whereDate('month_start', $monthStart)->when($validated['type'] === 'account', fn($q) => $q->where('account_id', $accountId))->when($validated['type'] === 'overall', fn($q) => $q->where('type', 'overall'))->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Budget already exists, update existing one',
            ], 422);
        }

        if ($validated['amount'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Budget must be greater than Zero',
            ], 403);
        }

        if ($validated['type'] === 'account') {
            $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->first();
            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => "Account doesn't belongs to user"
                ], 403);
            }
        }

        $budget = Budget::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'account_id' => $accountId,
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
            "data" => $budget->load('account:id,name,type'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'type' => 'required|in:account,overall',
            'account_id' => 'required_if:type,account|nullable|exists:accounts,id',
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

        if ($validated['type'] === 'account') {
            $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => "Account doesn't belongs to user"
                ], 403);
            }
        }

        $validated['month_start'] = Carbon::parse($validated['month_start'])->startOfMonth()->toDateString();

        $validated['account_id'] = $validated['type'] === 'overall' ? null : $validated['account_id'];

        $exists = Budget::where('user_id', $user->id)->where('id', '!=', $budget->id)->where('type', $validated['type'])->whereDate('month_start', $validated['month_start'])->when($validated['type'] === 'account', fn($q) => $q->where('account_id', $validated['account_id']))->when($validated['type'] === 'overall', fn($q) => $q->where('type', 'overall'))->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Budget already exists, update existing one',
            ], 422);
        }

        $budget->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Budget updated successfully",
            'data' => $budget->fresh()->load('user:id,name', 'account:id,name,type'),
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
