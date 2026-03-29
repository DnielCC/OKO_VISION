@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<!-- Header Section -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Gestión de Usuarios</h2>
        <p class="text-gray-400 mt-1">Administra cuentas de acceso al sistema</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn-primary flex items-center">
        <i class="fas fa-user-plus mr-2"></i>
        Añadir Usuario
    </a>
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

<!-- Users Table -->
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm w-16">ID</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Usuario</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Identificador</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Rol</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Email / Tel</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">#{{ $user->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-cyan-400/10 rounded-full flex items-center justify-center mr-3 text-cyan-400 font-bold uppercase">
                                {{ substr($user->nombre, 0, 1) }}{{ substr($user->apellidos, 0, 1) }}
                            </div>
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
                        @elseif($user->isUsuario())
                            <span class="bg-cyan-400/10 text-cyan-400 text-[10px] px-2 py-1 rounded-full border border-cyan-400/20 uppercase font-bold text-center inline-block w-24">Operador</span>
                        @else
                            <span class="bg-gray-700 text-gray-400 text-[10px] px-2 py-1 rounded-full border border-gray-600 uppercase font-bold text-center inline-block w-24">Visitante</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-300 text-sm"><i class="fas fa-envelope mr-1 text-gray-500 text-xs"></i> {{ $user->email }}</p>
                        @if($user->telefono)
                            <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone mr-1"></i> {{ $user->telefono }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('users.edit', $user) }}" class="text-yellow-400 hover:text-yellow-300 transition-colors p-2 bg-yellow-400/10 rounded" title="Editar Credenciales y Accesos">
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
                        <p>No hay usuarios registrados en el sistema</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Paginación Laravel integrando estilos Tailwind -->
    <div class="mt-4 px-6 pb-4">
        {{ $users->links('pagination::tailwind') }}
    </div>
</div>
@endsection
