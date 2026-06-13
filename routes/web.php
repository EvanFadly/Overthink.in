<?php

use App\Models\SharedResult;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('quiz');
});

Route::get('/result/{uuid}', function (string $uuid) {
    $result = SharedResult::where('uuid', $uuid)->firstOrFail();
    return view('result', [
        'result' => $result
    ]);
})->name('result.show');

