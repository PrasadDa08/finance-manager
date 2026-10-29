<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationOtpMail;
use App\Models\RegistrationOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Only regenerate session if session exists (website)
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // Only delete if it's a real personal access token
        if ($user) {
            $token = $user->currentAccessToken();

            if ($token && !($token instanceof \Laravel\Sanctum\TransientToken)) {
                $token->delete();
            }
        }

        // End cookie/session auth
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    public function requestOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $domain = strtolower(substr(strrchr($validated['email'], '@'), 1));
        $blocked = ['test.com', 'example.com', 'mailinator.com', 'tempmail.com', 'guerrillamail.com', 'yopmail.com'];
        if (in_array($domain, $blocked, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Please use a real email address',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // remove old otps for this email
        RegistrationOtp::where('email', $validated['email'])->delete();

        RegistrationOtp::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($validated['email'])
            ->send(new RegistrationOtpMail($validated['name'], $otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $record = \App\Models\RegistrationOtp::where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->latest()
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        if ($record->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new one.',
            ], 422);
        }

        if (User::where('email', $record->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already registered',
            ], 422);
        }

        $user = User::create([
            'name' => $record->name,
            'email' => $record->email,
            'password' => $record->password, // already hashed
        ]);

        $record->delete();

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
