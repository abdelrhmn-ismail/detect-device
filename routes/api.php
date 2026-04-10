<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceDataController;

Route::get('/devices-data/{id}', [DeviceDataController::class, 'show'])->name('api.devices.show');
