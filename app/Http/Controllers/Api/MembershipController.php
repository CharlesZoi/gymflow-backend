<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->profile()->firstOrCreate();

        return response()->json([
            'membership' => [
                'plan' => $profile->membership_plan ?? 'Free Plan',
                'status' => 'active',
                'renewsOn' => $profile->membership_renews_on?->toDateString(),
            ],
        ]);
    }
}
