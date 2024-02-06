<?php

use App\Http\Controllers\MyFilesController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-files/{folder?}', [MyFilesController::class, 'index'])->where('folder', '(.*)')->name('myFiles');
    Route::post('/folder/create', [MyFilesController::class, 'createFolder'])->name('folder.create');
    Route::post('/files', [MyFilesController::class, 'storeFiles'])->name('files.store');
    Route::delete('/files', [MyFilesController::class, 'destroy'])->name('files.destroy');
    Route::get('/files/download', [MyFilesController::class, 'download'])->name('files.download');

    Route::get('/trash', [MyFilesController::class, 'trash'])->name('trash');
    Route::get('/shared-with-me', [MyFilesController::class, 'sharedWithMe'])->name('sharedWithMe');
    Route::post('/files/restore', [MyFilesController::class, 'restore'])->name('files.restore');
    Route::delete('/files/delete-forever', [MyFilesController::class, 'deleteForever'])->name('files.deleteForever');

    Route::post('/files/toggle-favourite', [MyFilesController::class, 'toggleFavourite'])->name('files.toggleFavourite');
    Route::post('/files/share', [MyFilesController::class, 'share'])->name('files.share');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
