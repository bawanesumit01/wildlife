<?php

use App\Http\Controllers\API\AnimalEntryController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PanchnamaController;
use App\Http\Controllers\API\ReptileEntryController;
use App\Http\Controllers\API\RoadKillController;
use App\Http\Controllers\API\SnakeBiteController;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend-otp');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->post('/logout', [AuthController::class, 'logout']);



Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/animal-entry', [AnimalEntryController::class, 'Animal']);
    Route::get('/animal-data', [AnimalEntryController::class, 'getAllEntries']);
    Route::post('/reptile-entry', [ReptileEntryController::class, 'Reptile']);
    Route::get('/reptile-data', [ReptileEntryController::class, 'getAllEntries']);
    Route::post('/roadkill-entry', [RoadKillController::class, 'Roadkill']);
    Route::get('/roadkill-data', [RoadKillController::class, 'getAllEntries']);
    Route::post('/snakebite-entry', [SnakeBiteController::class, 'SnakeBite']);
    Route::get('/snakebite-data', [SnakeBiteController::class, 'getAllEntries']);
    Route::post('/panchnama-entry', [PanchnamaController::class, 'Panchnama']);
    Route::get('/panchnama-data', [PanchnamaController::class, 'getAllEntries']);
    Route::get('/userdata', [AuthController::class, 'userdata']);
});

