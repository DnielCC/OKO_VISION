@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<!-- Header Section -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Gestión de Usuarios y Visitantes</h2>
        <p class="text-gray-400 mt-1">Administra cuentas de acceso y registros temporales del sistema</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('users.create', ['role' => 3]) }}" class="btn-secondary flex items-center">
            <i class="fas fa-id-badge mr-2"></i>
            Añadir Visitante
        </a>
        <a href="{{ route('users.create') }}" class="btn-primary flex items-center">
            <i class="fas fa-user-plus mr-2"></i>
            Añadir Usuario
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            {{ session('success') }}
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-300">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-500/20 border border-red-500/50 text-red-400 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            {{ session('error') }}
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

<!-- Filtros de Búsqueda -->
<div class="card mb-6">
    <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-grow max-w-md">
            <label for="search" class="block text-sm font-medium text-gray-400 mb-1">Buscar por Nombre, Identificador o Correo</label>
            <div class="relative">
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="input-field w-full pl-10" placeholder="Ej. Juan, adm-01, correo@okovision.com">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        
        <div>
            <label for="role" class="block text-sm font-medium text-gray-400 mb-1">Filtrar Staff por Rol</label>
            <select name="role" id="role" class="input-field">
                <option value="">Todos los Roles</option>
                <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Administrador</option>
                <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Operador</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn-primary" style="height: 42px;">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
            @if(request()->has('search') || request()->has('role'))
                <a href="{{ route('users.index') }}" class="btn-secondary ml-2" style="height: 42px; display: inline-flex; align-items: center;">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- SECCIÓN: STAFF DEL SISTEMA                 -->
<!-- ========================================== -->
<h3 class="text-xl font-semibold text-white mb-4 flex items-center">
    <i class="fas fa-user-shield text-cyan-400 mr-2"></i> Staff del Sistema
</h3>

<div class="card overflow-hidden mb-10">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm w-16">ID</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Usuario</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Matrícula</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Rol</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Contacto</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">#{{ $user->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($user->persona && $user->persona->foto)
                                <img src="{{ $user->persona->foto }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover mr-3 border border-cyan-400/30 shadow-[0_0_10px_rgba(0,242,255,0.2)]">
                            @else
                                <div class="w-8 h-8 bg-cyan-400/10 rounded-full flex items-center justify-center mr-3 text-cyan-400 font-bold uppercase">
                                    {{ substr($user->nombre, 0, 1) }}{{ substr($user->apellidos, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <span class="text-white font-medium block">{{ $user->nombre }} {{ $user->apellidos }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-cyan-400 font-mono text-sm bg-cyan-400/10 px-2 py-1 rounded">
                            <i class="fas fa-fingerprint mr-1 text-xs text-gray-500"></i>{{ $user->identificador }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->isAdmin())
                            <span class="bg-red-400/10 text-red-400 text-[10px] px-2 py-1 rounded-full border border-red-400/20 uppercase font-bold text-center inline-block w-24">Admin</span>
                        @else
                            <span class="bg-cyan-400/10 text-cyan-400 text-[10px] px-2 py-1 rounded-full border border-cyan-400/20 uppercase font-bold text-center inline-block w-24">Operador</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-300 text-sm"><i class="fas fa-envelope mr-1 text-gray-500 text-xs"></i> {{ $user->email }}</p>
                        @if($user->telefono)
                            <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone mr-1 mt-1"></i> {{ $user->telefono }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('users.show', $user) }}" class="text-cyan-400 hover:text-cyan-300 transition-colors p-2 bg-cyan-400/10 rounded" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $user) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors p-2 bg-yellow-400/10 rounded" title="Editar Credenciales">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este usuario de forma permanente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors p-2 bg-red-400/10 rounded" title="Dar de baja">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users-slash text-4xl mb-3 opacity-50 block"></i>
                        <p>No se encontraron datos para el Staff con los filtros actuales</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-6 pb-4">
        {{ $users->links('pagination::tailwind') }}
    </div>
</div>

<!-- ========================================== -->
<!-- SECCIÓN: VISITANTES REGISTRADOS            -->
<!-- ========================================== -->
<h3 class="text-xl font-semibold text-white mb-4 mt-8 flex items-center">
    <i class="fas fa-id-badge text-purple-400 mr-2"></i> Visitantes Registrados
</h3>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm w-16">ID</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Visitante</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Pase Temporal</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Contacto</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($visitors as $visitor)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">#{{ $visitor->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($visitor->persona && $visitor->persona->foto)
                                <img src="{{ $visitor->persona->foto }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover mr-3 border border-purple-400/30 shadow-[0_0_10px_rgba(168,85,247,0.2)]">
                            @else
                                <div class="w-8 h-8 bg-purple-400/10 rounded-full flex items-center justify-center mr-3 text-purple-400 font-bold uppercase">
                                    {{ substr($visitor->nombre, 0, 1) }}{{ substr($visitor->apellidos, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <span class="text-white font-medium block">{{ $visitor->nombre }} {{ $visitor->apellidos }}</span>
                                <span class="bg-purple-400/10 text-purple-400 text-[10px] px-2 py-0.5 rounded-full border border-purple-400/20 uppercase font-bold text-center inline-block mt-1">Visitante</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-purple-400 font-mono text-sm bg-purple-400/10 px-2 py-1 rounded">
                            <i class="fas fa-ticket-alt mr-1 text-xs text-gray-500"></i>{{ $visitor->identificador }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-300 text-sm"><i class="fas fa-envelope mr-1 text-gray-500 text-xs"></i> {{ $visitor->email ?: 'Sin correo' }}</p>
                        @if($visitor->telefono)
                            <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone mr-1 mt-1"></i> {{ $visitor->telefono }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('users.show', $visitor) }}" class="text-cyan-400 hover:text-cyan-300 transition-colors p-2 bg-cyan-400/10 rounded" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $visitor) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors p-2 bg-yellow-400/10 rounded" title="Editar Credenciales">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('users.destroy', $visitor) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de revocar y eliminar este pase de visitante?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors p-2 bg-red-400/10 rounded" title="Dar de baja">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-id-card-alt text-4xl mb-3 opacity-50 block"></i>
                        <p>No hay visitantes registrados con los parámetros actuales</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-6 pb-4">
        {{ $visitors->links('pagination::tailwind') }}
    </div>
</div>
@endsection
