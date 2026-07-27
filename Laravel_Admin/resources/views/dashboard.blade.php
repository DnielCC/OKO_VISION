@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Video Feed Section -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-video mr-3 text-cyan-400"></i>
                    Monitor de Acceso en Vivo
                </h3>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-300">En Vivo</span>
                </div>
            </div>
            
            <!-- Video Feed -->
            <div class="relative bg-gray-900 rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
                <!-- Webcam Video Element -->
                <video id="webcam" autoplay playsinline class="w-full h-full object-cover"></video>
                <!-- Canvas for drawing detection boxes -->
                <canvas id="detectionCanvas" class="absolute inset-0 w-full h-full"></canvas>
                
                <!-- AI Detection Overlay -->
                <div id="detectionOverlay" class="absolute inset-0 pointer-events-none"></div>
                
                <!-- Detection Counter Panel -->
                <div class="absolute top-4 left-4 bg-black/70 px-4 py-2 rounded-lg">
                    <h4 class="text-white text-sm font-semibold mb-2">Detecciones en Tiempo Real</h4>
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 bg-cyan-400 rounded-full"></span>
                            <span class="text-gray-300 text-xs">Personas:</span>
                            <span id="person-count" class="text-white font-bold">0</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                            <span class="text-gray-300 text-xs">Vehículos:</span>
                            <span id="vehicle-count" class="text-white font-bold">0</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                            <span class="text-gray-300 text-xs">Otros:</span>
                            <span id="other-count" class="text-white font-bold">0</span>
                        </div>
                    </div>
                </div>
                
                <!-- Camera Controls -->
                <div class="absolute bottom-4 left-4 flex space-x-2">
                    <button class="p-2 bg-black/50 rounded-lg hover:bg-black/70 transition-colors">
                        <i class="fas fa-camera text-white"></i>
                    </button>
                    <button class="p-2 bg-black/50 rounded-lg hover:bg-black/70 transition-colors">
                        <i class="fas fa-record-vinyl text-red-500"></i>
                    </button>
                    <button class="p-2 bg-black/50 rounded-lg hover:bg-black/70 transition-colors">
                        <i class="fas fa-expand text-white"></i>
                    </button>
                </div>
                
                <!-- Timestamp -->
                <div class="absolute top-4 right-4 bg-black/70 px-3 py-1 rounded-lg">
                    <span class="text-white text-sm font-mono" id="timestamp"></span>
                </div>
            </div>
            
            <!-- Camera Selection -->
            <div class="mt-4 grid grid-cols-4 gap-2">
                <button class="p-2 bg-cyan-400/20 border border-cyan-400 rounded-lg text-cyan-400 hover:bg-cyan-400/30 transition-colors">
                    <i class="fas fa-video mr-2"></i>Cámara 1
                </button>
                <button class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-600 transition-colors">
                    <i class="fas fa-video mr-2"></i>Cámara 2
                </button>
                <button class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-600 transition-colors">
                    <i class="fas fa-video mr-2"></i>Cámara 3
                </button>
                <button class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-600 transition-colors">
                    <i class="fas fa-video mr-2"></i>Cámara 4
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Accesos Hoy</p>
                        <p id="accesos-hoy" class="text-2xl font-bold text-white">{{ $accesos_hoy }}</p>
                        <p class="text-green-400 text-sm mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>En tiempo real
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Vehículos Registrados</p>
                        <p class="text-2xl font-bold text-white">{{ $vehiculos_registrados }}</p>
                        <p class="text-cyan-400 text-sm mt-1">
                            <i class="fas fa-car mr-1"></i>Base de datos activa
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-car text-cyan-400 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Alertas Activas</p>
                        <p id="alertas-activas" class="text-2xl font-bold text-white">{{ $alertas_activas }}</p>
                        <p class="text-red-400 text-sm mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Requieren atención
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-red-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Sections -->
    <div class="space-y-6 lg:col-span-1">
        <!-- Recent Access Logs -->
        <div class="card">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-history mr-2 text-cyan-400"></i>
                Últimos Accesos
            </h3>
            <div id="ultimos-accesos" class="space-y-4">
                @foreach($ultimos_accesos as $acceso)
                <div class="flex items-center p-3 bg-gray-900/50 rounded-lg border border-gray-700">
                    <div class="w-10 h-10 bg-cyan-400/10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas {{ $acceso->access_type == 'ENTRY' ? 'fa-sign-in-alt text-green-400' : 'fa-sign-out-alt text-yellow-400' }}"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">{{ $acceso->vehicle_plate }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($acceso->access_time)->format('H:i:s') }} - {{ $acceso->access_type }}</p>
                    </div>
                    @if($acceso->is_authorized)
                        <span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">OK</span>
                    @else
                        <span class="text-xs text-red-400 bg-red-400/10 px-2 py-1 rounded">DENY</span>
                    @endif
                </div>
                @endforeach
            </div>
            <a href="{{ route('reportes') }}" class="block text-center mt-4 text-sm text-cyan-400 hover:text-cyan-300 transition-colors">
                Ver historial completo
            </a>
        </div>
        
        <!-- Active Alerts -->
        <div class="card">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-red-400"></i>
                Alertas Críticas
            </h3>
            <div id="ultimas-alertas" class="space-y-4">
                @foreach($ultimas_alertas as $alerta)
                <div class="p-3 bg-red-400/5 border-l-4 {{ $alerta->severity == 'CRITICAL' ? 'border-red-500' : 'border-yellow-500' }} rounded-r-lg">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-semibold text-white">{{ $alerta->title }}</p>
                        <span class="text-[10px] text-gray-400">{{ $alerta->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $alerta->description }}</p>
                </div>
                @endforeach
            </div>
            <a href="{{ route('alertas') }}" class="block text-center mt-4 text-sm text-red-400 hover:text-red-300 transition-colors">
                Ver todas las alertas
            </a>
        </div>
    </div>
