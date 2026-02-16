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
                <p class="text-2xl font-bold text-white">1,247</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>8% este mes
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
                <p class="text-gray-400 text-sm">Activos</p>
                <p class="text-2xl font-bold text-green-400">1,189</p>
                <p class="text-gray-400 text-xs mt-1">95.3% del total</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-check text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Vehículos</p>
                <p class="text-2xl font-bold text-white">2,156</p>
                <p class="text-gray-400 text-xs mt-1">1.73 por usuario</p>
            </div>
            <div class="w-12 h-12 bg-blue-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-car text-blue-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm"> Nuevos este mes</p>
                <p class="text-2xl font-bold text-yellow-400">89</p>
                <p class="text-gray-400 text-xs mt-1">En revisión</p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-clock text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">Lista de Usuarios</h3>
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
        <table class="w-full table-custom">
            <thead>
                <tr>
                    <th class="text-left py-3 px-4">Usuario</th>
                    <th class="text-left py-3 px-4">Contacto</th>
                    <th class="text-left py-3 px-4">Rol</th>
                    <th class="text-left py-3 px-4">Vehículos</th>
                    <th class="text-left py-3 px-4">Estado</th>
                    <th class="text-left py-3 px-4">Registrado</th>
                    <th class="text-left py-3 px-4">Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <!-- User 1 -->
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-3">
                            <img src="https://via.placeholder.com/40x40/0D1B35/E0E6ED?text=JD" 
                                 alt="User" 
                                 class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-white font-medium">Juan Díaz Martínez</p>
                                <p class="text-gray-400 text-sm">ID: USR-001</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">juan.diaz@email.com</p>
                            <p class="text-gray-400 text-sm">+52 55 1234 5678</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-purple-400/20 text-purple-400 px-2 py-1 rounded-full text-xs font-semibold">
                            Administrador
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">ABC-123</span>
                                <span class="badge-success">Activo</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">XYZ-789</span>
                                <span class="badge-success">Activo</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Activo</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">15/01/2026</p>
                            <p class="text-gray-400 text-xs">Hace 30 días</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="editUser('USR-001')" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUser('USR-001')" class="text-blue-400 hover:text-blue-300 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="suspendUser('USR-001')" class="text-yellow-400 hover:text-yellow-300 transition-colors">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button onclick="deleteUser('USR-001')" class="text-red-400 hover:text-red-300 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- User 2 -->
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-3">
                            <img src="https://via.placeholder.com/40x40/0D1B35/E0E6ED?text=MG" 
                                 alt="User" 
                                 class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-white font-medium">María García López</p>
                                <p class="text-gray-400 text-sm">ID: USR-002</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">maria.garcia@email.com</p>
                            <p class="text-gray-400 text-sm">+52 55 2345 6789</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-blue-400/20 text-blue-400 px-2 py-1 rounded-full text-xs font-semibold">
                            Operador
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">DEF-456</span>
                                <span class="badge-success">Activo</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Activo</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">20/01/2026</p>
                            <p class="text-gray-400 text-xs">Hace 25 días</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="editUser('USR-002')" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUser('USR-002')" class="text-blue-400 hover:text-blue-300 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="suspendUser('USR-002')" class="text-yellow-400 hover:text-yellow-300 transition-colors">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button onclick="deleteUser('USR-002')" class="text-red-400 hover:text-red-300 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- User 3 -->
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-3">
                            <img src="https://via.placeholder.com/40x40/0D1B35/E0E6ED?text=CR" 
                                 alt="User" 
                                 class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-white font-medium">Carlos Rodríguez Silva</p>
                                <p class="text-gray-400 text-sm">ID: USR-003</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">carlos.rodriguez@email.com</p>
                            <p class="text-gray-400 text-sm">+52 55 3456 7890</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-gray-400/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold">
                            Usuario
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">GHI-789</span>
                                <span class="badge-success">Activo</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">JKL-012</span>
                                <span class="badge-warning">Pendiente</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-success">Activo</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">10/02/2026</p>
                            <p class="text-gray-400 text-xs">Hace 5 días</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="editUser('USR-003')" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUser('USR-003')" class="text-blue-400 hover:text-blue-300 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="suspendUser('USR-003')" class="text-yellow-400 hover:text-yellow-300 transition-colors">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button onclick="deleteUser('USR-003')" class="text-red-400 hover:text-red-300 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- User 4 - Suspended -->
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-3">
                            <img src="https://via.placeholder.com/40x40/0D1B35/E0E6ED?text=AL" 
                                 alt="User" 
                                 class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-white font-medium">Ana López Torres</p>
                                <p class="text-gray-400 text-sm">ID: USR-004</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">ana.lopez@email.com</p>
                            <p class="text-gray-400 text-sm">+52 55 4567 8901</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-gray-400/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold">
                            Usuario
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-sm">MNO-345</span>
                                <span class="badge-danger">Suspendido</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge-warning">Suspendido</span>
                    </td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-white text-sm">05/01/2026</p>
                            <p class="text-gray-400 text-xs">Hace 40 días</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="editUser('USR-004')" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUser('USR-004')" class="text-blue-400 hover:text-blue-300 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="activateUser('USR-004')" class="text-green-400 hover:text-green-300 transition-colors">
                                <i class="fas fa-play"></i>
                            </button>
                            <button onclick="deleteUser('USR-004')" class="text-red-400 hover:text-red-300 transition-colors">
                                <i class="fas fa-trash"></i>
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
            Mostrando <span class="text-white">1-4</span> de <span class="text-white">1,247</span> usuarios
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1 bg-cyan-400 text-black rounded font-medium">1</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">2</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">3</button>
            <span class="px-2 text-gray-600">...</span>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">312</button>
            <button class="px-3 py-1 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
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
