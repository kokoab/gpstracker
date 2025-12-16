<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// Map view
Route::get('/', [LocationController::class, 'map']);
