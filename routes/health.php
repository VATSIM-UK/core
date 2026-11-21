<?php

use App\Http\Controllers\Health\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('up', [HealthController::class, 'up'])->name('health.up');
Route::get('health', HealthController::class)->name('health.index');
