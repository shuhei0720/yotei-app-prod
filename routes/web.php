<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('teams', TeamController::class);
    Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
    Route::post('/teams/leave', [TeamController::class, 'leave'])->name('teams.leave');
    Route::resource('events', EventController::class);
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
});

require __DIR__.'/auth.php';