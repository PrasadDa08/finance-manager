<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $user = auth('sanctum')->user();
        $accounts = auth('sanctum')->user()->accounts;

        return response()->json([
            "success" => true,
            "message" => "Accounts fetched successfully",
            "data" => $accounts,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:bank,cash,credit_card,wallet',
            'opening_balance' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $user = auth('sanctum')->user();

        $account = Account::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'opening_balance' => $request->opening_balance || 0,
            'currency' => $request->currency || 'INR',
            'is_active' => $request->is_active || true
        ]);

        return response()->json([
            "success" => true,
            "message" => "Account added successfully",
            "data" => $account->load('user'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {

        $user = auth('sanctum')->user();

        if ($user->id !== $account->user_id) {
            return response()->json([
                "success" => false,
                "message" => "Account dosen't belongs to you",
            ], 403);
        }

        return response()->json([
            "success" => true,
            "message" => "Account fetched successfully",
            "data" => $account->load('user'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:bank,cash,credit_card,wallet',
            'opening_balance' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = auth('sanctum')->user();

        if ($user->id !== $account->user_id) {
            return response()->json([
                "success" => false,
                "message" => "Account dosen't belongs to you",
            ], 403);
        }

        $account->update($validated);

        return response()->json([
            "success" => true,
            "message" => "Account details updated",
            "data" => $account->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $user = auth('sanctum')->user();

        if ($user->id !== $account->user_id) {
            return response()->json([
                "success" => false,
                "message" => "Account dosen't belongs to you",
            ], 403);
        }

        $account->delete();

        return response()->json([
            "success" => true,
            "message" => "Account deleted successfully"
        ], 200);
    }
}
