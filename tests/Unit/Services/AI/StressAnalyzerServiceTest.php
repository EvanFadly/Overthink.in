<?php

namespace Tests\Unit\Services\AI;

use App\Ai\Agents\OverthinkAgent;
use App\Services\AI\StressAnalyzerService;
use Tests\TestCase;

class StressAnalyzerServiceTest extends TestCase
{
    /**
     * Test successful analysis with a faked agent response.
     */
    public function test_analyze_returns_structured_output_from_agent(): void
    {
        $mockResult = [
            'title' => 'OVERTHINK LEVEL 85%: Cemas Maksimal',
            'result' => 'Lu kebanyakan mikir sampe-sampe router wifi di rumah ikut pusing.',
            'metadata' => [
                'mental_battery' => '5%',
                'delusion_level' => 'medium rare',
                'recommended_action' => 'rebahan tanpa main hp'
            ]
        ];

        // Fake the OverthinkAgent response using a list
        OverthinkAgent::fake([
            $mockResult
        ]);

        $service = new StressAnalyzerService();
        $response = $service->analyze(85, [
            'Apakah kamu sering begadang?' => 'Ya, scroll TikTok sampai jam 3 pagi.',
            'Berapa cangkir kopi hari ini?' => '3 cangkir biar tetep hidup.',
        ]);

        $this->assertEquals($mockResult, $response);
    }

    /**
     * Test exception handling and fallback recovery.
     */
    public function test_analyze_handles_exceptions_and_returns_fallback_result(): void
    {
        // Force OverthinkAgent to throw an exception
        OverthinkAgent::fake(function () {
            throw new \RuntimeException('API rate limit reached');
        });

        $service = new StressAnalyzerService();
        $response = $service->analyze(85, [
            'Apakah kamu cemas?' => 'Iya banget.',
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('title', $response);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('metadata', $response);
        
        $metadata = $response['metadata'];
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('mental_battery', $metadata);
        $this->assertArrayHasKey('delusion_level', $metadata);
        $this->assertArrayHasKey('recommended_action', $metadata);
    }
}
