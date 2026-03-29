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
            
            <!-- Video Feed Placeholder -->
            <div class="relative bg-gray-900 rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
                <img src="https://via.placeholder.com/800x450/0D1B35/E0E6ED?text=Cámara+Principal" 
                     alt="Video Feed" 
                     class="w-full h-full object-cover">
                
                <!-- AI Detection Overlay -->
                <div class="absolute inset-0 pointer-events-none">
                    <!-- Detection Box 1 -->
                    <div class="absolute top-20 left-32 w-48 h-32 border-2 border-cyan-400 rounded-lg" 
                         style="box-shadow: 0 0 20px rgba(0, 242, 255, 0.6);">
                        <div class="absolute -top-6 left-0 bg-cyan-400 text-black text-xs px-2 py-1 rounded font-semibold">
                            Vehículo Detectado
                        </div>
                        <div class="absolute bottom-0 left-0 bg-cyan-400/80 text-black text-xs px-2 py-1 rounded-tr">
                            ABC-123
                        </div>
                    </div>
                    
                    <!-- Detection Box 2 -->
                    <div class="absolute top-40 right-24 w-36 h-24 border-2 border-green-400 rounded-lg" 
                         style="box-shadow: 0 0 20px rgba(0, 255, 135, 0.6);">
                        <div class="absolute -top-6 left-0 bg-green-400 text-black text-xs px-2 py-1 rounded font-semibold">
                            Acceso Autorizado
                        </div>
                        <div class="absolute bottom-0 left-0 bg-green-400/80 text-black text-xs px-2 py-1 rounded-tr">
                            XYZ-789
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
                        <p class="text-2xl font-bold text-white">{{ $accesos_hoy }}</p>
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
                        <p class="text-2xl font-bold text-white">{{ $alertas_activas }}</p>
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
            <div class="space-y-4">
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
            <div class="space-y-4">
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
        <div class="space-y-4">
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-700">
                <div class="w-8 h-8 bg-cyan-400/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-brain text-cyan-400 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white text-sm">Modelo de IA actualizado exitosamente</p>
                    <p class="text-gray-400 text-xs mt-1">Hace 10 minutos</p>
                </div>
            </div>
            
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-700">
                <div class="w-8 h-8 bg-green-400/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-green-400 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white text-sm">5 usuarios autorizados en las últimas 2 horas</p>
                    <p class="text-gray-400 text-xs mt-1">Hace 25 minutos</p>
                </div>
            </div>
            
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-700">
                <div class="w-8 h-8 bg-yellow-400/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-white text-sm">Detección de vehículo no registrado: PQR-999</p>
                    <p class="text-gray-400 text-xs mt-1">Hace 1 hora</p>
                </div>
            </div>
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
    document.getElementById('timestamp').textContent = timestamp;
}

updateTimestamp();
setInterval(updateTimestamp, 1000);

// Simulate real-time updates
function addNewAccess() {
    const accessData = [
        { name: 'Pedro Martín', plate: 'STU-567', status: 'success', time: 'ahora' },
        { name: 'Laura Sánchez', plate: 'VWX-890', status: 'warning', time: 'ahora' },
        { name: 'Roberto Díaz', plate: 'YZA-123', status: 'success', time: 'ahora' }
    ];
    
    const randomAccess = accessData[Math.floor(Math.random() * accessData.length)];
    const recentAccess = document.getElementById('recentAccess');
    
    const newAccessHtml = `
        <div class="bg-gray-800/50 rounded-lg p-3 border-l-4 border-${randomAccess.status === 'success' ? 'green' : randomAccess.status === 'warning' ? 'yellow' : 'red'}-400 opacity-0 transition-opacity duration-500">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        <img src="https://via.placeholder.com/32x32/0D1B35/E0E6ED?text=${randomAccess.name.split(' ').map(n => n[0]).join('')}" 
                             alt="User" 
                             class="w-8 h-8 rounded-full">
                        <div>
                            <p class="text-white font-medium text-sm">${randomAccess.name}</p>
                            <p class="text-gray-400 text-xs">${randomAccess.plate}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-400">${randomAccess.time}</span>
                        <span class="badge-${randomAccess.status === 'success' ? 'success' : randomAccess.status === 'warning' ? 'warning' : 'danger'}">${randomAccess.status === 'success' ? 'Autorizado' : randomAccess.status === 'warning' ? 'Pendiente' : 'Denegado'}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    recentAccess.insertAdjacentHTML('afterbegin', newAccessHtml);
    
    // Remove last item if more than 5
    const items = recentAccess.children;
    if (items.length > 5) {
        items[items.length - 1].remove();
    }
    
    // Fade in new item
    setTimeout(() => {
        recentAccess.firstElementChild.style.opacity = '1';
    }, 100);
}

// Simulate new access every 30 seconds
setInterval(addNewAccess, 30000);

// Refresh button functionality
document.querySelector('.fa-sync-alt').parentElement.addEventListener('click', function() {
    this.querySelector('i').classList.add('fa-spin');
    setTimeout(() => {
        this.querySelector('i').classList.remove('fa-spin');
        addNewAccess();
    }, 1000);
});
</script>
@endsection
