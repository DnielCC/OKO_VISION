@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
@php
    $isVisitor = request('role') == 3;
    $pageTitle = $isVisitor ? 'Nuevo Visitante' : 'Nuevo Registro';
    $icon = $isVisitor ? 'fa-id-badge' : 'fa-user-plus';
    $color = $isVisitor ? 'purple' : 'cyan';
@endphp
<div class="mb-8 animate-fade-in">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tighter flex items-center">
                <span class="w-10 h-10 rounded-xl bg-{{ $color }}-500/20 flex items-center justify-center mr-4 neon-border-{{ $color }}">
                    <i class="fas {{ $icon }} text-{{ $color }}-400 text-xl"></i>
                </span>
                {{ $pageTitle }}
            </h2>
            <p class="text-gray-400 mt-2 ml-14 flex items-center">
                <span class="w-2 h-2 rounded-full bg-{{ $color }}-500 mr-2 animate-pulse"></span>
                Registrar nuevo {{ $isVisitor ? 'visitante temporal' : 'miembro' }} en el sistema
            </p>
        </div>
        <a href="{{ route('users.index') }}" class="group flex items-center text-gray-400 hover:text-cyan-400 transition-all font-bold uppercase tracking-widest text-xs bg-gray-800/50 py-3 px-6 rounded-2xl border border-gray-700 hover:border-cyan-500/50">
            <i class="fas fa-chevron-left mr-3 group-hover:-translate-x-1 transition-transform"></i>
            Panel Principal
        </a>
    </div>
</div>

