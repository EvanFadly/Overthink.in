<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SessionHandshakeTest extends TestCase
{
    /**
     * Test session initialization endpoint.
     */
    public function test_can_start_anonymous_session(): void
    {
        $response = $this->postJson('/api/start-session');

        $response->assertStatus(200)
            ->assertJsonStructure(['session_id']);

        $sessionId = $response->json('session_id');

        $this->assertStringStartsWith('overthink_sess_', $sessionId);

        // Verify that the token is stored in cache
        $this->assertTrue(Cache::has('session:'.$sessionId));
    }

    /**
     * Test middleware blocks request without session token.
     */
    public function test_middleware_blocks_request_without_session_id(): void
    {
        $response = $this->getJson('/api/test-middleware');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_id'])
            ->assertJson([
                'message' => 'Invalid or expired session token.',
            ]);
    }

    /**
     * Test middleware blocks request with invalid session token format.
     */
    public function test_middleware_blocks_invalid_format_session_id(): void
    {
        $response = $this->getJson('/api/test-middleware?session_id=invalid_prefix_token');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_id']);
    }

    /**
     * Test middleware blocks request when session has expired/not in cache.
     */
    public function test_middleware_blocks_expired_session_id(): void
    {
        $sessionId = 'overthink_sess_non_existent_token_in_cache';

        $response = $this->getJson("/api/test-middleware?session_id={$sessionId}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_id']);
    }

    /**
     * Test middleware allows request with valid session token passed via input body/query.
     */
    public function test_middleware_allows_valid_session_id_in_query_or_body(): void
    {
        $sessionId = 'overthink_sess_valid_token_123';
        Cache::put('session:'.$sessionId, true, now()->addHours(24));

        $response = $this->getJson("/api/test-middleware?session_id={$sessionId}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    /**
     * Test middleware allows request with valid session token passed via X-Session-ID header.
     */
    public function test_middleware_allows_valid_session_id_in_headers(): void
    {
        $sessionId = 'overthink_sess_valid_token_456';
        Cache::put('session:'.$sessionId, true, now()->addHours(24));

        $response = $this->getJson('/api/test-middleware', [
            'X-Session-ID' => $sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    /**
     * Test middleware allows request with valid session token passed via Bearer Authorization header.
     */
    public function test_middleware_allows_valid_session_id_as_bearer_token(): void
    {
        $sessionId = 'overthink_sess_valid_token_789';
        Cache::put('session:'.$sessionId, true, now()->addHours(24));

        $response = $this->getJson('/api/test-middleware', [
            'Authorization' => "Bearer {$sessionId}",
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }
}
