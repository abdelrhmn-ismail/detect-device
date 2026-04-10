<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceDataController extends Controller
{
    /**
     * Display the specified device data
     */
    public function show($id)
    {
        try {
            $device = DeviceData::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $device,
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve device data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve device data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
