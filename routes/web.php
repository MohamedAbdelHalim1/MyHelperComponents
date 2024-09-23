<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

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

////// Dynamically upload Images
Route::get('/upload', [FileUploadController::class, 'index']);
Route::post('/upload', [FileUploadController::class, 'store']);
Route::post('/upload-all', [FileUploadController::class, 'uploadAll'])->name('image.uploadAll');
Route::delete('/delete/{id}', [FileUploadController::class, 'destroy']);