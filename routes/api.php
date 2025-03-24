<?php


use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/sales', [ApiController::class, 'sales']);
    Route::get('/orders', [ApiController::class, 'orders']);
    Route::get('/stocks', [ApiController::class, 'stocks']);
    Route::get('/incomes', [ApiController::class, 'incomes']);
});
