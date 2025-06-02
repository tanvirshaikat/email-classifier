<?php

use App\Http\Controllers\EmailClassifierController;
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

Route::get('/', function () {
    return redirect()->route('emails.index');
});

Route::get('/emails', [EmailClassifierController::class, 'index'])->name('emails.index');
Route::get('/emails/upload', [EmailClassifierController::class, 'upload'])->name('emails.upload');
Route::post('/emails/process', [EmailClassifierController::class, 'process'])->name('emails.process');
Route::get('/emails/export-csv', [EmailClassifierController::class, 'exportCsv'])->name('emails.export-csv');
Route::get('/emails/filter', [EmailClassifierController::class, 'filter'])->name('emails.filter');
