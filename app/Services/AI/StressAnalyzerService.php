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
class StressAnalyzerService
{
    // Will be implemented in next phase
}
