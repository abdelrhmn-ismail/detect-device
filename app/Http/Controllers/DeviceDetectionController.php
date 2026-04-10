<?php

namespace App\Http\Controllers;

use App\Models\DeviceData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceDetectionController extends Controller
{
    /**
     * Detect and store comprehensive device information
     */
    public function detectDevice(Request $request)
    {
        try {
            $deviceInfo = $this->extractAdvancedDeviceInfo($request);
            
            $deviceData = DeviceData::create($deviceInfo);
            
            return response()->json([
                'success' => true,
                'message' => 'Device data detected and stored successfully',
                'data' => $deviceData,
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Device detection failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to detect device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all stored device data with filtering and pagination
     */
    public function getDevicesData(Request $request)
    {
        try {
            $query = DeviceData::query();
            
            // Apply filters if provided
            if ($request->has('browser')) {
                $query->where('browser_name', 'like', '%' . $request->browser . '%');
            }
            
            if ($request->has('os')) {
                $query->where('os_name', 'like', '%' . $request->os . '%');
            }
            
            if ($request->has('device_type')) {
                $query->where('device_type', 'like', '%' . $request->device_type . '%');
            }
            
            if ($request->has('is_mobile')) {
                $query->where('is_mobile', filter_var($request->is_mobile, FILTER_VALIDATE_BOOLEAN));
            }
            
            if ($request->has('is_desktop')) {
                $query->where('is_desktop', filter_var($request->is_desktop, FILTER_VALIDATE_BOOLEAN));
            }
            
            // Date range filter
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            
            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 50);
            $devices = $query->paginate($perPage);
            
            return view('devices-data', compact('devices'));
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve device data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve device data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract comprehensive device information using advanced methods
     */
    private function extractAdvancedDeviceInfo(Request $request): array
    {
        $userAgent = $request->header('User-Agent', '');
        $clientHints = $this->extractClientHints($request);
        
        // Combine User-Agent parsing with Client Hints for better accuracy
        $browserInfo = $this->parseBrowserInfo($userAgent, $clientHints);
        $osInfo = $this->parseOSInfo($userAgent, $clientHints);
        $deviceInfo = $this->parseDeviceInfo($userAgent, $clientHints);
        $networkInfo = $this->extractNetworkInfo($request);
        
        // Locale info from headers
        $localeInfo = $this->extractLocaleInfo($request);
        
        return array_merge(
            $browserInfo,
            $osInfo,
            $deviceInfo,
            $networkInfo,
            $localeInfo,
            [
                'user_agent' => $userAgent,
                'client_hints' => $clientHints,
                'extra_data' => $this->extractAdditionalData($request),
            ]
        );
    }

    /**
     * Extract Sec-CH-UA Client Hints for modern browsers
     */
    private function extractClientHints(Request $request): array
    {
        return [
            'mobile' => $request->header('Sec-CH-UA-Mobile'),
            'platform' => $request->header('Sec-CH-UA-Platform'),
            'platform_version' => $request->header('Sec-CH-UA-Platform-Version'),
            'model' => $request->header('Sec-CH-UA-Model'),
            'full_version_list' => $request->header('Sec-CH-UA-Full-Version-List'),
            'bitness' => $request->header('Sec-CH-UA-Bitness'),
            'wow64' => $request->header('Sec-CH-UA-WoW64'),
            'arch' => $request->header('Sec-CH-UA-Arch'),
            'form_factors' => $request->header('Sec-CH-UA-Form-Factors'),
        ];
    }

    /**
     * Parse browser information with advanced detection
     */
    private function parseBrowserInfo(string $userAgent, array $clientHints): array
    {
        $browserName = 'Unknown';
        $browserVersion = 'Unknown';
        $majorVersion = 'Unknown';
        
        // Try Client Hints first (more reliable for modern browsers)
        if (!empty($clientHints['full_version_list'])) {
            $versions = explode(',', $clientHints['full_version_list']);
            foreach ($versions as $version) {
                if (preg_match('/"([^"]+)";v="([^"]+)"/', trim($version), $matches)) {
                    if (stripos($matches[1], 'chrome') !== false || 
                        stripos($matches[1], 'edge') !== false ||
                        stripos($matches[1], 'firefox') !== false) {
                        $browserName = $matches[1];
                        $browserVersion = $matches[2];
                        $majorVersion = explode('.', $browserVersion)[0];
                        break;
                    }
                }
            }
        }
        
        // Fallback to User-Agent parsing
        if ($browserName === 'Unknown') {
            // Edge
            if (preg_match('/Edg[\/\s]([\d.]+)/i', $userAgent, $matches)) {
                $browserName = 'Microsoft Edge';
                $browserVersion = $matches[1];
                $majorVersion = explode('.', $browserVersion)[0];
            }
            // Chrome
            elseif (preg_match('/Chrome[\/\s]([\d.]+)/i', $userAgent, $matches) && 
                    !preg_match('/Edg/i', $userAgent)) {
                $browserName = 'Google Chrome';
                $browserVersion = $matches[1];
                $majorVersion = explode('.', $browserVersion)[0];
            }
            // Firefox
            elseif (preg_match('/Firefox[\/\s]([\d.]+)/i', $userAgent, $matches)) {
                $browserName = 'Mozilla Firefox';
                $browserVersion = $matches[1];
                $majorVersion = explode('.', $browserVersion)[0];
            }
            // Safari
            elseif (preg_match('/Version[\/\s]([\d.]+).*Safari/i', $userAgent, $matches)) {
                $browserName = 'Safari';
                $browserVersion = $matches[1];
                $majorVersion = explode('.', $browserVersion)[0];
            }
            // Opera
            elseif (preg_match('/OPR[\/\s]([\d.]+)/i', $userAgent, $matches)) {
                $browserName = 'Opera';
                $browserVersion = $matches[1];
                $majorVersion = explode('.', $browserVersion)[0];
            }
        }
        
        return [
            'browser_name' => $browserName,
            'browser_version' => $browserVersion,
            'browser_major_version' => $majorVersion,
        ];
    }

    /**
     * Parse operating system information
     */
    private function parseOSInfo(string $userAgent, array $clientHints): array
    {
        $osName = 'Unknown';
        $osVersion = 'Unknown';
        $osPlatform = 'Unknown';
        
        // Use Client Hints if available
        if (!empty($clientHints['platform'])) {
            $osName = trim($clientHints['platform'], '"');
            $osVersion = $clientHints['platform_version'] ? trim($clientHints['platform_version'], '"') : 'Unknown';
            $osPlatform = strpos(strtolower($osName), 'windows') !== false ? 'Windows' : 
                         (strpos(strtolower($osName), 'mac') !== false ? 'macOS' : 
                         (strpos(strtolower($osName), 'linux') !== false ? 'Linux' : 'Other'));
        } else {
            // Parse User-Agent for OS
            if (preg_match('/Windows NT ([\d.]+)/i', $userAgent, $matches)) {
                $osName = 'Windows';
                $osPlatform = 'Windows';
                $ntVersion = $matches[1];
                $osVersion = $this->getWindowsVersion($ntVersion);
            } elseif (preg_match('/Mac OS X ([\d_]+)/i', $userAgent, $matches)) {
                $osName = 'macOS';
                $osPlatform = 'macOS';
                $osVersion = str_replace('_', '.', $matches[1]);
            } elseif (preg_match('/Android ([\d.]+)/i', $userAgent, $matches)) {
                $osName = 'Android';
                $osPlatform = 'Linux';
                $osVersion = $matches[1];
            } elseif (preg_match('/iPhone OS ([\d_]+)/i', $userAgent, $matches)) {
                $osName = 'iOS';
                $osPlatform = 'iOS';
                $osVersion = str_replace('_', '.', $matches[1]);
            } elseif (preg_match('/iPad.*OS ([\d_]+)/i', $userAgent, $matches)) {
                $osName = 'iPadOS';
                $osPlatform = 'iPadOS';
                $osVersion = str_replace('_', '.', $matches[1]);
            } elseif (preg_match('/Linux/i', $userAgent)) {
                $osName = 'Linux';
                $osPlatform = 'Linux';
                $osVersion = 'Unknown';
            }
        }
        
        return [
            'os_name' => $osName,
            'os_version' => $osVersion,
            'os_platform' => $osPlatform,
        ];
    }

    /**
     * Parse device type and manufacturer information
     */
    private function parseDeviceInfo(string $userAgent, array $clientHints): array
    {
        $deviceType = 'Desktop';
        $deviceBrand = 'Unknown';
        $deviceModel = 'Unknown';
        $isMobile = false;
        $isTablet = false;
        $isDesktop = true;
        $isBot = false;
        
        // Check Client Hints
        if (!empty($clientHints['mobile'])) {
            $isMobile = $clientHints['mobile'] === '?1';
            $isDesktop = !$isMobile;
        }
        
        if (!empty($clientHints['model'])) {
            $deviceModel = trim($clientHints['model'], '"');
            $deviceBrand = $this->extractBrandFromModel($deviceModel);
        }
        
        // Parse User-Agent for device type
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
            if (preg_match('/iPad|Tablet/i', $userAgent)) {
                $deviceType = 'Tablet';
                $isTablet = true;
                $isMobile = false;
                $isDesktop = false;
            } else {
                $deviceType = 'Mobile';
                $isMobile = true;
                $isTablet = false;
                $isDesktop = false;
            }
        }
        
        // Detect specific brands
        if (preg_match('/Samsung|SM-|GT-/i', $userAgent)) {
            $deviceBrand = 'Samsung';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $deviceBrand = 'Apple';
            $deviceModel = 'iPhone';
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $deviceBrand = 'Apple';
            $deviceModel = 'iPad';
        } elseif (preg_match('/Huawei/i', $userAgent)) {
            $deviceBrand = 'Huawei';
        } elseif (preg_match('/Xiaomi|MI\s|Redmi/i', $userAgent)) {
            $deviceBrand = 'Xiaomi';
        } elseif (preg_match('/OnePlus/i', $userAgent)) {
            $deviceBrand = 'OnePlus';
        } elseif (preg_match('/Google|Pixel/i', $userAgent)) {
            $deviceBrand = 'Google';
        }
        
        // Detect bots
        if (preg_match('/bot|crawler|spider|slurp|teoma|scooter|archive|crawl|fetch|monitor/i', $userAgent)) {
            $isBot = true;
            $deviceType = 'Bot';
        }
        
        return [
            'device_type' => $deviceType,
            'device_brand' => $deviceBrand,
            'device_model' => $deviceModel,
            'is_mobile' => $isMobile,
            'is_tablet' => $isTablet,
            'is_desktop' => $isDesktop,
            'is_bot' => $isBot,
        ];
    }

    /**
     * Extract network information
     */
    private function extractNetworkInfo(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'ip_v6' => filter_var($request->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? $request->ip() : null,
            'connection_type' => $request->input('connection_type'),
            'effective_type' => $request->input('effective_type'),
            'downlink' => $request->input('downlink'),
            'rtt' => $request->input('rtt'),
            'save_data' => $request->input('save_data'),
        ];
    }

    /**
     * Extract locale and language information
     */
    private function extractLocaleInfo(Request $request): array
    {
        $languages = $request->header('Accept-Language', '');
        $primaryLanguage = explode(',', $languages)[0] ?? null;
        
        return [
            'language' => $primaryLanguage ? explode(';', $primaryLanguage)[0] : null,
            'languages' => $languages,
            'timezone' => $request->input('timezone'),
            'timezone_offset' => $request->input('timezone_offset'),
        ];
    }

    /**
     * Extract additional data
     */
    private function extractAdditionalData(Request $request): array
    {
        return [
            'referer' => $request->header('Referer'),
            'accept_encoding' => $request->header('Accept-Encoding'),
            'accept_language' => $request->header('Accept-Language'),
            'accept' => $request->header('Accept'),
            'http_protocol' => $request->getScheme(),
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'is_secure' => $request->secure(),
        ];
    }

    /**
     * Extract brand from device model
     */
    private function extractBrandFromModel(string $model): string
    {
        if (preg_match('/^(SM-|GT-)/i', $model)) return 'Samsung';
        if (preg_match('/^Pixel/i', $model)) return 'Google';
        if (preg_match('/^MI\s|Redmi/i', $model)) return 'Xiaomi';
        if (preg_match('/^ONEPLUS/i', $model)) return 'OnePlus';
        if (preg_match('/^HUAWEI|HW-/i', $model)) return 'Huawei';
        if (preg_match('/^OPPO/i', $model)) return 'Oppo';
        if (preg_match('/^VIVO/i', $model)) return 'Vivo';
        if (preg_match('/^Nokia/i', $model)) return 'Nokia';
        if (preg_match('/^Motorola| Moto/i', $model)) return 'Motorola';
        if (preg_match('/^LG/i', $model)) return 'LG';
        if (preg_match('/^Sony/i', $model)) return 'Sony';
        
        return 'Unknown';
    }

    /**
     * Map Windows NT version to Windows version name
     */
    private function getWindowsVersion(string $ntVersion): string
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
            '5.1' => 'XP',
            '5.0' => '2000',
        ];
        
        return $versions[$ntVersion] ?? $ntVersion;
    }
}
