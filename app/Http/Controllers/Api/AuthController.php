<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'profile.nickname' => ['nullable', 'string', 'max:80'],
            'profile.first_name' => ['nullable', 'string', 'max:80'],
            'profile.last_name' => ['nullable', 'string', 'max:80'],
            'profile.body_type' => ['nullable', 'string', 'max:50'],
            'profile.fitness_level' => ['nullable', 'string', 'max:50'],
            'profile.training_days' => ['nullable', 'string', 'max:50'],
            'profile.training_preference' => ['nullable', 'string', 'max:50'],
            'profile.main_fitness_goal' => ['nullable', 'string', 'max:80'],
            'profile.onboarding_completed' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $profileAttributes = collect($data['profile'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $profileAttributes['onboarding_completed'] = $profileAttributes['onboarding_completed']
            ?? ! empty($profileAttributes);

        $user->profile()->create($profileAttributes);

        $token = $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken;

        $user->load('profile');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile,
            ],
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
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile,
            ],
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
}
