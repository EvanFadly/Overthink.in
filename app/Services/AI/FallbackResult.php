<?php

namespace App\Services\AI;

class FallbackResult
{
    /**
     * Get a random funny fallback result when AI fails.
     *
     * @return array{title: string, result: string, metadata: array{mental_battery: string, delusion_level: string, recommended_action: string}}
     */
    public static function get(): array
    {
        $fallbacks = [
            [
                'title' => 'AI LAGI OVERTHINKING JUGA',
                'result' => 'Server kami sedang merenungi arti kehidupan dan mengapa guling bisa bau asem padahal gak pernah diajak lari pagi. Coba beberapa saat lagi ya.',
                'metadata' => [
                    'mental_battery' => '0%',
                    'delusion_level' => 'maksimal',
                    'recommended_action' => 'seduh mi instan malem-malem terus merenung di pojok kasur'
                ]
            ],
            [
                'title' => 'OTAK AI SEDANG DEFRAGMENTASI',
                'result' => 'Terlahu banyak beban pikiran yang masuk. AI kami memutuskan untuk pura-pura tidur biar gak diajak ngomong. Coba lagi nanti.',
                'metadata' => [
                    'mental_battery' => '1%',
                    'delusion_level' => 'setara dewa',
                    'recommended_action' => 'tatap langit-langit kamar selama 15 menit'
                ]
            ],
            [
                'title' => 'KONEKSI JIWA TERPUTUS',
                'result' => 'Groq API lagi kena mental akibat membaca keluh kesahmu yang kelewat absurd. Kami butuh waktu untuk menenangkan AI ini.',
                'metadata' => [
                    'mental_battery' => 'habis total',
                    'delusion_level' => 'infinite',
                    'recommended_action' => 'minum air putih dingin terus sadar diri'
                ]
            ]
        ];

        return $fallbacks[array_rand($fallbacks)];
    }
}
