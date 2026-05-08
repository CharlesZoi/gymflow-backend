<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use App\Support\UserProfilePayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:120'],
            'device_name' => ['nullable', 'string'],
            'profile' => ['nullable', 'array'],
            ...UserProfilePayload::rules('profile.'),
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $profileAttributes = UserProfilePayload::filledAttributes($data['profile'] ?? []);

        $profileAttributes['onboarding_completed'] = $profileAttributes['onboarding_completed']
            ?? ! empty($profileAttributes);

        $user->profile()->create($profileAttributes);

        $token = $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken;

        $user->load('profile');

        return response()->json([
            'token' => $token,
            'user' => AuthUserResource::make($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::with('profile')->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => AuthUserResource::make($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => AuthUserResource::make($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Always respond with the same generic message to avoid leaking
        // whether the email is registered. The Laravel password broker
        // delivers the email when a mailer is configured.
        if (User::where('email', $data['email'])->exists()) {
            try {
                Password::sendResetLink(['email' => $data['email']]);
            } catch (\Throwable) {
                // Swallow mailer/transport errors so we never leak details.
            }
        }

        return response()->json([
            'message' => 'If that email belongs to a GymFlow member, we just sent reset instructions.',
            'reference' => Str::uuid()->toString(),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => ['Unable to reset password with the provided token.'],
            ]);
        }

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password updated.',
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Account deleted.',
        ]);
    }
}
