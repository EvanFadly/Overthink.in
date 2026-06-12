<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharedResult;
use App\Services\AI\StressAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizApiController extends Controller
{
    /**
     * Static questions representing the overthinking assessment flow.
     * Hidden scores are backend-only and not returned to the client.
     * The maximum possible score across all questions sums to exactly 100.
     */
    private const QUESTIONS = [
        [
            'id' => 1,
            'category' => 'mental',
            'text' => 'Otak masih aman?',
            'options' => [
                ['index' => 0, 'text' => 'Aman jaya', 'score' => 0],
                ['index' => 1, 'text' => 'Sedikit berasap', 'score' => 8],
                ['index' => 2, 'text' => 'Udah konslet total', 'score' => 16],
            ]
        ],
        [
            'id' => 2,
            'category' => 'sleep',
            'text' => 'Tidur berapa jam semalem?',
            'options' => [
                ['index' => 0, 'text' => '8 Jam lunas', 'score' => 0],
                ['index' => 1, 'text' => 'Kebangun tiap 2 jam', 'score' => 8],
                ['index' => 2, 'text' => 'Merem doang kagak tidur', 'score' => 16],
            ]
        ],
        [
            'id' => 3,
            'category' => 'emotional',
            'text' => 'Masih suka scroll chat lama?',
            'options' => [
                ['index' => 0, 'text' => 'Udah hapus', 'score' => 0],
                ['index' => 1, 'text' => 'Kadang pas sepi', 'score' => 8],
                ['index' => 2, 'text' => 'Tiap malem ritual nangis', 'score' => 17],
            ]
        ],
        [
            'id' => 4,
            'category' => 'social',
            'text' => 'Sisa baterai sosial hari ini?',
            'options' => [
                ['index' => 0, 'text' => '100% siap hangout', 'score' => 0],
                ['index' => 1, 'text' => 'Sisa 5% pengen ngilang', 'score' => 8],
                ['index' => 2, 'text' => 'Minus 50% jgn sapa gua', 'score' => 17],
            ]
        ],
        [
            'id' => 5,
            'category' => 'anxiety',
            'text' => 'Kalau ngelihat masa depan, apa yang kebayang?',
            'options' => [
                ['index' => 0, 'text' => 'Cerah menyilaukan', 'score' => 0],
                ['index' => 1, 'text' => 'Buram kayak kaca spion hujan', 'score' => 8],
                ['index' => 2, 'text' => 'Gelap gulita kek goa hantu', 'score' => 17],
            ]
        ],
        [
            'id' => 6,
            'category' => 'caffeine',
            'text' => 'Asupan kopi/kafein hari ini?',
            'options' => [
                ['index' => 0, 'text' => 'Air putih aja cukup', 'score' => 0],
                ['index' => 1, 'text' => 'Satu gelas es kopi susu', 'score' => 8],
                ['index' => 2, 'text' => 'Udah gelas ketiga tapi tetep ngantuk', 'score' => 17],
            ]
        ]
    ];

    /**
     * GET /api/questions
     * Return static question entities to ensure clean frontend separation.
     * Excludes the hidden scoring matrix to protect backend rules.
     */
    public function index(): JsonResponse
    {
        $publicQuestions = array_map(function ($q) {
            return [
                'id' => $q['id'],
                'category' => $q['category'],
                'text' => $q['text'],
                'options' => array_map(function ($opt) {
                    return [
                        'index' => $opt['index'],
                        'text' => $opt['text'],
                    ];
                }, $q['options']),
            ];
        }, self::QUESTIONS);

        return response()->json([
            'questions' => $publicQuestions,
        ]);
    }

    /**
     * POST /api/submit-answers
     * Process selections, calculate hidden stress score, trigger LLM agent,
     * persist in database, and return formatted response.
     */
    public function submitAnswers(Request $request, StressAnalyzerService $analyzerService): JsonResponse
    {
        $questionIds = array_column(self::QUESTIONS, 'id');
        $questionCount = count(self::QUESTIONS);

        // Build mapping of ID to question details for quick lookup
        $questionsMap = [];
        foreach (self::QUESTIONS as $q) {
            $questionsMap[$q['id']] = $q;
        }

        // Validate payload structure
        $validator = Validator::make($request->all(), [
            'session_id' => ['required', 'string'],
            'answers' => ['required', 'array', 'size:' . $questionCount],
            'answers.*.question_id' => ['required', 'integer', 'distinct', 'in:' . implode(',', $questionIds)],
            'answers.*.selected_option_index' => ['required', 'integer'],
        ]);

        // Validate that option indices match valid options in static array
        $validator->after(function ($validator) use ($request, $questionsMap) {
            $answers = $request->input('answers');
            if (!is_array($answers)) {
                return;
            }

            foreach ($answers as $index => $answer) {
                if (!is_array($answer) || !isset($answer['question_id']) || !isset($answer['selected_option_index'])) {
                    continue;
                }

                $qId = $answer['question_id'];
                $optIdx = $answer['selected_option_index'];

                if (isset($questionsMap[$qId])) {
                    $validOptionIndexes = array_column($questionsMap[$qId]['options'], 'index');
                    if (!in_array($optIdx, $validOptionIndexes, true)) {
                        $validator->errors()->add(
                            "answers.{$index}.selected_option_index",
                            "The selected option index {$optIdx} is invalid for question ID {$qId}."
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Calculate total score and build answers summary
        $stressScore = 0;
        $answersSummary = [];

        foreach ($request->input('answers') as $answer) {
            $qId = $answer['question_id'];
            $optIdx = $answer['selected_option_index'];

            $q = $questionsMap[$qId];
            foreach ($q['options'] as $opt) {
                if ($opt['index'] === $optIdx) {
                    $stressScore += $opt['score'];
                    $answersSummary[$q['text']] = $opt['text'];
                    break;
                }
            }
        }

        // Ensure score doesn't exceed bounds (though math constraints limit it to 0-100)
        $stressScore = min(100, max(0, $stressScore));

        // Call AI Service (triggers Official OverthinkAgent and LLM evaluation)
        $aiResult = $analyzerService->analyze($stressScore, $answersSummary);

        // Store result in database
        $sharedResult = SharedResult::create([
            'session_id' => $request->input('session_id'),
            'result_title' => $aiResult['title'],
            'result_text' => $aiResult['result'],
            'stress_score' => $stressScore,
            'metadata' => $aiResult['metadata'],
        ]);

        return response()->json([
            'uuid' => $sharedResult->uuid,
            'title' => $sharedResult->result_title,
            'result' => $sharedResult->result_text,
            'metadata' => $sharedResult->metadata,
        ], 200);
    }

    /**
     * GET /api/result/{uuid}
     * Public share retrieval endpoint.
     */
    public function showResult(string $uuid): JsonResponse
    {
        $result = SharedResult::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'title' => $result->result_title,
            'result' => $result->result_text,
            'metadata' => $result->metadata,
        ], 200);
    }
}
