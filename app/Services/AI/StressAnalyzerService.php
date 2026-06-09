<?php

namespace App\Services\AI;

/**
 * StressAnalyzerService
 *
 * Bertanggung jawab untuk:
 * - Menyusun prompt dari jawaban user
 * - Memanggil OpenAI API via openai-php/laravel
 * - Mengembalikan hasil dalam format strict JSON
 *
 * Rules (dari ai-context/99-rules.md):
 * - Bahasa Indonesia kasual
 * - Tone: absurd humor
 * - Output WAJIB strict JSON mode
 * - Jangan taruh AI call langsung di Controller
 *
 * @todo Fase berikutnya: implementasi buildPrompt() + call OpenAI
 */
use App\Ai\Agents\OverthinkAgent;
use Illuminate\Support\Facades\Log;
use Throwable;

class StressAnalyzerService
{
    /**
     * Analyze user stress level and generate dynamic absurd AI response.
     *
     * @param int $stressScore Hidden stress score (0-100)
     * @param array $answersSummary Summary of user answers (question/answer pairs)
     * @return array{title: string, result: string, metadata: array{mental_battery: string, delusion_level: string, recommended_action: string}}
     */
    public function analyze(int $stressScore, array $answersSummary): array
    {
        try {
            // 1. Build prompt instructions containing scores and answers
            $instructions = PromptBuilder::build($stressScore, $answersSummary);

            // 2. Instantiate structured agent with customized system prompt
            $agent = OverthinkAgent::make(instructions: $instructions);

            // 3. Request evaluation using casual prompt to save input tokens
            $response = $agent->prompt("Mulai analisis kelakuan user.");

            // 4. Return structured JSON response directly
            return $response->structured;
        } catch (Throwable $e) {
            Log::error('AI stress analysis failed, triggering fallback: ' . $e->getMessage(), [
                'exception' => $e,
                'stress_score' => $stressScore,
                'answers_summary' => $answersSummary
            ]);

            // 5. Recover with an entertaining static backup response
            return FallbackResult::get();
        }
    }
}
