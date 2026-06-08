<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /**
     * Start a new anonymous session.
     *
     * Generates a cryptographically secure token with prefix overthink_sess_
     * and saves it in the Cache store for 24 hours.
     */
    public function start(Request $request): JsonResponse
    {
        // Generate a secure anonymous token session prefix overthink_sess_
        $token = 'overthink_sess_'.Str::random(40);

        // Store the token in cache for 24 hours (86400 seconds)
        Cache::put('session:'.$token, true, now()->addHours(24));

        return response()->json([
            'session_id' => $token,
        ], 200);
    }
}
