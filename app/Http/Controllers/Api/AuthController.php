<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'region_id' => 'nullable|exists:regions,id',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'] ?? null,
            'password'          => Hash::make($validated['password']),
            'role'              => 'customer',
            'region_id'         => $validated['region_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Account created successfully. Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'Please verify your email address before logging in. Check your inbox for a verification link.',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->firstOrFail();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message' => 'OTP sent successfully. Valid for 10 minutes.',
            '_debug_otp' => $otp,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|exists:users,phone',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('phone', $request->phone)->firstOrFail();

        if (! $user->isOtpValid() || $user->otp !== $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP. Please request a new one.',
            ], 422);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->update(['otp' => null, 'otp_expires_at' => null]);

        $token = $user->createToken('otp-token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified. Login successful.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('region.city.governorate.country');

        return response()->json(['data' => $this->formatUser($user)]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'email_verified' => $user->hasVerifiedEmail(),
            'region' => $user->region ? [
                'id' => $user->region->id,
                'name' => $user->region->name,
                'city' => $user->region->city?->name,
                'governorate' => $user->region->city?->governorate?->name,
                'country' => $user->region->city?->governorate?->country?->name,
            ] : null,
        ];
    }
}
