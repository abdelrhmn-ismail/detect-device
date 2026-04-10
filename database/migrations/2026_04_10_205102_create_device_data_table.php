<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_data', function (Blueprint $table) {
            $table->id();
            
            // Browser Information
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('browser_major_version')->nullable();
            $table->string('user_agent')->nullable();
            
            // Operating System
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('os_platform')->nullable();
            
            // Device Information
            $table->string('device_type')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_tablet')->default(false);
            $table->boolean('is_desktop')->default(false);
            $table->boolean('is_bot')->default(false);
            
            // Display & Screen
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->integer('viewport_width')->nullable();
            $table->integer('viewport_height')->nullable();
            $table->integer('color_depth')->nullable();
            $table->integer('pixel_ratio')->nullable();
            $table->string('screen_orientation')->nullable();
            
            // Network Information
            $table->string('ip_address')->nullable();
            $table->string('ip_v6')->nullable();
            $table->string('connection_type')->nullable();
            $table->string('effective_type')->nullable();
            $table->integer('downlink')->nullable();
            $table->integer('rtt')->nullable();
            $table->boolean('save_data')->nullable();
            
            // Hardware Information
            $table->integer('cpu_cores')->nullable();
            $table->integer('device_memory')->nullable();
            $table->string('gpu_vendor')->nullable();
            $table->string('gpu_renderer')->nullable();
            
            // Language & Locale
            $table->string('language')->nullable();
            $table->string('languages')->nullable();
            $table->string('timezone')->nullable();
            $table->integer('timezone_offset')->nullable();
            
            // Capabilities
            $table->boolean('cookies_enabled')->nullable();
            $table->boolean('do_not_track')->nullable();
            $table->boolean('webgl_supported')->nullable();
            $table->boolean('canvas_supported')->nullable();
            $table->boolean('webRTC_supported')->nullable();
            $table->boolean('touch_support')->nullable();
            $table->integer('max_touch_points')->nullable();
            
            // Additional Data
            $table->json('client_hints')->nullable();
            $table->json('extra_data')->nullable();
            
            // Indexes for better query performance
            $table->index(['browser_name', 'os_name', 'device_type']);
            $table->index('ip_address');
            $table->index('created_at');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_data');
    }
};
