@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="mb-8 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tighter flex items-center">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center mr-4 neon-border">
                    <i class="fas fa-user-gear text-cyan-400 text-xl"></i>
                </span>
                Perfil de Usuario
            </h2>
            <p class="text-gray-400 mt-2 ml-14 flex items-center">
                <span class="w-2 h-2 rounded-full bg-cyan-500 mr-2 animate-pulse"></span>
                Editando: <span class="text-cyan-400 font-bold ml-1">{{ $user->nombre }} {{ $user->apellidos }}</span>
            </p>
        </div>
        <a href="{{ route('users.index') }}" class="group flex items-center text-gray-400 hover:text-cyan-400 transition-all font-bold uppercase tracking-widest text-xs bg-gray-800/50 py-3 px-6 rounded-2xl border border-gray-700 hover:border-cyan-500/50">
            <i class="fas fa-chevron-left mr-3 group-hover:-translate-x-1 transition-transform"></i>
            Panel Principal
        </a>
    </div>
</div>

<div class="max-w-5xl mx-auto">
    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Izquierda: Perfil y Foto -->
            <div class="lg:col-span-1 space-y-8">
                <div class="glassmorphism rounded-[2.5rem] p-8 text-center relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-cyan-500 to-transparent opacity-50"></div>
                    
                    <div class="relative inline-block mb-6">
                        <div class="w-32 h-32 rounded-full border-4 border-gray-800 p-1 bg-gray-900 overflow-hidden neon-glow mx-auto">
                            <img id="preview-img" src="{{ $user->persona->foto ?? 'https://ui-avatars.com/api/?name='.urlencode($user->nombre).'&background=0D1117&color=00F2FF' }}" 
                                 class="w-full h-full object-cover rounded-full" alt="Avatar">
                        </div>
                        <label for="foto" class="absolute bottom-0 right-0 w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center text-gray-900 cursor-pointer hover:bg-cyan-400 transition-all border-4 border-gray-900 shadow-xl">
                            <i class="fas fa-camera text-sm"></i>
                            <input type="file" id="foto" name="foto" class="hidden" accept="image/*" onchange="previewImage(this)">
                        </label>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-1">{{ $user->nombre }}</h3>
                    <p class="text-cyan-500 font-mono text-xs uppercase tracking-widest mb-6">{{ $user->getRoleLabel() }}</p>
                    
                    <div class="flex flex-col space-y-3 text-left">
                        <div class="bg-gray-900/50 rounded-2xl p-4 border border-gray-800">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block mb-1">Identificador</label>
                            <span class="text-white font-mono text-sm tracking-tighter">{{ $user->identificador }}</span>
                        </div>
                        <div class="bg-gray-900/50 rounded-2xl p-4 border border-gray-800">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block mb-1">Fecha Registro</label>
                            <span class="text-white font-mono text-sm tracking-tighter">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Credenciales Rápidas -->
                <div class="glassmorphism rounded-[2.5rem] p-8 space-y-6">
                    <h4 class="text-sm font-black text-white uppercase tracking-widest flex items-center">
                        <i class="fas fa-shield-halved text-orange-500 mr-3"></i>
                        Seguridad
                    </h4>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label for="identificador" class="block text-[10px] font-bold text-orange-500 uppercase tracking-widest ml-1">Nueva Matrícula / ID</label>
                            <div class="relative group">
                                <input type="text" id="identificador" name="identificador" value="{{ old('identificador', $user->identificador) }}" 
                                       class="w-full bg-gray-900 border border-gray-700 rounded-2xl py-4 pl-12 pr-4 text-white font-mono focus:border-orange-500/50 focus:ring-0 transition-all group-hover:border-gray-600"
                                       required maxlength="15">
                                <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm transition-colors group-focus-within:text-orange-500"></i>
                            </div>
                            <p class="text-[9px] text-gray-500 ml-1 italic">Identificador único del sistema (mín. 3 caracteres)</p>
                        </div>
                        <div class="space-y-2">
                            <label for="id_rol" class="block text-[10px] font-bold text-orange-500 uppercase tracking-widest ml-1">Nivel de Acceso</label>
                            <div class="relative group">
                                @if($user->id_rol == 3)
                                    <!-- Si es visitante, el rol está bloqueado -->
                                    <div class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 pl-12 pr-10 text-gray-400 font-bold flex items-center cursor-not-allowed">
                                        Visitante
                                    </div>
                                    <input type="hidden" name="id_rol" value="3">
                                @else
                                    <select id="id_rol" name="id_rol" class="w-full bg-gray-900 border border-gray-700 rounded-2xl py-4 pl-12 pr-10 text-white appearance-none focus:border-orange-500/50 focus:ring-0 transition-all cursor-pointer group-hover:border-gray-600">
                                        <option value="1" {{ old('id_rol', $user->id_rol) == '1' ? 'selected' : '' }}>Administrador</option>
                                        <option value="2" {{ old('id_rol', $user->id_rol) == '2' ? 'selected' : '' }}>Usuario</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 text-[10px]"></i>
                                @endif
                                <i class="fas fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm transition-colors group-focus-within:text-orange-500"></i>
                            </div>
                            @if($user->id_rol == 3)
                                <p class="text-[9px] text-gray-500 ml-1 italic">El rol de visitante no puede ser cambiado</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario Principal -->
            <div class="lg:col-span-2 space-y-8">
                <div class="glassmorphism rounded-[2.5rem] p-10 relative overflow-hidden border border-white/5">
                    <div class="flex items-center justify-between mb-10">
                        <h4 class="text-xl font-bold text-white flex items-center">
                            <span class="w-2 h-8 bg-cyan-500 rounded-full mr-4 shadow-[0_0_15px_rgba(6,182,212,0.5)]"></span>
                            Información Detallada
                        </h4>
                        <span class="px-4 py-1.5 bg-cyan-500/10 border border-cyan-500/20 rounded-full text-[10px] font-bold text-cyan-400 uppercase tracking-widest">
                            Datos de Persona
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                        <!-- Nombre -->
                        <div class="space-y-3 group">
                            <label for="nombre" class="block text-xs font-black text-cyan-500 uppercase tracking-widest ml-1">Nombre(s)</label>
                            <div class="relative">
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $user->nombre) }}" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-cyan-500/50 focus:ring-0 transition-all group-hover:border-gray-600"
                                       required>
                            </div>
                            @error('nombre') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Apellidos -->
                        <div class="space-y-3 group">
                            <label for="apellidos" class="block text-xs font-black text-cyan-500 uppercase tracking-widest ml-1">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" 
                                   class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-cyan-500/50 focus:ring-0 transition-all group-hover:border-gray-600"
                                   required>
                            @error('apellidos') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-3 group">
                            <label for="email" class="block text-xs font-black text-cyan-500 uppercase tracking-widest ml-1">Correo Institucional</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 pl-12 pr-6 text-white focus:bg-gray-900 focus:border-cyan-500/50 focus:ring-0 transition-all group-hover:border-gray-600"
                                       required>
                                <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm group-focus-within:text-cyan-500 transition-colors"></i>
                            </div>
                            @error('email') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Telefono -->
                        <div class="space-y-3 group">
                            <label for="telefono" class="block text-xs font-black text-cyan-500 uppercase tracking-widest ml-1">Teléfono de Contacto</label>
                            <div class="relative">
                                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 pl-12 pr-6 text-white focus:bg-gray-900 focus:border-cyan-500/50 focus:ring-0 transition-all group-hover:border-gray-600"
                                       maxlength="10" placeholder="Ej: 4421234567">
                                <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm group-focus-within:text-cyan-500 transition-colors"></i>
                            </div>
                            <p class="text-[9px] text-gray-500 ml-1 italic">Máximo 10 dígitos sin espacios</p>
                        </div>

                        <!-- Sexo -->
                        <div class="space-y-3 group">
                            <label for="sexo" class="block text-xs font-black text-purple-500 uppercase tracking-widest ml-1">Género</label>
                            <div class="flex space-x-4">
                                <label class="flex-1 cursor-pointer group/radio">
                                    <input type="radio" name="sexo" value="H" class="hidden peer" {{ old('sexo', $user->persona->sexo ?? '') == 'H' ? 'checked' : '' }}>
                                    <div class="bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 text-center text-gray-500 peer-checked:bg-purple-500/20 peer-checked:border-purple-500 peer-checked:text-white transition-all font-bold text-xs uppercase tracking-widest group-hover/radio:border-gray-600">
                                        Masculino
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer group/radio">
                                    <input type="radio" name="sexo" value="M" class="hidden peer" {{ old('sexo', $user->persona->sexo ?? '') == 'M' ? 'checked' : '' }}>
                                    <div class="bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 text-center text-gray-500 peer-checked:bg-purple-500/20 peer-checked:border-purple-500 peer-checked:text-white transition-all font-bold text-xs uppercase tracking-widest group-hover/radio:border-gray-600">
                                        Femenino
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Nacimiento -->
                        <div class="space-y-3 group">
                            <label for="fecha_nacimiento" class="block text-xs font-black text-purple-500 uppercase tracking-widest ml-1">Fecha Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->persona->fecha_nacimiento ?? '') }}" 
                                   class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-purple-500/50 focus:ring-0 transition-all group-hover:border-gray-600">
                        </div>

                        <!-- Password -->
                        <div class="md:col-span-2 space-y-3 pt-6 border-t border-gray-800/50">
                            <label for="password" class="block text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Actualizar Seguridad <span class="text-gray-600 normal-case font-medium ml-2">(Dejar en blanco para mantener actual)</span></label>
                            <div class="relative group">
                                <input type="password" id="password" name="password" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-5 pl-14 pr-6 text-white focus:bg-gray-900 focus:border-orange-500/50 focus:ring-0 transition-all placeholder:text-gray-700 group-hover:border-gray-600"
                                       placeholder="Escriba la nueva contraseña de acceso...">
                                <i class="fas fa-lock-open absolute left-5 top-1/2 -translate-y-1/2 text-gray-600 text-lg group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            @error('password') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                            <div class="bg-orange-500/5 border border-orange-500/10 rounded-xl p-3 mt-2">
                                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Requisitos de seguridad:</p>
                                <ul class="text-[10px] text-gray-500 grid grid-cols-2 gap-x-4 gap-y-1">
                                    <li class="flex items-center"><i class="fas fa-circle-check text-orange-500/50 mr-2 text-[8px]"></i> Mínimo 8 caracteres</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-orange-500/50 mr-2 text-[8px]"></i> Incluir letras y números</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-orange-500/50 mr-2 text-[8px]"></i> Sin espacios</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-orange-500/50 mr-2 text-[8px]"></i> Máximo 64 caracteres</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-6 mt-12 pt-8 border-t border-gray-800/50">
                        <button type="submit" class="relative group overflow-hidden bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-black py-5 px-12 rounded-2xl transition-all transform hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(0,242,255,0.3)] flex items-center uppercase tracking-[0.2em] text-sm">
                            <span class="relative z-10 flex items-center">
                                <i class="fas fa-floppy-disk mr-3"></i>
                                Guardar Cambios
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: 0.5;
        cursor: pointer;
    }
</style>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
