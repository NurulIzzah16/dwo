<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);

Route::get('/api/profit-monthly', [DashboardController::class, 'profitMonthly']);

Route::get('/purchasing', function () {
    return view('purchasing');
});