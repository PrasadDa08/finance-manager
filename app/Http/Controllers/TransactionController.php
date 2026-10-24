<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = auth('sanctum')->user()->transactions()->with('account:id,name', 'category:id,name,type')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            "message" => "Transactions fetched successfully",
            "data" => $transactions,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $user = auth('sanctum')->user();


        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', true);
        })->first();

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => "Account doesn't belongs to user",
            ], 403);
        }

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to user",
            ], 403);
        }

        if ($category->type !== $validated['type']) {
            return response()->json([
                'success' => false,
                'message' => "Category type must match transaction type"
            ], 403);
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $validated['account_id'],
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'transaction_date' => $validated['transaction_date']
        ]);

        return response()->json([
            'success' => true,
            'message' => "Transaction added successfully",
            'data' => $transaction->load('account:id,name,opening_balance'),
        ], 201);
    }

    public function show(Transaction $transaction)
    {
        $user = auth('sanctum')->user();

        if ($transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Transaction doesn't belongs to you",
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => "Transaction fetched successfully",
            'data' => $transaction->load('user:id,name', 'account:id,name,type', 'category:id,name,type'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $user = auth('sanctum')->user();

        if ($transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Transaction doesn't belongs to you"
            ], 403);
        }

        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', true);
        })->first();

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => "Account doesn't belongs to user",
            ], 403);
        }

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to user",
            ], 403);
        }

        if ($category->type !== $validated['type']) {
            return response()->json([
                'success' => false,
                'message' => "Category type must match transaction type"
            ], 403);
        }

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction->fresh()->load('user:id,name', 'account:id,name,type', 'category:id,name,type'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $user = auth('sanctum')->user();

        if ($transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Transaction doesn't belongs to you",
            ], 403);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted sucessfully',
        ], 200);
    }
}
