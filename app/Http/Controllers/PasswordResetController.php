<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always same response (don't reveal if email exists)
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If that email exists, a reset link has been sent',
            ]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $resetUrl = $frontendUrl . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email);

        // Simple mail — or use a Mailable like OTP
        Mail::raw(
            "Reset your password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your password');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'If that email exists, a reset link has been sent',
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $row = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset link',
            ], 422);
        }

        // Expire after 60 minutes
        if (Carbon::parse($row->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            return response()->json([
                'success' => false,
                'message' => 'Reset link has expired',
            ], 422);
        }

        if (!Hash::check($validated['token'], $row->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset link',
            ], 422);
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset link',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can login now.',
        ]);
    }
}
