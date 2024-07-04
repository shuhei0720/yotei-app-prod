<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LineController;
use App\Http\Controllers\TutorialController;

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
    
    Route::resource('events', EventController::class)->except(['show']);
    Route::get('/events/user', [EventController::class, 'userEvents'])->name('events.user');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
});

Route::view('/privacy-policy', 'privacy')->name('privacy.policy');
Route::view('/terms-of-service', 'terms')->name('terms.service');
Route::get('auth/line', [LineController::class, 'redirectToProvider'])->name('auth.line');
Route::get('auth/line/callback', [LineController::class, 'handleProviderCallback']);
Route::get('/tutorial', [TutorialController::class, 'show'])->name('tutorial.show')->middleware('auth');
Route::post('/tutorial/complete', [TutorialController::class, 'complete'])->name('tutorial.complete')->middleware('auth');

require __DIR__.'/auth.php';