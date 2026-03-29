@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Crear Nuevo Usuario</h2>
            <p class="text-gray-400 mt-1">Registrar un nuevo miembro y asignarle una contraseña temporal</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </a>
    </div>
</div>

<div class="card max-w-4xl mx-auto">
    <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Información Personal -->
        <div>
            <h4 class="text-lg font-medium text-white mb-4 border-b border-gray-700 pb-2">
                <i class="fas fa-id-card text-cyan-400 mr-2"></i> Información Personal
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('nombre') border-red-500 @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Juan">
                    @error('nombre')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="apellidos" class="block text-sm font-medium text-gray-300 mb-2">Apellidos <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('apellidos') border-red-500 @enderror" 
                           id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required placeholder="Ej. Pérez">
                    @error('apellidos')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email <span class="text-red-400">*</span></label>
                    <input type="email" class="input-field w-full @error('email') border-red-500 @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required placeholder="juan@ejemplo.com">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-300 mb-2">Teléfono</label>
                    <input type="tel" class="input-field w-full @error('telefono') border-red-500 @enderror" 
                           id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="10" placeholder="10 dígitos">
                    @error('telefono')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Credenciales de Acceso -->
        <div>
            <h4 class="text-lg font-medium text-white mb-4 border-b border-gray-700 pb-2 mt-8">
                <i class="fas fa-lock text-cyan-400 mr-2"></i> Credenciales de Acceso
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="identificador" class="block text-sm font-medium text-gray-300 mb-2">Identificador (Username) <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('identificador') border-red-500 @enderror" 
                           id="identificador" name="identificador" value="{{ old('identificador') }}" required maxlength="15" placeholder="Ej. jperez">
                    @error('identificador')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="id_rol" class="block text-sm font-medium text-gray-300 mb-2">Rol del Sistema <span class="text-red-400">*</span></label>
                    <select class="input-field w-full @error('id_rol') border-red-500 @enderror" id="id_rol" name="id_rol" required>
                        <option value="" class="bg-gray-900">Seleccionar rol...</option>
                        <option value="1" class="bg-gray-900" {{ old('id_rol') == '1' ? 'selected' : '' }}>Administrador</option>
                        <option value="2" class="bg-gray-900" {{ old('id_rol') == '2' ? 'selected' : '' }}>Usuario Operador</option>
                        <option value="3" class="bg-gray-900" {{ old('id_rol') == '3' ? 'selected' : '' }}>Visitante</option>
                    </select>
                    @error('id_rol')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Contraseña Temporal <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('password') border-red-500 @enderror" 
                           id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres (Ej. temporal123)">
                    <p class="text-gray-400 text-xs mt-1">El usuario deberá utilizar esta contraseña la primera vez que ingrese.</p>
                    @error('password')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i>
                Guardar Usuario
            </button>
        </div>
    </form>
</div>
@endsection
