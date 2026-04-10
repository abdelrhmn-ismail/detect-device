<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceDetectionController;
use App\Http\Controllers\Api\DeviceDataController;

Route::get('/', function () {
    return view('welcome');
});

// Device detection routes
Route::get('/detect-device', [DeviceDetectionController::class, 'detectDevice'])->name('detect.device');
Route::get('/devices-data', [DeviceDetectionController::class, 'getDevicesData'])->name('devices.data');
