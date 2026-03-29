@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Detalles del Sistema</h2>
            <p class="text-gray-400 mt-1">Inspección de credenciales y auditoría de la cuenta</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('users.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al panel
            </a>
            <a href="{{ route('users.edit', $user) }}" class="btn-primary">
                <i class="fas fa-edit mr-2"></i>
                Editar Perfil
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- Tarjeta Principal de Identidad -->
    <div class="card md:col-span-1 border border-gray-700/50 bg-gray-900/40 relative overflow-hidden flex flex-col items-center pt-8">
        <!-- Decoration element -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl -ml-16 -mb-16"></div>
        
        @if($user->persona && $user->persona->foto)
            <img src="{{ $user->persona->foto }}" alt="Perfil" class="w-32 h-32 rounded-full object-cover mb-6 z-10 shadow-lg shadow-black/50 border-2 
                {{ $user->isAdmin() ? 'border-red-500/30' : ($user->isVisitante() ? 'border-purple-500/30' : 'border-cyan-500/30') }}">
        @else
            <div class="w-32 h-32 rounded-full flex items-center justify-center 
                 {{ $user->isAdmin() ? 'bg-red-500/20 text-red-400 border-2 border-red-500/30' : 
                    ($user->isVisitante() ? 'bg-purple-500/20 text-purple-400 border-2 border-purple-500/30' : 
                    'bg-cyan-500/20 text-cyan-400 border-2 border-cyan-500/30') }} text-4xl font-bold mb-6 z-10 shadow-lg shadow-black/50">
                {{ strtoupper(substr($user->nombre, 0, 1)) }}{{ strtoupper(substr($user->apellidos, 0, 1)) }}
            </div>
        @endif
        
        <h3 class="text-2xl font-bold text-white text-center z-10">{{ $user->nombre }} {{ $user->apellidos }}</h3>
        <span class="mt-2 text-xs font-mono px-3 py-1 rounded-full uppercase tracking-wider
              {{ $user->isAdmin() ? 'bg-red-500/10 text-red-500 border border-red-500/30' : 
                 ($user->isVisitante() ? 'bg-purple-500/10 text-purple-400 border border-purple-500/30' : 
                 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30') }}">
            {{ $user->getRoleLabel() }}
        </span>
        
        <div class="w-full mt-8 border-t border-gray-800 pt-6 px-6 z-10">
            <h4 class="text-gray-500 uppercase text-xs tracking-widest font-semibold mb-3 border-b border-gray-800 pb-2">Status del Sistema</h4>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-400 text-sm">Estado de Cuenta</span>
                <span class="text-green-400 text-sm flex items-center font-mono">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2 shadow-[0_0_5px_#22c55e]"></span>
                    Activa
                </span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-400 text-sm">Registrado Desde</span>
                <span class="text-white text-sm font-mono">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-400 text-sm">ID Interna</span>
                <span class="text-gray-500 text-sm font-mono">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    <!-- Panel de Detalles Técnicos -->
    <div class="md:col-span-2 space-y-6">
        
        <!-- Bloque de Contacto -->
        <div class="card border border-gray-700/50">
            <h4 class="text-lg font-medium text-white mb-4 flex items-center border-b border-gray-800 pb-2">
                <i class="fas fa-address-book text-cyan-400 mr-3"></i> 
                Información de Contacto
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mt-4">
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Correo Electrónico</span>
                    <a href="mailto:{{ $user->email }}" class="text-white font-medium hover:text-cyan-400 transition-colors">
                        <i class="fas fa-envelope text-gray-600 mr-2"></i>{{ $user->email ?: 'No proporcionado' }}
                    </a>
                </div>
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Teléfono Móvil</span>
                    <span class="text-white font-medium">
                        <i class="fas fa-phone-alt text-gray-600 mr-2"></i>{{ $user->telefono ?: 'No asociado' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bloque de Identidad Física -->
        <div class="card border border-gray-700/50">
            <h4 class="text-lg font-medium text-white mb-4 flex items-center border-b border-gray-800 pb-2">
                <i class="fas fa-fingerprint text-purple-400 mr-3"></i> 
                Identidad Física
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mt-4">
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Sexo</span>
                    <span class="text-white font-medium">
                        @if($user->persona && $user->persona->sexo == 'H')
                            <i class="fas fa-mars text-blue-400 mr-2"></i>Hombre
                        @elseif($user->persona && $user->persona->sexo == 'M')
                            <i class="fas fa-venus text-pink-400 mr-2"></i>Mujer
                        @else
                            <i class="fas fa-genderless text-gray-500 mr-2"></i>No especificado
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Fecha de Nacimiento</span>
                    <span class="text-white font-medium">
                        <i class="fas fa-calendar-alt text-gray-600 mr-2"></i>{{ ($user->persona && $user->persona->fecha_nacimiento) ? \Carbon\Carbon::parse($user->persona->fecha_nacimiento)->format('d / M / Y') : 'No registrada' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bloque de Autenticación -->
        <div class="card border border-gray-700/50">
            <h4 class="text-lg font-medium text-white mb-4 flex items-center border-b border-gray-800 pb-2">
                <i class="fas fa-key text-yellow-400 mr-3"></i> 
                Credenciales de Acceso
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mt-4">
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Matrícula o Pase</span>
                    <div class="flex items-center">
                        <div class="bg-gray-800 text-gray-300 font-mono px-3 py-1.5 rounded text-sm w-full border border-gray-700 border-l-4 border-l-cyan-500">
                            {{ $user->identificador }}
                        </div>
                    </div>
                </div>
                <div>
                    <span class="block text-gray-500 tracking-wider text-xs uppercase mb-1">Clave de Seguridad (Hash)</span>
                    <div class="flex items-center">
                        <div class="bg-gray-800 text-gray-500 truncate font-mono px-3 py-1.5 rounded text-sm w-full border border-gray-700 cursor-not-allowed italic" title="Por seguridad no desencriptable">
                            *********************************
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-900/20 border border-blue-800/50 p-4 rounded-lg mt-6 flex">
                <i class="fas fa-info-circle text-blue-400 mt-0.5 mr-3"></i>
                <p class="text-sm text-blue-300/80">
                    La matrícula es el valor de entrada numérico utilizado en torniquetes y en el login. 
                    Las contraseñas de visitantes y staff están protegidas mediante un Hash de sentido-único por la API y no pueden ser leídas por el Administrador. 
                    Si un usuario olvidó su contraseña, debes ir a <a href="{{ route('users.edit', $user) }}" class="text-blue-400 underline hover:text-white">Editar Perfil</a> para asignar una nueva clave temporal.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
