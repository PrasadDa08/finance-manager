<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailySpendController extends Controller
{
    public function index(Request $request)
    {

        $user = auth('sanctum')->user();
        $now = Carbon::now();

        $category_id = $request->query('category', 'all');
        $month = $request->query('month', $now->format("Y-m"));

        $monthDate = Carbon::createFromFormat('Y-m', $month);

        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
        $monthEnd = $monthDate->copy()->endOfMonth()->toDateString();
        $daysInMonth = $monthDate->copy()->daysInMonth;

        if ($category_id !== 'all') {
            $categoryCheck = Category::where('id', $category_id)->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('user_id', null)->orWhere('is_default', true);
            })->where('type', 'income')->exists();

            if (!$categoryCheck) {
                return response()->json([
                    'success' => false,
                    'message' => "Category dosen't belongs to you."
                ], 403);
            }
        }
        $salaryQuery = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd]);

        if ($category_id !== "all") {
            $salaryQuery->where('category_id', $category_id);
        }

        $salary = (float)$salaryQuery->sum('amount');

        $dailyLimit = $daysInMonth > 0 ? $salary / $daysInMonth : 0;

        $spendings = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('transaction_date, SUM(amount) as total')
            ->groupBy('transaction_date')
            ->pluck('total', 'transaction_date');

        $today = $now->toDateString();
        $data = collect();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthDate->copy()->startOfMonth()->addDays($day - 1)->toDateString();

            $spent = (float) $spendings->get($date, 0);

            $kind = Carbon::parse($date)->toDateString() <=> $today;

            $weekDay = Carbon::parse($date)->format('l');

            $data->push([
                'date' => $date,
                'day' => $weekDay,
                'daily_limit' => round($dailyLimit, 2),
                'spent' => $spent,
                'remaining' => round($dailyLimit - $spent, 2),
                'kind' => $kind < 0 ? "past" : ($kind == 0 ? "today" : "upcoming"),
                'status' => $kind == 1 ? 'upcoming' : ($spent > $dailyLimit ? 'over' : 'under'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daily spend fetched successfully',
            'data' => [
                'month' => $month,
                'monthly_income' => $salary,
                'daily_limit' => round($dailyLimit, 2),
                'income_filter' => $category_id,
                'days' => $data->values(),
            ],
        ]);
    }
}
