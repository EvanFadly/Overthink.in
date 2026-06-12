<?php

namespace Tests\Feature;

use App\Ai\Agents\OverthinkAgent;
use App\Models\SharedResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QuizApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GET /api/questions endpoint.
     */
    public function test_can_get_questions(): void
    {
        $response = $this->getJson('/api/questions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'questions' => [
                    '*' => [
                        'id',
                        'category',
                        'text',
                        'options' => [
                            '*' => [
                                'index',
                                'text',
                            ]
                        ]
                    ]
                ]
            ]);

        // Assert that the 'score' field is not leaked to the frontend
        $data = $response->json();
        $this->assertNotEmpty($data['questions']);
        $this->assertArrayNotHasKey('score', $data['questions'][0]['options'][0]);
    }

    /**
     * Test POST /api/submit-answers requires a valid session token.
     */
    public function test_submit_answers_requires_valid_session(): void
    {
        $response = $this->postJson('/api/submit-answers', [
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 0]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_id']);
    }

    /**
     * Test POST /api/submit-answers validates payload constraints.
     */
    public function test_submit_answers_validates_payload(): void
    {
        $sessionId = 'overthink_sess_test_validation';
        Cache::put('session:' . $sessionId, true, now()->addHours(24));

        // Case 1: Empty answers array
        $response = $this->postJson('/api/submit-answers', [
            'session_id' => $sessionId,
            'answers' => []
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);

        // Case 2: Incomplete answers array (less than 6 questions)
        $response = $this->postJson('/api/submit-answers', [
            'session_id' => $sessionId,
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 0]
            ]
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);

        // Case 3: Invalid question ID
        $response = $this->postJson('/api/submit-answers', [
            'session_id' => $sessionId,
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 0],
                ['question_id' => 2, 'selected_option_index' => 0],
                ['question_id' => 3, 'selected_option_index' => 0],
                ['question_id' => 4, 'selected_option_index' => 0],
                ['question_id' => 5, 'selected_option_index' => 0],
                ['question_id' => 999, 'selected_option_index' => 0], // Invalid ID
            ]
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers.5.question_id']);

        // Case 4: Invalid selected option index
        $response = $this->postJson('/api/submit-answers', [
            'session_id' => $sessionId,
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 0],
                ['question_id' => 2, 'selected_option_index' => 0],
                ['question_id' => 3, 'selected_option_index' => 0],
                ['question_id' => 4, 'selected_option_index' => 0],
                ['question_id' => 5, 'selected_option_index' => 0],
                ['question_id' => 6, 'selected_option_index' => 5], // Invalid option index (valid is 0, 1, 2)
            ]
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers.5.selected_option_index']);
    }

    /**
     * Test POST /api/submit-answers calculates score, invokes AI, saves to DB, and returns result.
     */
    public function test_submit_answers_saves_to_db_and_returns_ai_result(): void
    {
        $sessionId = 'overthink_sess_test_success';
        Cache::put('session:' . $sessionId, true, now()->addHours(24));

        $mockAiResponse = [
            'title' => 'OVERTHINK LEVEL 80%: Burnout Berjamaah',
            'result' => 'Lu kebanyakan mikirin masa depan sampe lupa kalo besok hari senin.',
            'metadata' => [
                'mental_battery' => '5%',
                'delusion_level' => 'overcooked',
                'recommended_action' => 'matikan hp tidur sekarang'
            ]
        ];

        // Mock the OverthinkAgent structured output response
        OverthinkAgent::fake([
            $mockAiResponse
        ]);

        $payload = [
            'session_id' => $sessionId,
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 2], // 16 score
                ['question_id' => 2, 'selected_option_index' => 2], // 16 score
                ['question_id' => 3, 'selected_option_index' => 2], // 17 score
                ['question_id' => 4, 'selected_option_index' => 2], // 17 score
                ['question_id' => 5, 'selected_option_index' => 2], // 17 score
                ['question_id' => 6, 'selected_option_index' => 2], // 17 score
            ] // total score = 100
        ];

        $response = $this->postJson('/api/submit-answers', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'uuid',
                'title',
                'result',
                'metadata' => [
                    'mental_battery',
                    'delusion_level',
                    'recommended_action',
                ]
            ])
            ->assertJson([
                'title' => $mockAiResponse['title'],
                'result' => $mockAiResponse['result'],
                'metadata' => $mockAiResponse['metadata'],
            ]);

        $uuid = $response->json('uuid');
        $this->assertNotEmpty($uuid);

        // Verify it was saved in the database
        $this->assertDatabaseHas('shared_results', [
            'uuid' => $uuid,
            'session_id' => $sessionId,
            'result_title' => $mockAiResponse['title'],
            'result_text' => $mockAiResponse['result'],
            'stress_score' => 100,
        ]);
    }

    /**
     * Test GET /api/result/{uuid} public retrieval endpoint.
     */
    public function test_can_retrieve_shared_result_by_uuid(): void
    {
        $shared = SharedResult::create([
            'session_id' => 'overthink_sess_dummy',
            'result_title' => 'Cemas Maksimal',
            'result_text' => 'Kopi mu dingin karna kebanyakan merenung.',
            'stress_score' => 75,
            'metadata' => [
                'mental_battery' => '10%',
                'delusion_level' => 'akut',
                'recommended_action' => 'taruh hp lu'
            ],
        ]);

        $response = $this->getJson("/api/result/{$shared->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Cemas Maksimal',
                'result' => 'Kopi mu dingin karna kebanyakan merenung.',
                'metadata' => [
                    'mental_battery' => '10%',
                    'delusion_level' => 'akut',
                    'recommended_action' => 'taruh hp lu'
                ],
            ]);
    }

    /**
     * Test rate limiting on POST /api/submit-answers.
     */
    public function test_submit_answers_enforces_rate_limiting(): void
    {
        $sessionId = 'overthink_sess_rate_limit';
        Cache::put('session:' . $sessionId, true, now()->addHours(24));

        $mockAiResponse = [
            'title' => 'Dummy Result',
            'result' => 'Dummy Result Text',
            'metadata' => [
                'mental_battery' => '5%',
                'delusion_level' => 'low',
                'recommended_action' => 'none'
            ]
        ];

        // Fake multiple responses
        OverthinkAgent::fake([
            $mockAiResponse,
            $mockAiResponse,
            $mockAiResponse,
            $mockAiResponse,
            $mockAiResponse,
            $mockAiResponse,
        ]);

        $payload = [
            'session_id' => $sessionId,
            'answers' => [
                ['question_id' => 1, 'selected_option_index' => 0],
                ['question_id' => 2, 'selected_option_index' => 0],
                ['question_id' => 3, 'selected_option_index' => 0],
                ['question_id' => 4, 'selected_option_index' => 0],
                ['question_id' => 5, 'selected_option_index' => 0],
                ['question_id' => 6, 'selected_option_index' => 0],
            ]
        ];

        // Send 5 successful requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/submit-answers', $payload);
            $response->assertStatus(200);
        }

        // The 6th request should fail due to throttle middleware (429 Too Many Requests)
        $response = $this->postJson('/api/submit-answers', $payload);
        $response->assertStatus(429);
    }
}
