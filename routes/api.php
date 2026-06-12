<?php

use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

Route::post('/start-session', [SessionController::class, 'start']);

// Route for testing the validation middleware
Route::middleware('validate.session')->get('/test-middleware', function () {
    return response()->json(['status' => 'success']);
});

// Quiz & AI Analysis Routes
Route::get('/questions', [QuizApiController::class, 'index']);
Route::middleware(['validate.session', 'throttle:5,1'])->post('/submit-answers', [QuizApiController::class, 'submitAnswers']);
Route::get('/result/{uuid}', [QuizApiController::class, 'showResult'])->name('share.show');