</div>

<!-- Activity Timeline -->
<div class="mt-6">
    <div class="card">
        <h3 class="text-lg font-semibold text-white mb-4">Actividad Reciente del Sistema</h3>
        <div id="actividad-reciente" class="space-y-4">
            @if($actividad_reciente && count($actividad_reciente) > 0)
                @foreach($actividad_reciente as $actividad)
                    @php
                        $nivel = $actividad['nivel'] ?? 'info';
                        $colorMap = [
                            'success' => ['bg' => 'bg-green-400/20', 'text' => 'text-green-400', 'icon' => 'fa-check'],
                            'warning' => ['bg' => 'bg-yellow-400/20', 'text' => 'text-yellow-400', 'icon' => 'fa-exclamation-triangle'],
                            'danger'  => ['bg' => 'bg-red-400/20',    'text' => 'text-red-400',    'icon' => 'fa-exclamation-circle'],
                            'info'    => ['bg' => 'bg-cyan-400/20',   'text' => 'text-cyan-400',   'icon' => 'fa-info-circle'],
                        ];
                        $color = $colorMap[$nivel] ?? $colorMap['info'];
                    @endphp
                    <div class="flex items-start space-x-3 pb-3 border-b border-gray-700">
                        <div class="w-8 h-8 {{ $color['bg'] }} rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $color['icon'] }} {{ $color['text'] }} text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-white text-sm">{{ $actividad['titulo'] }}</p>
                            <p class="text-gray-400 text-xs mt-1">
                                {{ $actividad['descripcion'] }}
                                @if(!empty($actividad['time_human']))
                                    · {{ $actividad['time_human'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            @else
                <div data-empty-state class="flex flex-col items-center justify-center py-8 text-center space-y-3">
                    <div class="w-10 h-10 bg-gray-600/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-gray-500"></i>
                    </div>
                    <p class="text-gray-400 text-sm">No hay actividad reciente registrada en el sistema.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Update timestamp
function updateTimestamp() {
    const now = new Date();
    const timestamp = now.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const timestampEl = document.getElementById('timestamp');
    if (timestampEl) timestampEl.textContent = timestamp;
}

updateTimestamp();
setInterval(updateTimestamp, 1000);

// Dashboard state
let dashboardState = {
    accesosHoy: {{ $accesos_hoy }},
    alertasActivas: {{ $alertas_activas }},
    detectedPlates: new Set() // To avoid duplicate detections
};

// Webcam and YOLO integration
let video = document.getElementById('webcam');
let canvas = document.getElementById('detectionCanvas');
let ctx = canvas ? canvas.getContext('2d') : null;
let detectionOverlay = document.getElementById('detectionOverlay');
let isProcessing = false;
let apiToken = @json($api_token); // Get token from Laravel
const API_URL = 'http://localhost:8002'; // API backend URL

// Initialize webcam
async function initWebcam() {
    if (!video) return;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        };
    } catch (err) {
        console.error("Error accessing webcam:", err);
        alert("No se pudo acceder a la cámara. Por favor, permite el acceso.");
    }
}

// Generate a random plate for simulation
function generateRandomPlate() {
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    let plate = '';
    for (let i = 0; i < 3; i++) {
        plate += letters.charAt(Math.floor(Math.random() * letters.length));
    }
    plate += '-';
    for (let i = 0; i < 3; i++) {
        plate += numbers.charAt(Math.floor(Math.random() * numbers.length));
    }
    return plate;
}

// Update dashboard with new access
function updateDashboardWithAccess(plate, isAuthorized, label) {
    // Update "Accesos Hoy" count
    dashboardState.accesosHoy++;
    const accesosHoyEl = document.getElementById('accesos-hoy');
    if (accesosHoyEl) {
        accesosHoyEl.textContent = dashboardState.accesosHoy;
    }

    // Add to "Últimos Accesos"
    const ultimosAccesosEl = document.getElementById('ultimos-accesos');
    if (ultimosAccesosEl) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        const newAccessHtml = `
            <div class="flex items-center p-3 bg-gray-900/50 rounded-lg border border-gray-700 opacity-0 transition-opacity duration-500">
                <div class="w-10 h-10 bg-cyan-400/10 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-sign-in-alt text-green-400"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-white">${plate}</p>
                    <p class="text-xs text-gray-400">${timeStr} - ENTRY</p>
                </div>
                ${isAuthorized 
                    ? '<span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">OK</span>' 
                    : '<span class="text-xs text-red-400 bg-red-400/10 px-2 py-1 rounded">DENY</span>'}
            </div>
        `;
        
        ultimosAccesosEl.insertAdjacentHTML('afterbegin', newAccessHtml);
        
        // Remove last item if more than 5
        const items = ultimosAccesosEl.children;
        if (items.length > 5) {
            items[items.length - 1].remove();
        }
        
        // Fade in new item
        setTimeout(() => {
            ultimosAccesosEl.firstElementChild.style.opacity = '1';
        }, 100);
    }

    // Add to "Actividad Reciente del Sistema"
    const actividadRecienteEl = document.getElementById('actividad-reciente');
    if (actividadRecienteEl) {
        const now = new Date();
        const titulo = isAuthorized
            ? `Acceso autorizado: ${plate}`
            : `Acceso no autorizado: ${plate}`;
        const descripcion = `Detección de ${label}`;
        const timeHuman = 'Hace 1 segundo';
        const color = isAuthorized ? 'green' : 'yellow';
        const icon = isAuthorized ? 'check' : 'exclamation-triangle';

        const newActivityHtml = `
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-700 opacity-0 transition-opacity duration-500">
                <div class="w-8 h-8 bg-${color}-400/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-${icon} text-${color}-400 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white text-sm">${titulo}</p>
                    <p class="text-gray-400 text-xs mt-1">${descripcion} · ${timeHuman}</p>
                </div>
            </div>
        `;

        // Remove empty state placeholder if present
        const emptyState = actividadRecienteEl.querySelector('[data-empty-state]');
        if (emptyState) emptyState.remove();

        actividadRecienteEl.insertAdjacentHTML('afterbegin', newActivityHtml);

        // Remove last item if more than 6
        const items = actividadRecienteEl.children;
        if (items.length > 6) {
            items[items.length - 1].remove();
        }

        // Fade in new item
        setTimeout(() => {
            actividadRecienteEl.firstElementChild.style.opacity = '1';
        }, 100);
    }

    // If not authorized, add an alert
    if (!isAuthorized) {
        dashboardState.alertasActivas++;
        const alertasActivasEl = document.getElementById('alertas-activas');
        if (alertasActivasEl) {
            alertasActivasEl.textContent = dashboardState.alertasActivas;
        }

        const ultimasAlertasEl = document.getElementById('ultimas-alertas');
        if (ultimasAlertasEl) {
            const newAlertHtml = `
                <div class="p-3 bg-red-400/5 border-l-4 border-red-500 rounded-r-lg opacity-0 transition-opacity duration-500">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-semibold text-white">Acceso no autorizado detectado</p>
                        <span class="text-[10px] text-gray-400">ahora</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">${label} con placa ${plate} intentó acceder</p>
                </div>
            `;
            
            ultimasAlertasEl.insertAdjacentHTML('afterbegin', newAlertHtml);
            
            // Remove last item if more than 3
            const items = ultimasAlertasEl.children;
            if (items.length > 3) {
                items[items.length - 1].remove();
            }
            
            // Fade in new item
            setTimeout(() => {
                ultimasAlertasEl.firstElementChild.style.opacity = '1';
            }, 100);
        }
    }
}

