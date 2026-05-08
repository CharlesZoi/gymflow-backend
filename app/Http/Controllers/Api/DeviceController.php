<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $currentTokenId = $request->user()->currentAccessToken()?->id;

        return response()->json([
            'devices' => $request->user()
                ->tokens()
                ->latest()
                ->get()
                ->map(fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'lastUsedAt' => $token->last_used_at?->toIso8601String(),
                    'createdAt' => $token->created_at?->toIso8601String(),
                    'isCurrent' => $token->id === $currentTokenId,
                ]),
        ]);
    }

    public function destroy(Request $request, int $device)
    {
        $token = $request->user()->tokens()->whereKey($device)->firstOrFail();

        if ($token->id === $request->user()->currentAccessToken()?->id) {
            return response()->json([
                'message' => 'Current device cannot be revoked from this endpoint.',
            ], 422);
        }

        $token->delete();

        return response()->json([
            'message' => 'Device revoked.',
        ]);
    }
}