<div class="max-w-5xl mx-auto">
    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Izquierda: Vista Previa y Rol -->
            <div class="lg:col-span-1 space-y-8">
                <div class="glassmorphism rounded-[2.5rem] p-8 text-center relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-{{ $color }}-500 to-transparent opacity-50"></div>
                    
                    <div class="relative inline-block mb-6">
                        <div class="w-32 h-32 rounded-full border-4 border-gray-800 p-1 bg-gray-900 overflow-hidden neon-glow-{{ $color }} mx-auto">
                            <img id="preview-img" src="https://ui-avatars.com/api/?name=New+User&background=0D1117&color={{ $color == 'cyan' ? '00F2FF' : 'A855F7' }}" 
                                 class="w-full h-full object-cover rounded-full" alt="Avatar">
                        </div>
                        <label for="foto" class="absolute bottom-0 right-0 w-10 h-10 bg-{{ $color }}-500 rounded-full flex items-center justify-center text-gray-900 cursor-pointer hover:bg-{{ $color }}-400 transition-all border-4 border-gray-900 shadow-xl">
                            <i class="fas fa-camera text-sm"></i>
                            <input type="file" id="foto" name="foto" class="hidden" accept="image/*" onchange="previewImage(this)">
                        </label>
                    </div>
                    
                    <h3 id="preview-name" class="text-xl font-bold text-white mb-1">Nuevo {{ $isVisitor ? 'Visitante' : 'Usuario' }}</h3>
                    <p id="preview-role" class="text-{{ $color }}-500 font-mono text-xs uppercase tracking-widest mb-6">{{ $isVisitor ? 'Visitante' : 'Seleccionar Rol' }}</p>
                    
                    <div class="bg-gray-900/50 rounded-2xl p-4 border border-gray-800 text-left">
                        <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2">Requerimientos de Foto</p>
                        <ul class="text-[10px] text-gray-400 space-y-1">
                            <li class="flex items-center"><i class="fas fa-check text-{{ $color }}-500 mr-2"></i> Formato JPG, PNG</li>
                            <li class="flex items-center"><i class="fas fa-check text-{{ $color }}-500 mr-2"></i> Máximo 2MB</li>
                        </ul>
                    </div>
                </div>

                <div class="glassmorphism rounded-[2.5rem] p-8 space-y-6">
                    <h4 class="text-sm font-black text-white uppercase tracking-widest flex items-center">
                        <i class="fas fa-shield-halved text-orange-500 mr-3"></i>
                        Nivel de Acceso
                    </h4>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label for="id_rol" class="block text-[10px] font-bold text-orange-500 uppercase tracking-widest ml-1">Rol del Sistema</label>
                            <div class="relative">
                                @if($isVisitor)
                                    <select id="id_rol_display" class="w-full bg-gray-900/50 border border-gray-700 rounded-2xl py-4 pl-12 pr-10 text-gray-400 appearance-none cursor-not-allowed" disabled>
                                        <option value="3" selected>Visitante</option>
                                    </select>
                                    <input type="hidden" name="id_rol" value="3">
                                @else
                                    <select id="id_rol" name="id_rol" class="w-full bg-gray-900 border border-gray-700 rounded-2xl py-4 pl-12 pr-10 text-white appearance-none focus:border-{{ $color }}-500/50 focus:ring-0 transition-all cursor-pointer" required onchange="updateRolePreview(this)">
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="1" {{ old('id_rol', request('role')) == '1' ? 'selected' : '' }}>Admin Sistema</option>
                                        <option value="2" {{ old('id_rol', request('role')) == '2' ? 'selected' : '' }}>Usuario</option>
                                        <option value="3" {{ old('id_rol', request('role')) == '3' ? 'selected' : '' }}>Visitante</option>
                                    </select>
                                @endif
                                <i class="fas fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm"></i>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 text-[10px]"></i>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="identificador" class="block text-[10px] font-bold text-orange-500 uppercase tracking-widest ml-1">Matrícula / ID</label>
                            <div class="relative">
                                <input type="text" id="identificador" name="identificador" value="{{ old('identificador') }}" 
                                       class="w-full bg-gray-900 border border-gray-700 rounded-2xl py-4 pl-12 pr-4 text-white font-mono focus:border-{{ $color }}-500/50 focus:ring-0 transition-all @error('identificador') border-red-500 @enderror"
                                       required maxlength="15" placeholder="Ej. 202400123">
                                <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm"></i>
                            </div>
                            @error('identificador') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario Principal -->
            <div class="lg:col-span-2 space-y-8">
                <div class="glassmorphism rounded-[2.5rem] p-10 relative overflow-hidden">
                    <h4 class="text-xl font-bold text-white mb-10 flex items-center">
                        <span class="w-2 h-8 bg-{{ $color }}-500 rounded-full mr-4"></span>
                        Información Personal
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        <div class="space-y-3">
                            <label for="nombre" class="block text-xs font-black text-{{ $color }}-500 uppercase tracking-widest ml-1">Nombre(s)</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                   class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-{{ $color }}-500/50 focus:ring-0 transition-all @error('nombre') border-red-500 @enderror"
                                   required placeholder="Ej. Juan" oninput="updateNamePreview()">
                            @error('nombre') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <label for="apellidos" class="block text-xs font-black text-{{ $color }}-500 uppercase tracking-widest ml-1">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" 
                                   class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-{{ $color }}-500/50 focus:ring-0 transition-all @error('apellidos') border-red-500 @enderror"
                                   required placeholder="Ej. Pérez">
                            @error('apellidos') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <label for="email" class="block text-xs font-black text-{{ $color }}-500 uppercase tracking-widest ml-1">Correo Institucional</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 pl-12 pr-6 text-white focus:bg-gray-900 focus:border-{{ $color }}-500/50 focus:ring-0 transition-all @error('email') border-red-500 @enderror"
                                       required placeholder="usuario@ejemplo.com">
                                <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm"></i>
                            </div>
                            @error('email') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <label for="telefono" class="block text-xs font-black text-{{ $color }}-500 uppercase tracking-widest ml-1">Contacto Directo</label>
                            <div class="relative">
                                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" 
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 pl-12 pr-6 text-white focus:bg-gray-900 focus:border-{{ $color }}-500/50 focus:ring-0 transition-all @error('telefono') border-red-500 @enderror"
                                       maxlength="10" placeholder="10 dígitos">
                                <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-sm"></i>
                            </div>
                            @error('telefono') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <label for="sexo" class="block text-xs font-black text-purple-500 uppercase tracking-widest ml-1">Género</label>
                            <div class="flex space-x-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="sexo" value="H" class="hidden peer" {{ old('sexo') == 'H' ? 'checked' : '' }}>
                                    <div class="bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 text-center text-gray-500 peer-checked:bg-purple-500/20 peer-checked:border-purple-500 peer-checked:text-white transition-all font-bold text-xs uppercase tracking-widest @error('sexo') border-red-500 @enderror">
                                        Masculino
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="sexo" value="M" class="hidden peer" {{ old('sexo') == 'M' ? 'checked' : '' }}>
                                    <div class="bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 text-center text-gray-500 peer-checked:bg-purple-500/20 peer-checked:border-purple-500 peer-checked:text-white transition-all font-bold text-xs uppercase tracking-widest @error('sexo') border-red-500 @enderror">
                                        Femenino
                                    </div>
                                </label>
                            </div>
                            @error('sexo') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                            <label for="fecha_nacimiento" class="block text-xs font-black text-purple-500 uppercase tracking-widest ml-1">Fecha Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" 
                                   class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-4 px-6 text-white focus:bg-gray-900 focus:border-purple-500/50 focus:ring-0 transition-all @error('fecha_nacimiento') border-red-500 @enderror">
                            @error('fecha_nacimiento') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2 space-y-3 pt-4">
                            <label for="password" class="block text-xs font-black text-{{ $color }}-500 uppercase tracking-widest ml-1">Contraseña de Acceso <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="password" name="password" required
                                       class="w-full bg-gray-900/50 border border-gray-700/50 rounded-2xl py-5 pl-14 pr-6 text-white focus:bg-gray-900 focus:border-{{ $color }}-500/50 focus:ring-0 transition-all placeholder:text-gray-700 font-mono @error('password') border-red-500 @enderror"
                                       placeholder="Escriba la contraseña temporal...">
                                <i class="fas fa-key absolute left-5 top-1/2 -translate-y-1/2 text-gray-600 text-lg"></i>
                            </div>
                            @error('password') <p class="text-red-500 text-[10px] uppercase font-bold ml-1">{{ $message }}</p> @enderror
                            <div class="bg-{{ $color }}-500/5 border border-{{ $color }}-500/10 rounded-xl p-3 mt-2">
                                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Requisitos de seguridad:</p>
                                <ul class="text-[10px] text-gray-500 grid grid-cols-2 gap-x-4 gap-y-1">
                                    <li class="flex items-center"><i class="fas fa-circle-check text-{{ $color }}-500/50 mr-2 text-[8px]"></i> Mínimo 8 caracteres</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-{{ $color }}-500/50 mr-2 text-[8px]"></i> Incluir letras y números</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-{{ $color }}-500/50 mr-2 text-[8px]"></i> Sin espacios</li>
                                    <li class="flex items-center"><i class="fas fa-circle-check text-{{ $color }}-500/50 mr-2 text-[8px]"></i> Máximo 64 caracteres</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-6 mt-12 pt-8 border-t border-gray-800/50">
                        <button type="submit" class="relative group overflow-hidden bg-{{ $color }}-500 hover:bg-{{ $color }}-400 text-gray-900 font-black py-5 px-12 rounded-2xl transition-all transform hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba({{ $color == 'cyan' ? '0,242,255' : '168,85,247' }},0.3)] flex items-center uppercase tracking-[0.2em] text-sm">
                            <span class="relative z-10 flex items-center">
                                <i class="fas fa-user-check mr-3"></i>
                                Crear {{ $isVisitor ? 'Visitante' : 'Miembro' }}
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
    .neon-border-cyan {
        box-shadow: 0 0 15px rgba(0, 242, 255, 0.1);
        border: 1px solid rgba(0, 242, 255, 0.2);
    }
    .neon-border-purple {
        box-shadow: 0 0 15px rgba(168, 85, 247, 0.1);
        border: 1px solid rgba(168, 85, 247, 0.2);
    }
    .neon-glow-cyan {
        box-shadow: 0 0 20px rgba(0, 242, 255, 0.15);
    }
    .neon-glow-purple {
        box-shadow: 0 0 20px rgba(168, 85, 247, 0.15);
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

function updateNamePreview() {
    const name = document.getElementById('nombre').value;
    document.getElementById('preview-name').textContent = name || 'Nuevo Usuario';
}

function updateRolePreview(select) {
    const roleMap = {
        '1': 'Admin Sistema',
        '2': 'Usuario',
        '3': 'Visitante'
    };
    document.getElementById('preview-role').textContent = roleMap[select.value] || 'Seleccionar Rol';
}
</script>
@endsection
