<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $user = User::create(
                [
                    "name" => $request->name,
                    "email" => $request->email,
                    "password" => Hash::make($request->password, [PASSWORD_DEFAULT]),
                ]
            );

            return response()->json([
                "success" => true,
                "message" => "User registered successfully",
                "user" => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong, try again after few minutes"
            ], 500);
        }
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);


        if (!Auth::attempt($credentials)) {
            return response()->json([
                "success" => false,
                "message" => "Invalid email or password",
            ], 401);
        }

        $user = auth('sanctum')->user();

        // $request->session()->regenerate();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Logged in successfully",
            "user" => $user,
            "token" => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke current token (if using token)
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // Logout from session (cookie-based)
        // Auth::guard('web')->logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
