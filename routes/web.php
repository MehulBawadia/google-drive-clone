<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FileController;
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
    Route::get('/my-files/{folder?}', [FileController::class, 'index'])->where('folder', '(.*)')->name('myFiles');
    Route::post('/folder/create', [FileController::class, 'createFolder'])->name('folder.create');
    Route::post('/files', [FileController::class, 'storeFiles'])->name('files.store');
    Route::delete('/files', [FileController::class, 'destroy'])->name('files.destroy');

    Route::get('/trash', [FileController::class, 'trash'])->name('trash');
    Route::get('/shared-with-me', [FileController::class, 'sharedWithMe'])->name('sharedWithMe');
    Route::get('/shared-by-me', [FileController::class, 'sharedByMe'])->name('sharedByMe');
    Route::post('/files/restore', [FileController::class, 'restore'])->name('files.restore');
    Route::delete('/files/delete-forever', [FileController::class, 'deleteForever'])->name('files.deleteForever');

    Route::post('/files/toggle-favourite', [FileController::class, 'toggleFavourite'])->name('files.toggleFavourite');
    Route::post('/files/share', [FileController::class, 'share'])->name('files.share');

    Route::name('files')->prefix('files')->controller(DownloadController::class)->group(function () {
        Route::get('/download', 'fromMyFiles')->name('.download');
        Route::get('/download/shared-with-me', 'sharedWithMe')->name('.downloadSharedWithMe');
        Route::get('/download/shared-by-me', 'sharedByMe')->name('.downloadSharedByMe');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
