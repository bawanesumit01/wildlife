<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReptileController;
use App\Http\Controllers\SnakeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('optimize', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return 'Done';
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware('auth')->group(function () {
    // user
    Route::get('/user-list', [RegisteredUserController::class, 'showUser'])->name('user.list');
    Route::get('/user/{id}', [RegisteredUserController::class, 'userDetail'])->name('user.detail');

    // animal
    Route::get('/add-animal', [AnimalController::class, 'index'])->name('add-animal');
    Route::post('/animal/store', [AnimalController::class, 'store'])->name('animal.store');
    Route::post('/update-status', [AnimalController::class, 'updateStatus'])->name('update-status');
    Route::delete('/animal/destroy/{id}', [AnimalController::class, 'destroy'])->name('animal.destroy');

    // reptile
    Route::get('/add-reptile', [ReptileController::class, 'index'])->name('add-reptile');
    Route::post('/reptile/store', [ReptileController::class, 'store'])->name('reptile.store');
    Route::post('/update-status-reptile', [ReptileController::class, 'updateStatus'])->name('update-status-reptile');
    Route::delete('/reptile/destroy/{id}', [ReptileController::class, 'destroy'])->name('reptile.destroy');

    // snake
    Route::get('/add-snake', [SnakeController::class, 'index'])->name('add-snake');
    Route::post('/snake/store', [SnakeController::class, 'store'])->name('snake.store');
    Route::post('/update-status-snake', [SnakeController::class, 'updateStatus'])->name('update-status-snake');
    Route::delete('/snake/destroy/{id}', [SnakeController::class, 'destroy'])->name('snake.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
