<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function transactions(Request $request)
    {
        $user = auth('sanctum')->user();

        $month = substr($request->query('month', now()->format('Y-m')), 0, 7);
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $accountId = $request->query('account_id', "all");

        if ($accountId !== 'all') {
            $owns = Account::where('id', $accountId)->where('user_id', $user->id)->exists();

            if (!$owns) {
                return response()->json([
                    'success' => false,
                    'message' => "Account doesn't belongs to you",
                ], 403);
            }
        }

        $query = Transaction::where('user_id', $user->id)->whereBetween('transaction_date', [$monthStart, $monthEnd])->with(['account:id,name', 'category:id,name'])->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

        if ($accountId !== 'all') {
            $query->where('account-id', $accountId);
        }

        $transactions = $query->get();

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Date', 'Type', 'Amount', 'Account', 'Category', 'Note']);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                substr($t->transaction_date, 0, 10),
                $t->type,
                $t->amount,
                $t->account?->name ?? '',
                $t->category?->name ?? '',
                $t->note ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = "transaction-{$month}.csv";

        return response($csv, 200, [
            'Content-Type' => 'tsxt/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

