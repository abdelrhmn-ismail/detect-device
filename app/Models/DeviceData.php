<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceData extends Model
{
    use HasFactory;

    protected $table = 'device_data';

    protected $fillable = [
        // Browser Information
        'browser_name',
        'browser_version',
        'browser_major_version',
        'user_agent',
        
        // Operating System
        'os_name',
        'os_version',
        'os_platform',
        
        // Device Information
        'device_type',
        'device_brand',
        'device_model',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'is_bot',
        
        // Display & Screen
        'screen_width',
        'screen_height',
        'viewport_width',
        'viewport_height',
        'color_depth',
        'pixel_ratio',
        'screen_orientation',
        
        // Network Information
        'ip_address',
        'ip_v6',
        'connection_type',
        'effective_type',
        'downlink',
        'rtt',
        'save_data',
        
        // Hardware Information
        'cpu_cores',
        'device_memory',
        'gpu_vendor',
        'gpu_renderer',
        
        // Language & Locale
        'language',
        'languages',
        'timezone',
        'timezone_offset',
        
        // Capabilities
        'cookies_enabled',
        'do_not_track',
        'webgl_supported',
        'canvas_supported',
        'webRTC_supported',
        'touch_support',
        'max_touch_points',
        
        // Additional Data
        'client_hints',
        'extra_data',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'is_bot' => 'boolean',
        'save_data' => 'boolean',
        'cookies_enabled' => 'boolean',
        'do_not_track' => 'boolean',
        'webgl_supported' => 'boolean',
        'canvas_supported' => 'boolean',
        'webRTC_supported' => 'boolean',
        'touch_support' => 'boolean',
        'client_hints' => 'array',
        'extra_data' => 'array',
    ];
}
