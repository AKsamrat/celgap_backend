<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\LawJournalController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\SpringWorkshopTraineeController;
use Illuminate\Support\Facades\Auth;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::get('/categories', [CategoryController::class, 'index']);
// Route::get('/posts', [PostController::class, 'index']);
// Route::get('/posts/{id}', [PostController::class, 'show']);


//blog routes
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{id}', [BlogController::class, 'show']);

//news routes
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

//conference routes
Route::get('/conferences', [ConferenceController::class, 'index']);
Route::get('/conferences/{conference}', [ConferenceController::class, 'show']);


//speaker routes
Route::get('/speakers', [SpeakerController::class, 'index']);
Route::get('/speakers/{speaker}', [SpeakerController::class, 'show']);

//spring workshop trainee routes
Route::get('/spring-workshop-trainees', [SpringWorkshopTraineeController::class, 'index']);
Route::get('/spring-workshop-trainees/{springWorkshopTrainee}', [SpringWorkshopTraineeController::class, 'show']);

// Protected Routes (Require Auth)
Route::middleware('auth:sanctum')->get('/users/{id}', [AuthController::class, 'show']);
Route::middleware('auth:sanctum')->get('/user', fn(Request $r) => Auth::user());

//law_journal routes
Route::get('/law-journals', [LawJournalController::class, 'index']);
Route::get('/law-journals/{lawJournal}', [LawJournalController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/blogs', [BlogController::class, 'store']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //Blog Operations
    Route::put('/blogs/{id}', [BlogController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
    Route::post('/blogs/userBlogs', [BlogController::class, 'userBlogs']);
    Route::patch('/blogs/{id}/status', [BlogController::class, 'updateStatus']);

    //news operations

    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);

    //conference operations
    Route::post('/conferences', [ConferenceController::class, 'store']);
    Route::put('/conferences/{conference}', [ConferenceController::class, 'update']);
    Route::delete('/conferences/{conference}', [ConferenceController::class, 'destroy']);

    //speaker operations
    Route::post('/speakers', [SpeakerController::class, 'store']);
    Route::put('/speakers/{speaker}', [SpeakerController::class, 'update']);
    Route::delete('/speakers/{speaker}', [SpeakerController::class, 'destroy']);

    //spring workshop trainee operations
    Route::post('/spring-workshop-trainees', [SpringWorkshopTraineeController::class, 'store']);
    Route::put('/spring-workshop-trainees/{springWorkshopTrainee}', [SpringWorkshopTraineeController::class, 'update']);
    Route::delete('/spring-workshop-trainees/{springWorkshopTrainee}', [SpringWorkshopTraineeController::class, 'destroy']);

    //law_journal routes
    Route::post('/law-journals', [LawJournalController::class, 'store']);
    Route::put('/law-journals/{lawJournal}', [LawJournalController::class, 'update']);
    Route::delete('/law-journals/{lawJournal}', [LawJournalController::class, 'destroy']);

    // Admin & User Operations
    // Route::apiResource('categories', CategoryController::class)->except(['index']);
    // Route::apiResource('posts', PostController::class)->except(['index', 'show']);
});
