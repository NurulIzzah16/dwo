<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('schuser')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/api/profit-monthly', [DashboardController::class, 'profitMonthly']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');;

    Route::get('/purchasing', function () {
        return view('purchasing');
    });

    Route::get('/mondriansales', function () {
    return redirect()->away(
        'http://localhost:8080/mondrian/testpage.jsp?query=schsales'
    );
    });
    Route::get('/mondrianpurchasing', function () {
    return redirect()->away(
        'http://localhost:8080/mondrian/testpage.jsp?query=schpurchasing'
    );
    });
    // Route::get('/mondriansales', function () {
    //     return view('mondriansales');
    // });
    // Route::get('/mondrianpurchasing', function () {
    //     return view('mondrianpurchasing');
    // });
});
