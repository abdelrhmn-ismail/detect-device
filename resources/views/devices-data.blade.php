<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Device Data</h1>
            <form action="{{ route('detect.device') }}" method="GET" class="inline">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Detect My Device
                </button>
            </form>
        </div>

        @if($devices->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500 text-lg">No device data found.</p>
                <p class="text-gray-400 mt-2">Click "Detect My Device" to add your device information.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">Browser</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">OS</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">Device Type</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">Brand</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">IP Address</th>
                            <th class="px-4 py-3 border-b text-left text-xs font-semibold text-gray-600 uppercase">Created At</th>
                            <th class="px-4 py-3 border-b text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 border-b">{{ $device->id }}</td>
                                <td class="px-4 py-3 border-b">
                                    <span class="font-medium">{{ $device->browser_name }}</span>
                                    @if($device->browser_version && $device->browser_version !== 'Unknown')
                                        <span class="text-gray-500 text-sm ml-1">{{ $device->browser_version }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b">
                                    <span class="font-medium">{{ $device->os_name }}</span>
                                    @if($device->os_version && $device->os_version !== 'Unknown')
                                        <span class="text-gray-500 text-sm ml-1">{{ $device->os_version }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b">
                                    @if($device->device_type === 'Mobile')
                                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">📱 Mobile</span>
                                    @elseif($device->device_type === 'Tablet')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">📱 Tablet</span>
                                    @elseif($device->device_type === 'Bot')
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">🤖 Bot</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">💻 Desktop</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b">{{ $device->device_brand ?? '-' }}</td>
                                <td class="px-4 py-3 border-b font-mono text-sm">{{ $device->ip_address ?? '-' }}</td>
                                <td class="px-4 py-3 border-b text-sm text-gray-600">{{ $device->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 border-b text-center">
                                    <button onclick="showDeviceDetails({{ $device->id }})" 
                                            class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded text-sm transition-colors">
                                        Show More
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

    <!-- Device Details Modal -->
    <div id="deviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
                <h2 class="text-2xl font-bold text-gray-800">Device Details</h2>
                <button onclick="closeModal()" class="text-gray-600 hover:text-gray-800 text-2xl font-bold">&times;</button>
            </div>

            <!-- Modal Body -->
            <div id="modalContent" class="overflow-y-auto max-h-[calc(90vh-140px)] p-6">
                <p class="text-gray-500 text-center">Loading...</p>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t bg-gray-50 text-right">
                <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function showDeviceDetails(deviceId) {
            const modal = document.getElementById('deviceModal');
            const content = document.getElementById('modalContent');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            fetch(`/api/devices-data/${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = renderDeviceDetails(data.data);
                    } else {
                        content.innerHTML = '<p class="text-red-500 text-center">Failed to load device details</p>';
                    }
                })
                .catch(error => {
                    content.innerHTML = '<p class="text-red-500 text-center">Error loading device details</p>';
                    console.error('Error:', error);
                });
        }

        function closeModal() {
            const modal = document.getElementById('deviceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function renderDeviceDetails(device) {
            return `
                <div class="space-y-6">
                    <!-- Browser Information -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-blue-800 mb-3">🌐 Browser Information</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Browser:</span> <span class="text-gray-600">${device.browser_name || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Version:</span> <span class="text-gray-600">${device.browser_version || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Major Version:</span> <span class="text-gray-600">${device.browser_major_version || '-'}</span></div>
                        </div>
                    </div>

                    <!-- Operating System -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-green-800 mb-3">💻 Operating System</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">OS Name:</span> <span class="text-gray-600">${device.os_name || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Version:</span> <span class="text-gray-600">${device.os_version || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Platform:</span> <span class="text-gray-600">${device.os_platform || '-'}</span></div>
                        </div>
                    </div>

                    <!-- Device Information -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-purple-800 mb-3">📱 Device Information</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Device Type:</span> <span class="text-gray-600">${device.device_type || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Brand:</span> <span class="text-gray-600">${device.device_brand || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Model:</span> <span class="text-gray-600">${device.device_model || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Is Mobile:</span> <span class="text-gray-600">${device.is_mobile ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Is Tablet:</span> <span class="text-gray-600">${device.is_tablet ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Is Desktop:</span> <span class="text-gray-600">${device.is_desktop ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Is Bot:</span> <span class="text-gray-600">${device.is_bot ? '✅ Yes' : '❌ No'}</span></div>
                        </div>
                    </div>

                    <!-- Display & Screen -->
                    ${device.screen_width || device.screen_height ? `
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-yellow-800 mb-3">🖥️ Display & Screen</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Screen Width:</span> <span class="text-gray-600">${device.screen_width ? device.screen_width + 'px' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Screen Height:</span> <span class="text-gray-600">${device.screen_height ? device.screen_height + 'px' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Viewport Width:</span> <span class="text-gray-600">${device.viewport_width ? device.viewport_width + 'px' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Viewport Height:</span> <span class="text-gray-600">${device.viewport_height ? device.viewport_height + 'px' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Color Depth:</span> <span class="text-gray-600">${device.color_depth ? device.color_depth + '-bit' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Pixel Ratio:</span> <span class="text-gray-600">${device.pixel_ratio || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Orientation:</span> <span class="text-gray-600">${device.screen_orientation || '-'}</span></div>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Network Information -->
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-indigo-800 mb-3">🌍 Network Information</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">IP Address:</span> <span class="text-gray-600 font-mono">${device.ip_address || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">IPv6:</span> <span class="text-gray-600 font-mono">${device.ip_v6 || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Connection Type:</span> <span class="text-gray-600">${device.connection_type || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Effective Type:</span> <span class="text-gray-600">${device.effective_type || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Downlink:</span> <span class="text-gray-600">${device.downlink ? device.downlink + ' Mbps' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">RTT:</span> <span class="text-gray-600">${device.rtt ? device.rtt + 'ms' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Save Data:</span> <span class="text-gray-600">${device.save_data ? '✅ Yes' : '❌ No'}</span></div>
                        </div>
                    </div>

                    <!-- Hardware Information -->
                    ${device.cpu_cores || device.device_memory || device.gpu_vendor ? `
                    <div class="bg-red-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-red-800 mb-3">⚙️ Hardware Information</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">CPU Cores:</span> <span class="text-gray-600">${device.cpu_cores || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Device Memory:</span> <span class="text-gray-600">${device.device_memory ? device.device_memory + ' GB' : '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">GPU Vendor:</span> <span class="text-gray-600">${device.gpu_vendor || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">GPU Renderer:</span> <span class="text-gray-600">${device.gpu_renderer || '-'}</span></div>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Language & Locale -->
                    <div class="bg-teal-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-teal-800 mb-3">🌐 Language & Locale</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Language:</span> <span class="text-gray-600">${device.language || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">All Languages:</span> <span class="text-gray-600">${device.languages || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Timezone:</span> <span class="text-gray-600">${device.timezone || '-'}</span></div>
                            <div><span class="font-semibold text-gray-700">Timezone Offset:</span> <span class="text-gray-600">${device.timezone_offset ? 'UTC' + (device.timezone_offset >= 0 ? '+' : '') + (device.timezone_offset / 60) : '-'}</span></div>
                        </div>
                    </div>

                    <!-- Capabilities -->
                    <div class="bg-orange-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-orange-800 mb-3">🔧 Capabilities</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Cookies Enabled:</span> <span class="text-gray-600">${device.cookies_enabled ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Do Not Track:</span> <span class="text-gray-600">${device.do_not_track ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">WebGL Supported:</span> <span class="text-gray-600">${device.webgl_supported ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Canvas Supported:</span> <span class="text-gray-600">${device.canvas_supported ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">WebRTC Supported:</span> <span class="text-gray-600">${device.webRTC_supported ? '✅ Yes' : '❌ No'}</span></div>
                            <div><span class="font-semibold text-gray-700">Touch Support:</span> <span class="text-gray-600">${device.touch_support ? '✅ Yes (' + (device.max_touch_points || 'N/A') + ' points)' : '❌ No'}</span></div>
                        </div>
                    </div>

                    <!-- User Agent -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-3">🔍 User Agent</h3>
                        <p class="text-gray-600 text-sm break-all font-mono">${device.user_agent || '-'}</p>
                    </div>

                    <!-- Metadata -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-3">📅 Metadata</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="font-semibold text-gray-700">Created At:</span> <span class="text-gray-600">${new Date(device.created_at).toLocaleString()}</span></div>
                            <div><span class="font-semibold text-gray-700">Updated At:</span> <span class="text-gray-600">${new Date(device.updated_at).toLocaleString()}</span></div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Close modal on outside click
        document.getElementById('deviceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
