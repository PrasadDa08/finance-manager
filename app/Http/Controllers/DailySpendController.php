<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;

class DailySpendController extends Controller
{
    public function index()
    {
        $user = auth('sanctum')->user();

        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();
        $category = Category::where('name', 'Salary')->where('type', 'income')->first();

        $salary = $user->transactions()->where('category_id', $category->id)->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');

        $dailySpendLimit = $salary / Carbon::now()->daysInMonth();

        $spendings =  $user->transactions()->whereBetween('transaction_date', [$monthStart, $monthEnd])->selectRaw('transaction_date, SUM("amount") as total')->groupBy('transaction_date')->pluck('total', 'transaction_date');

        $dates = collect();

        for($day = 1; $day <= Carbon::now()->daysInMonth(); $day++){
            $dates->push(Carbon::now()->startOfMonth()->addDay($day-1)->toDateString());
        }

        $data = $dates->map(function($date) use($spendings, $dailySpendLimit){
            $spent = $spendings->get($date, 0);
            $net = $dailySpendLimit - $spent;

            return [
                'date' => $date,
                'daily_limit' => $dailySpendLimit,
                'spent' => $spent,
                'net' => $net,
                'status' => $spent > 0 ? "over spent" : "under spent",
            ];
        });

        return response()->json([
            'salary' => $salary,
            'data' => $data,
            'spendings' => $spendings,
        ]);
    }
}
