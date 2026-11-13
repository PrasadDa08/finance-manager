<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringRun;
use App\Models\RecurringTemplate;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringController extends Controller
{
    public function index()
    {
        $user = auth('sanctum')->user();

        $templates = $user->recurringTemplates()->orderBy('id', 'desc')->get();

        return response()->json([
            "success" => true,
            "message" => "Templates fetched successfully",
            "data" => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'string|nullable',
            'day_of_month' => 'required|integer|min:1|max:28',
            'start_month' => 'required|date',
            'end_month' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $start_month = Carbon::createFromFormat('Y-m', $validated['start_month'])->toDateString();
        $end_month = $validated['end_month'] ? (Carbon::createFromFormat('Y-m', $validated['end_month'])->toDateString()) : null;

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->exists();

        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', true);
        })->first();


        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => "Account doesn't belongs to you",
            ], 422);
        }

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to you",
            ], 422);
        }

        $recurringTemplate = RecurringTemplate::create([
            'user_id' => $user->id,
            'account_id' => $validated['account_id'],
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'note' => $validated['note'],
            'day_of_month' => $validated['day_of_month'],
            'start_month' => $start_month,
            'end_month' => $end_month,
            'is_active' => $validated['is_active'] ?? 'true',
        ]);

        return response()->json([
            'success' => true,
            "message" => "Recurring template created successfully",
            "data" => $recurringTemplate,
        ], 201);
    }

    public function show(RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                "success" => false,
                "message" => "Template doesn't belongs to you",
            ], 403);
        }

        return response()->json([
            "success" => true,
            "message" => "Template fetched successfully",
            "data" => $recurringTemplate,
        ], 200);
    }

    public function update(Request $request, RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'string|nullable',
            'day_of_month' => 'required|integer',
            'start_month' => 'required|date',
            'end_month' => 'date|nullable',
            'is_active' => 'boolean',
        ]);

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->exists();

        $category = Category::where('id', $validated['category_id'])->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('is_default', 'true');
        })->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => "Account doesn't belongs to you",
            ], 403);
        }

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => "Category doesn't belongs to you",
            ]);
        }

        $recurringTemplate->update($validated);

        return response()->json([
            'success' => true,
            'message' => "template updated successfully",
            'data' => $recurringTemplate->fresh(),
        ]);
    }

    public function destroy(RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission to perform this request",
            ], 403);
        }

        $recurringTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => "template deleted successfully",
        ]);
    }

    public function due(Request $request)
    {

        $user = auth('sanctum')->user();

        $month = $request->query('month', now()->format('Y-m'));

        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $handledRunIds = RecurringRun::where('user_id', $user->id)->whereDate('month_start', $monthStart)->pluck('recurring_template_id');

        $templates = RecurringTemplate::where('user_id', $user->id)->where('is_active', true)->whereDate('start_month', '<=', $monthStart)->where(function ($q) use ($monthStart) {
            $q->whereNull('end_month')->orWhereDate('end_month', '>=', $monthStart);
        })->whereNotIn('id', $handledRunIds)->with('account:id,name,type', 'category:id,name,type,color')->orderBy('day_of_month')->get();


        return response()->json([
            'success' => true,
            'message' => "Due templates fetched successfully",
            'data' => $templates,
        ]);
    }

    public function confirm(Request $request, RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Template dosen't belongs to you"
            ], 403);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

        $exists = RecurringRun::where('recurring_template_id', $recurringTemplate->id)->whereDate('month_start', $monthStart)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Already confirmed or skipped for this month',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $recurringTemplate->account_id,
                'category_id' => $recurringTemplate->category_id,
                'type' => $recurringTemplate->type,
                'amount' => $request->input('amount', $recurringTemplate->amount),
                'note' => $request->input('note', $recurringTemplate->note),
                'transaction_date' => $request->input('transaction_date', now()->toDateString()),
            ]);

            $run = RecurringRun::create([
                'recurring_template_id' => $recurringTemplate->id,
                'user_id' => $recurringTemplate->user_id,
                'month_start' => $monthStart,
                'status' => 'confirmed',
                'transaction_id' => $transaction->id,

            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "recurring transaction added successfully",
                'data' => ["transaction" => $transaction, 'run' => $run],
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? "something went wrong, please try again later",
            ], 500);
        }
    }

    public function skip(Request $request, RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Template dosen't belongs to you"
            ], 403);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

        $exists = RecurringRun::where('recurring_template_id', $recurringTemplate->id)->whereDate('month_start', $monthStart)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Already confirmed or skipped for this month',
            ], 422);
        }

        $run = RecurringRun::create([
            'recurring_template_id' => $recurringTemplate->id,
            'user_id' => $recurringTemplate->user_id,
            'month_start' => $monthStart,
            'status' => 'skipped',
            'transaction_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "recurring transaction skipped successfully",
            'data' => $run,
        ], 201);
    }
}
