<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        // 1. Month
        $month = $request->query('month', now()->format('Y-m'));
        $month = substr($month, 0, 7);
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        // 2. Account filter
        $accountId = $request->query('account_id', 'all');
        if ($accountId !== 'all') {
            $owns = Account::where('id', $accountId)->where('user_id', $user->id)->exists();
            if (!$owns) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        // 3. Totals (each is its own query)
        $incomeTotalQuery = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
        if ($accountId !== 'all') {
            $incomeTotalQuery->where('account_id', $accountId);
        }
        $totalIncome = (float) $incomeTotalQuery->sum('amount');

        $expenseTotalQuery = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
        if ($accountId !== 'all') {
            $expenseTotalQuery->where('account_id', $accountId);
        }
        $totalExpense = (float) $expenseTotalQuery->sum('amount');

        $net = $totalIncome - $totalExpense;

        // 4. Expense by category
        $expenseRowsQuery = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
        if ($accountId !== 'all') {
            $expenseRowsQuery->where('account_id', $accountId);
        }
        $expenseRows = $expenseRowsQuery
            ->selectRaw('category_id, SUM(amount) as amount')
            ->groupBy('category_id')
            ->get();

        $expense_by_category = [];
        foreach ($expenseRows as $row) {
            $cat = Category::find($row->category_id);
            $amount = (float) $row->amount;
            $expense_by_category[] = [
                'category_id' => $row->category_id,
                'name' => $cat ? $cat->name : 'Unknown',
                'color' => $cat && $cat->color ? $cat->color : '#6B7280',
                'amount' => $amount,
                'percent' => $totalExpense > 0 ? round(($amount / $totalExpense) * 100, 1) : 0,
            ];
        }

        // 5. Income by category (same pattern)
        $incomeRowsQuery = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
        if ($accountId !== 'all') {
            $incomeRowsQuery->where('account_id', $accountId);
        }
        $incomeRows = $incomeRowsQuery
            ->selectRaw('category_id, SUM(amount) as amount')
            ->groupBy('category_id')
            ->get();

        $income_by_category = [];
        foreach ($incomeRows as $row) {
            $cat = Category::find($row->category_id);
            $amount = (float) $row->amount;
            $income_by_category[] = [
                'category_id' => $row->category_id,
                'name' => $cat ? $cat->name : 'Unknown',
                'color' => $cat && $cat->color ? $cat->color : '#6B7280',
                'amount' => $amount,
                'percent' => $totalIncome > 0 ? round(($amount / $totalIncome) * 100, 1) : 0,
            ];
        }

        // 6. Accounts summary only when all
        $accounts_summary = [];
        if ($accountId === 'all') {
            $accounts = Account::where('user_id', $user->id)->get();
            foreach ($accounts as $account) {
                $income = (float) Transaction::where('user_id', $user->id)
                    ->where('account_id', $account->id)
                    ->where('type', 'income')
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->sum('amount');
                $expense = (float) Transaction::where('user_id', $user->id)
                    ->where('account_id', $account->id)
                    ->where('type', 'expense')
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->sum('amount');
                if ($income == 0 && $expense == 0) {
                    continue;
                }
                $accounts_summary[] = [
                    'account_id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Report fetched successfully',
            'data' => [
                'month' => $month,
                'account_id' => $accountId,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net' => $net,
                'expense_by_category' => $expense_by_category,
                'income_by_category' => $income_by_category,
                'accounts_summary' => $accounts_summary,
            ],
        ]);
    }
}
