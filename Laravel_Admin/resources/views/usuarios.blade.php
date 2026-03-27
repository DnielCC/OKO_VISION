@extends('layouts.app')

@section('content')
<!-- Header Section -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Gestión de Usuarios</h2>
        <p class="text-gray-400 mt-1">Administra usuarios y sus vehículos autorizados</p>
    </div>
    <button onclick="openUserModal()" class="btn-primary">
        <i class="fas fa-user-plus mr-2"></i>
        Añadir Usuario
    </button>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[300px]">
            <div class="relative">
                <input type="text" 
                       id="searchInput"
                       placeholder="Buscar por nombre, email o placa..." 
                       class="input-field w-full pl-10">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        
        <select class="input-field" style="width: auto;">
            <option>Todos los roles</option>
            <option>Administrador</option>
            <option>Operador</option>
            <option>Usuario</option>
            <option>Visitante</option>
        </select>
        
        <select class="input-field" style="width: auto;">
            <option>Todos los estados</option>
            <option>Activos</option>
            <option>Inactivos</option>
            <option>Suspendidos</option>
        </select>
        
        <button class="btn-secondary">
            <i class="fas fa-filter mr-2"></i>
            Más Filtros
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Usuarios</p>
                <p class="text-2xl font-bold text-white">{{ count($usuarios) }}</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-check mr-1"></i>En tiempo real
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-cyan-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Administradores</p>
                <p class="text-2xl font-bold text-green-400">{{ $usuarios->where('is_admin', true)->count() }}</p>
                <p class="text-gray-400 text-xs mt-1">Acceso total</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-shield text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">ID</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Usuario</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Email</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Rol</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Fecha Registro</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($usuarios as $usuario)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-300 font-mono text-sm">{{ $usuario->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-cyan-400/10 rounded-full flex items-center justify-center mr-3 text-cyan-400 font-bold">
                                {{ substr($usuario->username, 0, 1) }}
                            </div>
                            <span class="text-white font-medium">{{ $usuario->username }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">{{ $usuario->email }}</td>
                    <td class="px-6 py-4">
                        @if($usuario->is_admin)
                            <span class="bg-cyan-400/10 text-cyan-400 text-[10px] px-2 py-1 rounded-full border border-cyan-400/20 uppercase font-bold">Admin</span>
                        @else
                            <span class="bg-gray-700 text-gray-400 text-[10px] px-2 py-1 rounded-full border border-gray-600 uppercase font-bold">Usuario</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-gray-400 hover:text-cyan-400 transition-colors mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-gray-400 hover:text-red-400 transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- User Form Modal -->
<div id="userModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">
                    <i class="fas fa-user-plus mr-2 text-cyan-400"></i>
                    Nuevo Usuario
                </h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="userForm" class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h4 class="text-lg font-medium text-white mb-4">Información Personal</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nombre Completo *</label>
                            <input type="text" name="name" required class="input-field w-full" placeholder="Juan Pérez">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email *</label>
                            <input type="email" name="email" required class="input-field w-full" placeholder="juan@ejemplo.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Teléfono</label>
                            <input type="tel" name="phone" class="input-field w-full" placeholder="+52 55 1234 5678">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Rol *</label>
                            <select name="role" required class="input-field w-full">
                                <option value="">Seleccionar rol</option>
                                <option value="admin">Administrador</option>
                                <option value="operator">Operador</option>
                                <option value="user">Usuario</option>
                                <option value="visitor">Visitante</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Vehicle Information -->
                <div>
                    <h4 class="text-lg font-medium text-white mb-4">Información de Vehículos</h4>
                    <div id="vehiclesContainer" class="space-y-4">
                        <div class="vehicle-entry bg-gray-800/50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Placa *</label>
                                    <input type="text" name="plates[]" required class="input-field w-full" placeholder="ABC-123">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Marca/Modelo</label>
                                    <input type="text" name="models[]" class="input-field w-full" placeholder="Toyota Corolla">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Color</label>
                                    <input type="text" name="colors[]" class="input-field w-full" placeholder="Rojo">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Foto de Placa</label>
                                <div class="flex items-center space-x-4">
                                    <label class="btn-secondary cursor-pointer">
                                        <i class="fas fa-camera mr-2"></i>
                                        Subir Foto
                                        <input type="file" name="plate_images[]" accept="image/*" class="hidden">
                                    </label>
                                    <span class="text-gray-400 text-sm">Formatos: JPG, PNG (Máx. 5MB)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" onclick="addVehicleField()" class="mt-4 btn-secondary text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Añadir Otro Vehículo
                    </button>
                </div>
                
                <!-- Access Permissions -->
                <div>
                    <h4 class="text-lg font-medium text-white mb-4">Permisos de Acceso</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Días de Acceso</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="monday" class="mr-2" checked>
                                    <span class="text-gray-300">Lunes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="tuesday" class="mr-2" checked>
                                    <span class="text-gray-300">Martes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="wednesday" class="mr-2" checked>
                                    <span class="text-gray-300">Miércoles</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="thursday" class="mr-2" checked>
                                    <span class="text-gray-300">Jueves</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="friday" class="mr-2" checked>
                                    <span class="text-gray-300">Viernes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="saturday" class="mr-2">
                                    <span class="text-gray-300">Sábado</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="access_days[]" value="sunday" class="mr-2">
                                    <span class="text-gray-300">Domingo</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Horario de Acceso</label>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hora de Entrada</label>
                                    <input type="time" name="access_start" value="08:00" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Hora de Salida</label>
                                    <input type="time" name="access_end" value="18:00" class="input-field w-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                    <button type="button" onclick="closeUserModal()" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let vehicleCount = 1;

function openUserModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('userForm').reset();
}

function addVehicleField() {
    vehicleCount++;
    const container = document.getElementById('vehiclesContainer');
    const newVehicle = document.createElement('div');
    newVehicle.className = 'vehicle-entry bg-gray-800/50 p-4 rounded-lg';
    newVehicle.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <h5 class="text-white font-medium">Vehículo ${vehicleCount}</h5>
            <button type="button" onclick="removeVehicleField(this)" class="text-red-400 hover:text-red-300">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Placa *</label>
                <input type="text" name="plates[]" required class="input-field w-full" placeholder="ABC-123">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Marca/Modelo</label>
                <input type="text" name="models[]" class="input-field w-full" placeholder="Toyota Corolla">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Color</label>
                <input type="text" name="colors[]" class="input-field w-full" placeholder="Rojo">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Foto de Placa</label>
            <div class="flex items-center space-x-4">
                <label class="btn-secondary cursor-pointer">
                    <i class="fas fa-camera mr-2"></i>
                    Subir Foto
                    <input type="file" name="plate_images[]" accept="image/*" class="hidden">
                </label>
                <span class="text-gray-400 text-sm">Formatos: JPG, PNG (Máx. 5MB)</span>
            </div>
        </div>
    `;
    container.appendChild(newVehicle);
}

function removeVehicleField(button) {
    button.closest('.vehicle-entry').remove();
}

function editUser(userId) {
    // In a real app, this would fetch user data and populate the form
    openUserModal();
    document.querySelector('#userModal h3').innerHTML = '<i class="fas fa-edit mr-2 text-cyan-400"></i>Editar Usuario';
}

function viewUser(userId) {
    // In a real app, this would show user details
    showNotification(`Ver detalles del usuario ${userId}`, 'info');
}

function suspendUser(userId) {
    if (confirm(`¿Suspender al usuario ${userId}?`)) {
        showNotification(`Usuario ${userId} suspendido`, 'warning');
        setTimeout(() => location.reload(), 1500);
    }
}

function activateUser(userId) {
    if (confirm(`¿Activar al usuario ${userId}?`)) {
        showNotification(`Usuario ${userId} activado`, 'success');
        setTimeout(() => location.reload(), 1500);
    }
}

function deleteUser(userId) {
    if (confirm(`¿Eliminar al usuario ${userId}? Esta acción no se puede deshacer.`)) {
        showNotification(`Usuario ${userId} eliminado`, 'danger');
        setTimeout(() => location.reload(), 1500);
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'warning' ? 'bg-yellow-500' : 
        type === 'danger' ? 'bg-red-500' : 'bg-blue-500'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${
                type === 'success' ? 'check-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 
                type === 'danger' ? 'times-circle' : 'info-circle'
            }"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Form submission
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showNotification('Usuario guardado exitosamente', 'success');
    setTimeout(() => {
        closeUserModal();
        location.reload();
    }, 1500);
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserModal();
    }
});
</script>
@endsection
