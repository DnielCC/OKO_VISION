@extends('layouts.app')

@section('content')
<!-- Filters Section -->
<div class="card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <select class="input-field text-sm" style="width: auto;">
                <option>Todas las Alertas</option>
                <option>Críticas</option>
                <option>Altas</option>
                <option>Medias</option>
                <option>Bajas</option>
            </select>
            
            <select class="input-field text-sm" style="width: auto;">
                <option>Últimas 24 horas</option>
                <option>Últimos 7 días</option>
                <option>Últimos 30 días</option>
                <option>Todas</option>
            </select>
            
            <select class="input-field text-sm" style="width: auto;">
                <option>Todos los estados</option>
                <option>Pendientes</option>
                <option>Validadas</option>
                <option>Reportadas</option>
                <option>Resueltas</option>
            </select>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" placeholder="Buscar alertas..." class="input-field text-sm pl-10" style="width: 250px;">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            
            <button class="btn-secondary text-sm">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Alertas</p>
                <p class="text-2xl font-bold text-white">156</p>
                <p class="text-gray-400 text-xs mt-1">Últimas 24h</p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Críticas</p>
                <p class="text-2xl font-bold text-red-400">8</p>
                <p class="text-red-400 text-xs mt-1">Requieren acción</p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-fire text-red-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-400">23</p>
                <p class="text-yellow-400 text-xs mt-1">Por validar</p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Resueltas</p>
                <p class="text-2xl font-bold text-green-400">125</p>
                <p class="text-green-400 text-xs mt-1">80.1% del total</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Table -->
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">Registro de Incidencias</h3>
        <div class="flex items-center gap-2">
            <button class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-th-large"></i>
            </button>
            <button class="text-cyan-400">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">ID</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Fecha/Hora</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Tipo</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Descripción</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Vehículo</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Ubicación</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Estado</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Alert 1 - Critical -->
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="text-gray-400 text-sm">#ALT-001</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/02/2026</p>
                            <p class="text-gray-400 text-xs">14:32:15</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-danger">Crítico</span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Acceso no autorizado detectado</p>
                        <p class="text-gray-400 text-xs">Vehículo intentó ingresar sin permiso</p>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <img src="https://via.placeholder.com/32x32/0D1B35/E0E6ED?text=XYZ" 
                                 alt="Vehicle" 
                                 class="w-8 h-8 rounded">
                            <div>
                                <p class="text-white text-sm font-medium">XYZ-999</p>
                                <p class="text-gray-400 text-xs">No registrado</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Entrada Principal</p>
                        <p class="text-gray-400 text-xs">Cámara 1</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-danger">Pendiente</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="btn-primary text-xs px-2 py-1" onclick="validateAlert('ALT-001')">
                                <i class="fas fa-check mr-1"></i>Validar
                            </button>
                            <button class="btn-secondary text-xs px-2 py-1" onclick="reportAlert('ALT-001')">
                                <i class="fas fa-flag mr-1"></i>Reportar
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Alert 2 - High -->
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="text-gray-400 text-sm">#ALT-002</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/02/2026</p>
                            <p class="text-gray-400 text-xs">13:45:22</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-warning">Alta</span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Detección de placa falsificada</p>
                        <p class="text-gray-400 text-xs">IA detectó inconsistencias en la placa</p>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <img src="https://via.placeholder.com/32x32/0D1B35/E0E6ED?text=ABC" 
                                 alt="Vehicle" 
                                 class="w-8 h-8 rounded">
                            <div>
                                <p class="text-white text-sm font-medium">ABC-123</p>
                                <p class="text-gray-400 text-xs">Juan Pérez</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Salida Secundaria</p>
                        <p class="text-gray-400 text-xs">Cámara 3</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-warning">En revisión</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="btn-primary text-xs px-2 py-1" onclick="validateAlert('ALT-002')">
                                <i class="fas fa-check mr-1"></i>Validar
                            </button>
                            <button class="btn-secondary text-xs px-2 py-1" onclick="reportAlert('ALT-002')">
                                <i class="fas fa-flag mr-1"></i>Reportar
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Alert 3 - Medium -->
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="text-gray-400 text-sm">#ALT-003</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/02/2026</p>
                            <p class="text-gray-400 text-xs">12:15:30</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold">Media</span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Vehículo estacionado en zona prohibida</p>
                        <p class="text-gray-400 text-xs">Superó el tiempo permitido</p>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <img src="https://via.placeholder.com/32x32/0D1B35/E0E6ED?text=DEF" 
                                 alt="Vehicle" 
                                 class="w-8 h-8 rounded">
                            <div>
                                <p class="text-white text-sm font-medium">DEF-456</p>
                                <p class="text-gray-400 text-xs">María López</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Zona de Carga</p>
                        <p class="text-gray-400 text-xs">Cámara 2</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Resuelta</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-check mr-1"></i>Validado
                            </button>
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-eye mr-1"></i>Ver
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Alert 4 - Low -->
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="text-gray-400 text-sm">#ALT-004</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/02/2026</p>
                            <p class="text-gray-400 text-xs">11:30:45</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold">Baja</span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Detección de animal en vía</p>
                        <p class="text-gray-400 text-xs">Gato detectado cerca de entrada</p>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center">
                                <i class="fas fa-cat text-gray-400 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium">N/A</p>
                                <p class="text-gray-400 text-xs">Animal</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Jardines</p>
                        <p class="text-gray-400 text-xs">Cámara 4</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Resuelta</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-check mr-1"></i>Validado
                            </button>
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-eye mr-1"></i>Ver
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Alert 5 - Critical -->
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="text-gray-400 text-sm">#ALT-005</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/02/2026</p>
                            <p class="text-gray-400 text-xs">10:15:20</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-danger">Crítico</span>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Intento de acceso forzado</p>
                        <p class="text-gray-400 text-xs">Barrera física dañada</p>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <img src="https://via.placeholder.com/32x32/0D1B35/E0E6ED?text=GHI" 
                                 alt="Vehicle" 
                                 class="w-8 h-8 rounded">
                            <div>
                                <p class="text-white text-sm font-medium">GHI-789</p>
                                <p class="text-gray-400 text-xs">Desconocido</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <p class="text-white text-sm">Entrada Principal</p>
                        <p class="text-gray-400 text-xs">Cámara 1</p>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Resuelta</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-check mr-1"></i>Validado
                            </button>
                            <button class="text-gray-400 text-xs px-2 py-1" disabled>
                                <i class="fas fa-eye mr-1"></i>Ver
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="flex items-center justify-between mt-6">
        <div class="text-sm text-gray-400">
            Mostrando <span class="text-white">1-5</span> de <span class="text-white">156</span> alertas
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1 bg-cyan-400 text-black rounded font-medium">1</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">2</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">3</button>
            <span class="px-2 text-gray-600">...</span>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">32</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Alert Detail Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">Detalles de Alerta</h3>
                <button onclick="closeAlertModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modalContent">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
function validateAlert(alertId) {
    if (confirm(`¿Validar la alerta ${alertId}? Esta acción marcará la alerta como revisada.`)) {
        // Simulate validation
        showNotification(`Alerta ${alertId} validada exitosamente`, 'success');
        
        // Update UI (in real app, this would be an API call)
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
}

function reportAlert(alertId) {
    const reason = prompt(`¿Por qué razón desea reportar la alerta ${alertId}?`);
    if (reason) {
        // Simulate reporting
        showNotification(`Alerta ${alertId} reportada: ${reason}`, 'warning');
        
        // Update UI (in real app, this would be an API call)
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-red-500'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function closeAlertModal() {
    document.getElementById('alertModal').classList.add('hidden');
}

// Auto-refresh alerts every 30 seconds
setInterval(() => {
    // In a real app, this would fetch new alerts from the server
    console.log('Checking for new alerts...');
}, 30000);

// Search functionality
document.querySelector('input[placeholder="Buscar alertas..."]').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endsection
