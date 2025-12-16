<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// SIM900A posts GPS data here
Route::post('/location', [LocationController::class, 'store']);

// Frontend fetches latest location
Route::get('/location/latest', [LocationController::class, 'latest']);

// // Frontend fetches latest location
// Route::get('/location/latest', [LocationController::class, 'latest']);
