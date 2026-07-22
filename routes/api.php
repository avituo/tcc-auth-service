<?php

use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthApiController::class, 'token']);
