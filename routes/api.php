<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::post('/signup', [UserController::class, 'signup']);
Route::post('/signin', [UserController::class, 'signin']);
Route::get('/recommended-users', [UserController::class, 'recommendedUsers']);
Route::post('/like-user/{user}', [UserController::class, 'like'])->middleware('auth');

Route::resource('users', UserController::class)->middleware('auth');