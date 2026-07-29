<?php

use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/token', [TokenController::class, 'store'])
        ->middleware('throttle:auth-token')
        ->name('auth.token.store');
});
