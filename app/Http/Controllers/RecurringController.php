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
            'user_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|min:1',
            'amount' => 'required|decimal:12,2',
            'note' => 'string|nullable',
            'day_of_month' => 'required|integer',
            'start_month' => 'required|date',
            'end_month' => 'required|date',
            'is_active' => 'required|boolean|default:true',
        ]);

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->exists();

        $category = Category::where('id', $validated['category_id'])->where('user_id', $user->id)->exists();

        if ($validated['user_id'] !== $user->id) {
            return response()->json([
                "success" => false,
                "message" => "You don't have permission for this request",
            ], 403);
        }

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
            'start_month' => $validated['start_month'],
            'end_month' => $validated['end_month'],
            'is_active' => $validated ?? 'true',
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

        if ($user->id !== $recurringTemplate->id) {
            return response()->json([
                "success" => false,
                "message" => "Template doesn't belongs to you"
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
            'amount' => 'required|decimal:12,2',
            'note' => 'string|nullable',
            'day_of_month' => 'required|integer',
            'start_month' => 'required|date',
            'end_month' => 'date|nullable',
            'is_active' => 'boolean|default:true',
        ]);

        $account = Account::where('id', $validated['account_id'])->where('user_id', $user->id)->exists();

        $category = Category::where('id', $validated['category_id'])->where('user_id', $user->id)->exists();

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

        $month = $request->querry('month', now()->format('Y-m'));

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

    public function confirm(RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Template dosen't belongs to you"
            ], 403);
        }

        DB::beginTransaction();

        try {

            $transaction = Transaction::create([
                'user_id' => $recurringTemplate->user_id,
                'account_id' => $recurringTemplate->account_id,
                'category_id' => $recurringTemplate->category_id,
                'type' => $recurringTemplate->type,
                'amount' => $recurringTemplate->amount,
                'note' => $recurringTemplate->note ?? null,
                'transaction_date' => Carbon::now()->format('Y-m-d')->toDateString(),
            ]);

            $run = RecurringRun::create([
                'recurring_template_id' => $recurringTemplate->id,
                'user_id' => $recurringTemplate->user_id,
                'month_start' => $recurringTemplate->start_month,
                'status' => 'confirmed',
                'transaction_id' => $transaction->id,

            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "recurring transaction added successfully",
                'data' => [$transaction, $run],
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? "something went wrong, please try again later",
            ], 500);
        }
    }

    public function skip(RecurringTemplate $recurringTemplate)
    {
        $user = auth('sanctum')->user();

        if ($recurringTemplate->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => "Template dosen't belongs to you"
            ], 403);
        }

        $run = RecurringRun::create([
            'recurring_template_id' => $recurringTemplate->id,
            'user_id' => $recurringTemplate->user_id,
            'month_start' => $recurringTemplate->start_month,
            'status' => 'skipped',
            'transaction_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "recurring transaction added successfully",
            'data' => $run,
        ], 201);
    }
}
