<?php

use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

Route::post('/start-session', [SessionController::class, 'start']);

// Route for testing the validation middleware
Route::middleware('validate.session')->get('/test-middleware', function () {
    return response()->json(['status' => 'success']);
});
