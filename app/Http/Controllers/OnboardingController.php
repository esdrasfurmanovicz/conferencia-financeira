<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->onboarded_at) {
            $user->forceFill(['onboarded_at' => now()])->save();
        }

        return response()->json(['ok' => true]);
    }
}
