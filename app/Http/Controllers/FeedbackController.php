<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:feedback,bug,suggestion',
            'message' => 'required|string|min:5|max:2000',
            'page' => 'nullable|string|max:255',
        ]);

        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'page' => $validated['page'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks for your feedback',
            'data' => $feedback,
        ], 201);
    }
}
