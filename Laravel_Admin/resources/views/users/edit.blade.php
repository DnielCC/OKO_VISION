@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">Editar Usuario: {{ $user->nombre }} {{ $user->apellidos }}</h2>
            <p class="text-gray-400 mt-1">Actualizar los datos y credenciales del usuario</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver
        </a>
    </div>
</div>

<div class="card max-w-4xl mx-auto">
    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Información Personal -->
        <div>
            <h4 class="text-lg font-medium text-white mb-4 border-b border-gray-700 pb-2">
                <i class="fas fa-user-edit text-cyan-400 mr-2"></i> Información Personal
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('nombre') border-red-500 @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
                    @error('nombre')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="apellidos" class="block text-sm font-medium text-gray-300 mb-2">Apellidos <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full @error('apellidos') border-red-500 @enderror" 
                           id="apellidos" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" required>
                    @error('apellidos')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email <span class="text-red-400">*</span></label>
                    <input type="email" class="input-field w-full @error('email') border-red-500 @enderror" 
                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-300 mb-2">Teléfono</label>
                    <input type="tel" class="input-field w-full @error('telefono') border-red-500 @enderror" 
                           id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" maxlength="10">
                    @error('telefono')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Identidad Física -->
        <div>
            <h4 class="text-lg font-medium text-white mb-4 border-b border-gray-700 pb-2">
                <i class="fas fa-fingerprint text-purple-400 mr-2"></i> Identidad Física
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="sexo" class="block text-sm font-medium text-gray-300 mb-2">Sexo</label>
                    <select class="input-field w-full @error('sexo') border-red-500 @enderror" id="sexo" name="sexo">
                        <option value="" class="bg-gray-900">Seleccionar...</option>
                        <option value="H" class="bg-gray-900" {{ old('sexo', request()->route('user')->persona->sexo ?? '') == 'H' ? 'selected' : '' }}>Hombre (H)</option>
                        <option value="M" class="bg-gray-900" {{ old('sexo', request()->route('user')->persona->sexo ?? '') == 'M' ? 'selected' : '' }}>Mujer (M)</option>
                    </select>
                    @error('sexo')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-300 mb-2">Fecha de Nacimiento</label>
                    <input type="date" class="input-field w-full @error('fecha_nacimiento') border-red-500 @enderror" 
                           id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', request()->route('user')->persona->fecha_nacimiento ?? '') }}">
                    @error('fecha_nacimiento')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-300 mb-2">Foto de Perfil (Opcional)</label>
                    <input type="file" accept="image/*" class="input-field w-full p-2 text-sm @error('foto') border-red-500 @enderror" 
                           id="foto" name="foto">
                    @error('foto')
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
                    <label for="identificador" class="block text-sm font-medium text-gray-300 mb-2">Matrícula <span class="text-red-400">*</span></label>
                    <input type="text" class="input-field w-full font-mono @error('identificador') border-red-500 @enderror" 
                           id="identificador" name="identificador" value="{{ old('identificador', $user->identificador) }}" required maxlength="9" placeholder="Max. 9 números (Ej. 123456789)">
                    @error('identificador')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="id_rol" class="block text-sm font-medium text-gray-300 mb-2">Rol del Sistema <span class="text-red-400">*</span></label>
                    <select class="input-field w-full @error('id_rol') border-red-500 @enderror" id="id_rol" name="id_rol" required>
                        <option value="1" class="bg-gray-900" {{ old('id_rol', $user->id_rol) == '1' ? 'selected' : '' }}>Administrador</option>
                        <option value="2" class="bg-gray-900" {{ old('id_rol', $user->id_rol) == '2' ? 'selected' : '' }}>Usuario Operador</option>
                        <option value="3" class="bg-gray-900" {{ old('id_rol', $user->id_rol) == '3' ? 'selected' : '' }}>Visitante</option>
                    </select>
                    @error('id_rol')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Cambiar Contraseña Temporal</label>
                    <input type="text" class="input-field w-full bg-gray-900/40 text-gray-400 @error('password') border-red-500 @enderror" 
                           id="password" name="password" minlength="6" placeholder="Dejar en blanco para no cambiar">
                    <p class="text-gray-500 text-xs mt-1">Escribe nueva contraseña aquí si deseas reiniciar el acceso del usuario.</p>
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
                Actualizar Usuario
            </button>
        </div>
    </form>
</div>
@endsection
