<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SquarePaymentController;
Route::get('/', function () {
    return view('welcome');
});



// Payment Routes
Route::get('/pay', [SquarePaymentController::class, 'showForm'])->name('payment.form');
Route::post('/process-payment', [SquarePaymentController::class, 'process'])->name('payment.process');
