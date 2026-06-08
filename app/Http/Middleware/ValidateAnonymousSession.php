<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateAnonymousSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the session ID from request body, query parameter, or headers.
        $sessionId = $request->input('session_id')
            ?? $request->header('X-Session-ID')
            ?? ($request->bearerToken() ?: null);

        // Validate prefix and check existence in Cache
        if (! $sessionId || ! str_starts_with($sessionId, 'overthink_sess_') || ! Cache::has('session:'.$sessionId)) {
            return response()->json([
                'message' => 'Invalid or expired session token.',
                'errors' => [
                    'session_id' => [
                        'The session token is invalid or has expired.',
                    ],
                ],
            ], 422);
        }

        return $next($request);
    }
}
