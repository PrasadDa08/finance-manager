<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'period' => 'nullable|in:all,this_week,this_month,this_year',
            'account_id' => 'nullable|exists:accounts,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $period = $validated['period'] ?? 'all';
        $accountId = $validated['account_id'] ?? null;
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        if ($accountId) {
            $account = Account::where('id', $accountId)->where('user_id', $user->id)->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not belong to you',
                ], 403);
            }
        }

        [$start, $end] = $this->resolveDateRange($period, $fromDate, $toDate);

        $transactionsQuery = Transaction::where('user_id', $user->id);

        if ($accountId) {
            $transactionsQuery->where('account_id', $accountId);
        }

        $filterQuery = clone $transactionsQuery;

        if ($start) {
            $filterQuery->where('transaction_date', '>=', $start);
        }

        if ($end) {
            $filterQuery->where('transaction_date', '<=', $end);
        }

        $totalIncome = (clone $filterQuery)->where('type', 'income')->sum('amount');

        $totalExpense = (clone $filterQuery)->where('type', 'expense')->sum('amount');

        $totalBalance = $this->calculateBalance($user->id, $accountId);

        $recentTransactions = (clone $filterQuery)->with(['account:id,name', 'category:id,name,type'])->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->limit(5)->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data fetched successfully',
            'data' => [
                'filters' => [
                    'period' => $period,
                    'account_id' => $accountId,
                    'from_date' => $start,
                    'to_date' => $end
                ],
                'summary' => [
                    'total_balance' => round((float) $totalBalance, 2),
                    'total_income' => round((float) $totalIncome, 2),
                    'total_expense' => round((float) $totalExpense, 2),
                    'net' => round((float) $totalIncome - (float) $totalExpense, 2),
                ],
                'recent_transactions' => $recentTransactions,
            ]
        ]);
    }


    private function resolveDateRange(?string $period, ?string $fromDate, ?string $toDate): array
    {

        if ($fromDate || $toDate) {
            return [
                $fromDate ? Carbon::parse($fromDate)->toDateString() : null,
                $toDate ? Carbon::parse($toDate)->toDateString() : null,
            ];
        }

        $now = Carbon::now();

        return match ($period) {
            'this_week' => [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ],
            'this_month' =>
            [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ],
            'this_year' => [
                $now->copy()->startOfYear()->toDateString(),
                $now->copy()->endOfYear()->toDateString()
            ],
            default => [null, null],
        };
    }

    private function calculateBalance(int $userId, ?int $accountId = null): float{
        $accountsQuery = Account::where('user_id', $userId);

        if($accountId){
            $accountsQuery->where('id', $accountId);
        }

        $accounts = $accountsQuery->get();

        $balance = 0;

        foreach($accounts as $account){
            $txQuery = Transaction::where('user_id', $userId)->where('account_id', $account->id);

            $income = (clone $txQuery)->where('type', 'income')->sum('amount');
            $expense = (clone $txQuery)->where('type', 'expense')->sum('amount');

            $balance += (float) $account->opening_balance + (float) $income - (float) $expense;
        }

        return $balance;
    }
}
