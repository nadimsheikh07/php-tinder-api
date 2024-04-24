<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::post('/signup', [UserController::class, 'signup']);
Route::post('/signin', [UserController::class, 'signin']);

Route::middleware('auth:api')->group(function () {
    Route::get('/recommended-users', [UserController::class, 'recommendedUsers']);
    Route::post('/like-user/{user}', [UserController::class, 'like']);
    Route::resource('users', UserController::class);
});