// Update detection counters
function updateDetectionCounters(detections) {
    let personCount = 0;
    let vehicleCount = 0;
    let otherCount = 0;
    
    detections.forEach(detection => {
        const label = (detection.etiqueta || detection.tipo || 'objeto').toLowerCase();
        
        if (label === 'person' || label === 'persona') {
            personCount++;
        } else if (['car', 'truck', 'bus', 'motorcycle', 'bicycle', 'vehicle', 'vehículo'].includes(label)) {
            vehicleCount++;
        } else {
            otherCount++;
        }
    });
    
    // Update counter elements
    const personCountEl = document.getElementById('person-count');
    const vehicleCountEl = document.getElementById('vehicle-count');
    const otherCountEl = document.getElementById('other-count');
    
    if (personCountEl) personCountEl.textContent = personCount;
    if (vehicleCountEl) vehicleCountEl.textContent = vehicleCount;
    if (otherCountEl) otherCountEl.textContent = otherCount;
}

// Function to send frame to API for YOLO detection
async function processFrame() {
    if (!video || !ctx || !detectionOverlay) return;
    if (!video.videoWidth || !video.videoHeight) return;
    if (isProcessing) return;
    if (!apiToken) return; // No token, can't call API
    
    isProcessing = true;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert canvas to base64
    const imageBase64 = canvas.toDataURL('image/jpeg', 0.6);
    
    // Clear previous detections
    detectionOverlay.innerHTML = '';
    
    try {
        // Call YOLO API
        const response = await fetch(`${API_URL}/ai/procesar-imagen`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiToken}`
            },
            body: JSON.stringify({
                imagen_base64: imageBase64
            })
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.exito && result.detecciones) {
            // Update detection counters
            updateDetectionCounters(result.detecciones);
            
            // Get video element dimensions to scale coordinates
            const videoRect = video.getBoundingClientRect();
            const scaleX = videoRect.width / canvas.width;
            const scaleY = videoRect.height / canvas.height;
            
            // Process each detection
            result.detecciones.forEach(detection => {
                const label = detection.etiqueta || detection.tipo || 'Objeto';
                const isVehicle = ['car', 'truck', 'bus', 'motorcycle', 'bicycle'].includes(label.toLowerCase());
                const isPerson = ['person', 'persona'].includes(label.toLowerCase());
                
                const div = document.createElement('div');
                const color = isPerson ? "cyan" : (isVehicle ? "green" : "yellow");
                
                // Scale coordinates to match display size
                const x1 = detection.coordenadas.x1 * scaleX;
                const y1 = detection.coordenadas.y1 * scaleY;
                const x2 = detection.coordenadas.x2 * scaleX;
                const y2 = detection.coordenadas.y2 * scaleY;
                
                div.style.position = 'absolute';
                div.style.left = `${x1}px`;
                div.style.top = `${y1}px`;
                div.style.width = `${x2 - x1}px`;
                div.style.height = `${y2 - y1}px`;
                div.style.border = `2px solid var(--${color}-400)`;
                div.style.borderRadius = '4px';
                div.style.boxShadow = `0 0 15px rgba(0, ${color === "cyan" ? "242" : color === "green" ? "255" : "251"}, ${color === "cyan" ? "255" : color === "green" ? "135" : "76"}, 0.4)`;
                
                const labelDiv = document.createElement('div');
                labelDiv.style.position = 'absolute';
                labelDiv.style.top = '-24px';
                labelDiv.style.left = '0';
                labelDiv.style.background = `var(--${color}-400)`;
                labelDiv.style.color = '#000';
                labelDiv.style.padding = '2px 8px';
                labelDiv.style.fontSize = '12px';
                labelDiv.style.fontWeight = 'bold';
                labelDiv.style.borderRadius = '4px';
                labelDiv.textContent = `${label} (${Math.round((detection.confianza || 0) * 100)}%)`;
                
                div.appendChild(labelDiv);
                detectionOverlay.appendChild(div);

                // If it's a vehicle, update the dashboard (simulate plate detection)
                if (isVehicle && detection.confianza > 0.7) { // Only process if confidence > 70%
                    const plate = generateRandomPlate();
                    if (!dashboardState.detectedPlates.has(plate)) {
                        dashboardState.detectedPlates.add(plate);
                        const isAuthorized = Math.random() > 0.3; // 70% chance of being authorized
                        updateDashboardWithAccess(plate, isAuthorized, label);
                    }
                }
            });
        }
        
    } catch (err) {
        console.error("Error processing frame:", err);
        // Optional: show error on screen
    } finally {
        isProcessing = false;
    }
}

// Initialize everything
document.addEventListener('DOMContentLoaded', () => {
    initWebcam();
    // Process frame every 1000ms (1 second) to not overload API
    setInterval(processFrame, 1000);
});
</script>
@endsection
