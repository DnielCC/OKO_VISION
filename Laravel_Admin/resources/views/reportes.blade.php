@extends('layouts.app')

@section('content')
<?php
// Helpers locales (no tocar, solo para esta vista)
$pct = function($n, $d) { $d = (float)$d; return $d > 0 ? round(($n * 100) / $d, 1) : 0; };
$totalDist = $distribucion->sum('value') ?: 1;

// Preparar datos para gráficas (adaptables al tipo de reporte)
$trendMax   = max(1, (int)($trend['max']     ?? $tendencia->max(fn($d) => max((int)($d->a ?? $d->entradas ?? 0), (int)($d->b ?? $d->salidas ?? 0))) ?? 1));
$hourMax    = max(1, (int)($hourly['max']    ?? max($actividadHora)));
$donutCenterValue = $donut['centerValue']    ?? ($stats->total_accesos ?? 0);
$trendColorA = $trend['colorA'] ?? '#22d3ee';
$trendColorB = $trend['colorB'] ?? '#10b981';
$trendLabelA = $trend['legendA'] ?? 'Serie A';
$trendLabelB = $trend['legendB'] ?? 'Serie B';
$trendTitle  = $trend['title']   ?? 'Tendencia';
$trendYLabel = $trend['yLabel']  ?? 'Registros';
$donutTitle  = $donut['title']   ?? 'Distribución';
$hourTitle   = $hourly['title']  ?? 'Actividad por Hora';
$hourYLabel  = $hourly['yLabel'] ?? 'Registros';

// Tipo actual por defecto
$_tipo = $currentTipo ?? 'all';
?>
<!-- Header Section -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Reportes y Estadísticas</h2>
        <p class="text-gray-400 mt-1">
            Análisis detallado del sistema de control de acceso
            <span class="ml-2 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-full px-3 py-0.5 text-xs">
                Período: {{ $periodLabel }}
            </span>
        </p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="exportReport('pdf')" class="btn-secondary">
            <i class="fas fa-file-pdf mr-2"></i>
            Exportar PDF
        </button>
        <button onclick="exportReport('excel')" class="btn-secondary">
            <i class="fas fa-file-excel mr-2"></i>
            Exportar Excel
        </button>
    </div>
</div>

@if(!empty($error))
    <div class="bg-red-500/20 border border-red-500/50 text-red-400 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            {{ $error }}
        </div>
    </div>
@endif

@if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            {{ session('success') }}
        </div>
    </div>
@endif

<!-- Date Range Filter -->
<div class="card mb-6">
    <form id="report-filter-form" action="{{ route('reportes') }}" method="GET" class="flex flex-wrap items-center gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Período</label>
            <select id="periodSelect" name="period" class="input-field" style="width: auto;">
                @foreach($periodOptions as $val => $lbl)
                    <option value="{{ $val }}" {{ $currentPeriod === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div id="customDateRange" class="{{ $currentPeriod !== 'custom' ? 'hidden' : '' }}">
            <label class="block text-sm font-medium text-gray-300 mb-2">Rango de Fechas</label>
            <div class="flex items-center gap-2">
                <input type="date" id="startDate" name="start_date" value="{{ $currentStart }}" class="input-field">
                <span class="text-gray-400">a</span>
                <input type="date" id="endDate" name="end_date" value="{{ $currentEnd }}" class="input-field">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Tipo de Reporte</label>
            <select name="tipo" class="input-field" style="width: auto;">
                @foreach($tipoOptions as $val => $lbl)
                    <option value="{{ $val }}" {{ $currentTipo === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary mt-6">
            <i class="fas fa-sync-alt mr-2"></i>
            Actualizar
        </button>
    </form>
</div>

<!-- Key Metrics -->
@switch($_tipo)
@case('alertas')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Alertas en período</p>
                <p class="text-2xl font-bold text-white">{{ number_format($contextStats->alertas_periodo, 0, ',', '.') }}</p>
                <p class="text-red-400 text-xs mt-1">
                    <i class="fas fa-triangle-exclamation mr-1"></i>
                    Pendientes: {{ number_format($contextStats->alertas_pendientes_periodo) }}
                </p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-red-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Críticas / Altas</p>
                <p class="text-2xl font-bold text-yellow-300">
                    {{ number_format(($resumenAlertas->firstWhere('key','CRITICAL')?->total ?? 0) + ($resumenAlertas->firstWhere('key','HIGH')?->total ?? 0)) }}
                </p>
                <p class="text-gray-500 text-xs mt-1">
                    C {{ number_format($resumenAlertas->firstWhere('key','CRITICAL')?->total ?? 0) }}
                    ·
                    A {{ number_format($resumenAlertas->firstWhere('key','HIGH')?->total ?? 0) }}
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-fire text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Resueltas</p>
                <p class="text-2xl font-bold text-green-400">
                    {{ number_format($resumenAlertas->firstWhere('key','TOTAL')?->resueltas ?? 0) }}
                </p>
                <p class="text-gray-500 text-xs mt-1">
                    @php $tot = max(1,(int)($contextStats->alertas_periodo ?? 0)); @endphp
                    {{ $pct($resumenAlertas->firstWhere('key','TOTAL')?->resueltas ?? 0, $tot) }}% del período
                </p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-circle-check text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Usuarios / Vehículos / Globales</p>
                <p class="text-lg font-bold text-white">
                    U <span class="text-cyan-400">{{ number_format($contextStats?->usuarios_activos_periodo ?? 0) }}</span>
                    · V <span class="text-purple-400">{{ number_format($contextStats?->vehiculos_periodo ?? 0) }}</span>
                    · A <span class="text-yellow-400">{{ number_format($stats->total_alertas) }}</span>
                </p>
                <p class="text-gray-500 text-xs mt-1">Totales del período</p>
            </div>
            <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-pie text-purple-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>
@break

@case('usuarios')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Usuarios Registrados</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats->total_usuarios) }}</p>
                <p class="text-cyan-400 text-xs mt-1">
                    <i class="fas fa-users mr-1"></i>Activos en período: {{ number_format($contextStats->usuarios_activos_periodo ?? 0) }}
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
                <p class="text-gray-400 text-sm">Accesos Totales</p>
                <p class="text-2xl font-bold text-green-400">{{ number_format($stats->total_accesos) }}</p>
                <p class="text-gray-500 text-xs mt-1">En período actual</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Visitantes</p>
                <p class="text-2xl font-bold text-purple-400">{{ number_format($stats->visitantes) }}</p>
                <p class="text-gray-500 text-xs mt-1">Accesos de rol visitante</p>
            </div>
            <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-tie text-purple-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Alertas / Vehículos</p>
                <p class="text-lg font-bold text-white">
                    A <span class="text-yellow-400">{{ number_format($contextStats->alertas_periodo ?? 0) }}</span>
                    · V <span class="text-purple-400">{{ number_format($contextStats->vehiculos_periodo ?? 0) }}</span>
                </p>
                <p class="text-gray-500 text-xs mt-1">Durante el período seleccionado</p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-pie text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>
@break

@case('vehiculos')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Vehículos Detectados</p>
                <p class="text-2xl font-bold text-white">{{ number_format($contextStats->vehiculos_periodo ?? 0) }}</p>
                <p class="text-purple-400 text-xs mt-1">
                    <i class="fas fa-car mr-1"></i>Placas únicas en período
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-car text-purple-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Accesos de Vehículos</p>
                <p class="text-2xl font-bold text-cyan-400">{{ number_format($stats->total_accesos) }}</p>
                <p class="text-gray-500 text-xs mt-1">
                    E {{ number_format($stats->entradas) }} · S {{ number_format($stats->salidas) }}
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-right-left text-cyan-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Autorizados</p>
                <p class="text-2xl font-bold text-green-400">{{ number_format($stats->autorizados) }}</p>
                <p class="text-gray-500 text-xs mt-1">
                    {{ $pct($stats->autorizados, max(1,$stats->total_accesos)) }}% del total
                </p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-circle-check text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Registrados / Alertas</p>
                <p class="text-lg font-bold text-white">
                    R <span class="text-cyan-400">{{ number_format($stats->total_vehiculos) }}</span>
                    · A <span class="text-yellow-400">{{ number_format($contextStats->alertas_periodo ?? 0) }}</span>
                </p>
                <p class="text-gray-500 text-xs mt-1">Registro vs alertas en período</p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-gauge-high text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>
@break

@case('accesos')
@case('all')
@default
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Accesos</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats->total_accesos, 0, ',', '.') }}</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-check mr-1"></i>Entradas {{ number_format($stats->entradas) }} · Salidas {{ number_format($stats->salidas) }}
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-cyan-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Autorizados</p>
                <p class="text-2xl font-bold text-green-400">{{ number_format($stats->autorizados) }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ $pct($stats->autorizados, $stats->total_accesos) }}% del total</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-circle-check text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Denegados</p>
                <p class="text-2xl font-bold text-red-400">{{ number_format($stats->denegados) }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ $pct($stats->denegados, $stats->total_accesos) }}% del total</p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-circle-xmark text-red-400 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Alertas · Usuarios · Vehículos</p>
                <p class="text-lg font-bold text-white">
                    A <span class="text-yellow-400">{{ number_format($contextStats->alertas_periodo ?? 0) }}</span>
                    · U <span class="text-cyan-400">{{ number_format($contextStats->usuarios_activos_periodo ?? 0) }}</span>
                    · V <span class="text-purple-400">{{ number_format($contextStats->vehiculos_periodo ?? 0) }}</span>
                </p>
                <p class="text-gray-500 text-xs mt-1">Totales del período seleccionado</p>
            </div>
            <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-pie text-purple-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>
@endswitch

<!-- Primary Table (depende del tipo) -->
<div class="card overflow-hidden mb-6">
    <div class="flex items-center justify-between px-6 pt-5 pb-3">
        <h3 class="text-lg font-semibold text-white">{{ $primaryTitle ?? 'Registros' }}</h3>
        <span class="text-xs text-gray-500 font-mono bg-gray-800/60 px-3 py-1 rounded-full">
            Mostrando {{ $primaryCount ?? 0 }} de {{ number_format($primaryTotal ?? 0) }}
        </span>
    </div>
    <div class="overflow-x-auto">

        @if($primaryKind === 'access')
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">ID</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Usuario</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Vehículo</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Tipo</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Fecha/Hora</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($primaryTabla as $acceso)
                @php
                    $nombre = trim(($acceso->persona_nombre ?? '') . ' ' . ($acceso->persona_apellidos ?? ''));
                    if (!$nombre) $nombre = $acceso->user?->nombre ? ($acceso->user->nombre . ' ' . ($acceso->user->apellidos ?? '')) : 'Usuario #' . ($acceso->user_id ?? '?');
                    $isEntry = strtoupper((string)$acceso->access_type) === 'ENTRY';
                @endphp
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-300 font-mono text-sm">#{{ $acceso->id }}</td>
                    <td class="px-6 py-4 text-white">{{ $nombre }}</td>
                    <td class="px-6 py-4">
                        <span class="text-cyan-400 font-mono">
                            <i class="fas fa-car mr-1 text-xs text-gray-500"></i>{{ $acceso->vehicle_plate ?: 'Sin vehículo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs {{ $isEntry ? 'text-green-400' : 'text-yellow-400' }}">
                            <i class="fas {{ $isEntry ? 'fa-sign-in-alt' : 'fa-sign-out-alt' }} mr-1"></i>
                            {{ $isEntry ? 'Entrada' : 'Salida' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">{{ optional($acceso->access_time)->format('d/m/Y H:i:s') }}</td>
                    <td class="px-6 py-4">
                        @if($acceso->is_authorized)
                            <span class="bg-green-400/10 text-green-400 text-[10px] px-2 py-1 rounded-full border border-green-400/20 uppercase font-bold">Autorizado</span>
                        @else
                            <span class="bg-red-400/10 text-red-400 text-[10px] px-2 py-1 rounded-full border border-red-400/20 uppercase font-bold">Denegado</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-door-closed text-4xl mb-3 opacity-50 block"></i>
                        No hay accesos registrados para el período y tipo seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($primaryKind === 'alert')
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">ID Alerta</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Título</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Severidad</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Estado</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Ubicación / Vehículo</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Fecha/Hora</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($primaryTabla as $alert)
                @php
                    $sevColor = match(strtoupper((string)$alert->severity)){
                        'CRITICAL' => 'text-red-400 bg-red-400/10 border-red-400/20',
                        'HIGH'     => 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20',
                        'MEDIUM'   => 'text-blue-400 bg-blue-400/10 border-blue-400/20',
                        default    => 'text-gray-400 bg-gray-400/10 border-gray-400/20',
                    };
                    $statusColor = $alert->is_resolved
                        ? 'text-green-400 bg-green-400/10 border-green-400/20'
                        : 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20';
                @endphp
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-gray-300 font-mono text-sm">
                        #{{ $alert->formatted_id ?? $alert->id }}
                    </td>
                    <td class="px-6 py-4 text-white">
                        <div>{{ $alert->title }}</div>
                        <div class="text-gray-500 text-xs mt-0.5 line-clamp-1">{{ Str::limit($alert->description ?? '', 70) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] px-2 py-1 rounded-full border uppercase font-bold {{ $sevColor }}">
                            {{ $alert->severity_label ?? $alert->severity }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] px-2 py-1 rounded-full border uppercase font-bold {{ $statusColor }}">
                            {{ $alert->status_label ?? $alert->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-300 text-sm">
                            <i class="fas fa-location-dot text-gray-500 mr-1"></i>
                            {{ $alert->location ?? 'N/A' }}
                        </div>
                        <div class="text-cyan-400 font-mono text-xs mt-1">
                            <i class="fas fa-car text-gray-500 mr-1"></i>
                            {{ $alert->vehicle_plate ?? ($alert->vehicle?->plate ?? 'Sin vehículo') }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">
                        {{ optional($alert->created_at)->format('d/m/Y H:i:s') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-triangle-exclamation text-4xl mb-3 opacity-50 block"></i>
                        No hay alertas para el período seleccionado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($primaryKind === 'user')
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Usuario</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Email</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Rol</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Accesos (período)</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Último acceso</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($primaryTabla as $u)
                @php
                    $persona = $u->persona;
                    $nombre = trim(($persona?->nombre ?? '') . ' ' . ($persona?->apellidos ?? ''));
                    if (!$nombre) $nombre = $u->email ? explode('@', $u->email)[0] : ('Usuario #' . $u->id);
                    $iniciales = strtoupper(substr((string)($persona?->nombre ?? '?'), 0, 1) . substr((string)($persona?->apellidos ?? '?'), 0, 1));
                @endphp
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if(!empty($persona?->foto))
                                <img src="{{ $persona->foto }}" class="w-9 h-9 rounded-full object-cover" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xs font-bold">
                                    {{ $iniciales }}
                                </div>
                            @endif
                            <div>
                                <div class="text-white font-medium">{{ $nombre }}</div>
                                <div class="text-gray-500 text-xs">ID #{{ $u->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-300 text-sm">{{ $u->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="bg-purple-400/10 text-purple-400 text-[10px] px-2 py-1 rounded-full border border-purple-400/20 uppercase font-bold">
                            {{ $u->role?->nombre ?? 'Sin rol' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-white font-mono">
                        {{ number_format($u->accesos_periodo ?? 0) }}
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">
                        {{ !empty($u->ultimo_periodo) ? Carbon\Carbon::parse($u->ultimo_periodo)->diffForHumans() : 'Sin accesos' }}
                    </td>
                    <td class="px-6 py-4">
                        @if(($u->activo ?? true) !== false && ($u->status ?? 'active') !== 'inactive')
                            <span class="bg-green-400/10 text-green-400 text-[10px] px-2 py-1 rounded-full border border-green-400/20 uppercase font-bold">Activo</span>
                        @else
                            <span class="bg-gray-400/10 text-gray-400 text-[10px] px-2 py-1 rounded-full border border-gray-400/20 uppercase font-bold">Inactivo</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users-slash text-4xl mb-3 opacity-50 block"></i>
                        No hay usuarios para mostrar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($primaryKind === 'vehicle')
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Placa</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Total Accesos</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Entradas / Salidas</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Autorizados</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Primer acceso</th>
                    <th class="px-6 py-4 text-gray-400 font-medium text-sm">Último acceso</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($primaryTabla as $v)
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4 text-cyan-400 font-mono font-bold text-sm">
                        <i class="fas fa-car mr-1 text-xs text-gray-500"></i>{{ $v->vehicle_plate }}
                    </td>
                    <td class="px-6 py-4 text-white font-mono">{{ number_format($v->total_accesos) }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="text-green-400 mr-3">E {{ number_format($v->entradas ?? 0) }}</span>
                        <span class="text-yellow-400">S {{ number_format($v->salidas ?? 0) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @php $vTotal = max(1, (int)($v->total_accesos ?? 0)); @endphp
                        <span class="text-green-400">{{ number_format($v->autorizados ?? 0) }}</span>
                        <span class="text-gray-500 text-xs ml-2">
                            ({{ round((($v->autorizados ?? 0) * 100) / $vTotal, 1) }}%)
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">
                        {{ !empty($v->primer_acceso) ? Carbon\Carbon::parse($v->primer_acceso)->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-sm">
                        {{ !empty($v->ultimo_acceso) ? Carbon\Carbon::parse($v->ultimo_acceso)->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-car text-4xl mb-3 opacity-50 block"></i>
                        No hay vehículos detectados en el período.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @endif

    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Trend Chart (adaptable) -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">{{ $trendTitle }}</h3>
            <div class="text-xs text-gray-500 bg-gray-800/60 px-3 py-1 rounded-full">Últimos 7 días</div>
        </div>

        <div class="relative" style="height: 300px;">
            <svg viewBox="0 0 600 300" class="w-full h-full">
                <!-- Grilla -->
                <g stroke="#1f2937" stroke-width="1">
                    <line x1="50" y1="30"  x2="580" y2="30"/>
                    <line x1="50" y1="90"  x2="580" y2="90"/>
                    <line x1="50" y1="150" x2="580" y2="150"/>
                    <line x1="50" y1="210" x2="580" y2="210"/>
                    <line x1="50" y1="260" x2="580" y2="260"/>
                </g>
                <!-- Eje Y -->
                <g fill="#6b7280" font-size="10" text-anchor="end">
                    <text x="40" y="34">{{ $trendMax }}</text>
                    <text x="40" y="94">{{ (int)round($trendMax*0.75) }}</text>
                    <text x="40" y="154">{{ (int)round($trendMax*0.5) }}</text>
                    <text x="40" y="214">{{ (int)round($trendMax*0.25) }}</text>
                    <text x="40" y="264">0</text>
                </g>
                <text x="18" y="150" transform="rotate(-90,18,150)" fill="#6b7280" font-size="10" text-anchor="middle">{{ $trendYLabel }}</text>

                <?php
                $n = max(1, $tendencia->count());
                $w = 580 - 50;
                $colW = $w / $n;
                $barGroupW = $colW * 0.7;
                $barW = $barGroupW / 2.4;
                $baseY = 260;
                ?>
                <!-- Etiquetas X + barras -->
                @foreach($tendencia as $idx => $day)
                    <?php
                    $x0 = 50 + $colW * $idx + ($colW - $barGroupW) / 2;
                    $xA = $x0;
                    $xB = $x0 + $barW + 2;
                    $aVal = (int)($day->a ?? $day->entradas ?? 0);
                    $bVal = (int)($day->b ?? $day->salidas ?? 0);
                    $hA = ($aVal / $trendMax) * 230;
                    $hB = ($bVal / $trendMax) * 230;
                    ?>
                    <rect x="{{ $xA }}" y="{{ $baseY - $hA }}" width="{{ $barW }}" height="{{ $hA }}" rx="3" fill="{{ $trendColorA }}" opacity="0.85"/>
                    <rect x="{{ $xB }}" y="{{ $baseY - $hB }}" width="{{ $barW }}" height="{{ $hB }}" rx="3" fill="{{ $trendColorB }}" opacity="0.85"/>
                    <text x="{{ 50 + $colW*$idx + $colW/2 }}" y="285" fill="#6b7280" font-size="10" text-anchor="middle">{{ $day->label }}</text>
                @endforeach
            </svg>
        </div>

        <div class="flex items-center justify-center gap-6 mt-1">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background:{{ $trendColorA }}"></div>
                <span class="text-gray-400 text-sm">{{ $trendLabelA }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background:{{ $trendColorB }}"></div>
                <span class="text-gray-400 text-sm">{{ $trendLabelB }}</span>
            </div>
        </div>
    </div>

    <!-- Distribution Donut (adaptable) -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">{{ $donutTitle }}</h3>
            <div class="text-xs text-gray-500 bg-gray-800/60 px-3 py-1 rounded-full">
                {{ number_format($donutCenterValue) }} registros
            </div>
        </div>

        <div class="relative" style="height: 300px;">
            <svg viewBox="-110 -110 220 220" class="w-full h-full" style="max-height:260px">
                <?php
                $r = 90;
                $rInner = 62;
                $cx = 0; $cy = 0;
                $acc = 0;
                $total = $distribucion->sum('value');
                $segments = [];
                foreach ($distribucion as $d) {
                    $v = (int)($d['value'] ?? 0);
                    if ($total <= 0) { $segments[] = null; continue; }
                    $p = $v / $total;
                    $start = $acc * 2 * M_PI - M_PI/2;
                    $acc += $p;
                    $end   = $acc * 2 * M_PI - M_PI/2;
                    $segments[] = [$start, $end, $d['color'], $d['key'], $v];
                }
                $any = false;
                foreach ($segments as $seg) {
                    if ($seg === null) continue;
                    [$s, $e, $c, $k, $v] = $seg;
                    if ($v <= 0) continue;
                    $any = true;
                    $large = ($e - $s) > M_PI ? 1 : 0;
                    if ($v == $total) {
                        echo '<circle cx="0" cy="0" r="' . $r . '" fill="' . e($c) . '"/>' . "\n";
                    } else {
                        $x1 = $cx + $r * cos($s); $y1 = $cy + $r * sin($s);
                        $x2 = $cx + $r * cos($e); $y2 = $cy + $r * sin($e);
                        $xi2 = $cx + $rInner * cos($e); $yi2 = $cy + $rInner * sin($e);
                        $xi1 = $cx + $rInner * cos($s); $yi1 = $cy + $rInner * sin($s);
                        $d = "M $x1 $y1 A $r $r 0 $large 1 $x2 $y2 L $xi2 $yi2 A $rInner $rInner 0 $large 0 $xi1 $yi1 Z";
                        echo '<path d="' . $d . '" fill="' . e($c) . '"/>' . "\n";
                    }
                }
                if (!$any) {
                    echo '<circle cx="0" cy="0" r="' . $r . '" fill="#374151" opacity="0.5"/>';
                }
                echo '<circle cx="0" cy="0" r="' . $rInner . '" fill="#0b1220" stroke="#111827" stroke-width="1"/>';
                ?>
                <text x="0" y="-6" text-anchor="middle" fill="#fff" font-size="28" font-weight="700">{{ number_format($donutCenterValue) }}</text>
                <text x="0" y="16" text-anchor="middle" fill="#9ca3af" font-size="11">Total</text>
            </svg>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-0">
            @foreach($distribucion as $d)
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" style="background: {{ $d['color'] }}"></div>
                <span class="text-gray-400 text-sm">
                    {{ $d['label'] }} ({{ $distribucionPct[$d['key']] ?? 0 }}%)
                    <span class="text-gray-600 text-xs">· {{ number_format((int)($d['value'] ?? 0)) }}</span>
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Hourly Activity Chart (adaptable) -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">{{ $hourTitle }}</h3>
        <div class="text-xs text-gray-500 bg-gray-800/60 px-3 py-1 rounded-full">Picos según horario</div>
    </div>

    <div class="relative" style="height: 250px;">
        <svg viewBox="0 0 800 250" class="w-full h-full">
            <g stroke="#1f2937" stroke-width="1">
                <line x1="40" y1="20"  x2="780" y2="20"/>
                <line x1="40" y1="70"  x2="780" y2="70"/>
                <line x1="40" y1="120" x2="780" y2="120"/>
                <line x1="40" y1="170" x2="780" y2="170"/>
                <line x1="40" y1="210" x2="780" y2="210"/>
            </g>
            <g fill="#6b7280" font-size="10" text-anchor="end">
                <text x="30" y="24">{{ $hourMax }}</text>
                <text x="30" y="74">{{ (int)round($hourMax*0.75) }}</text>
                <text x="30" y="124">{{ (int)round($hourMax*0.5) }}</text>
                <text x="30" y="174">{{ (int)round($hourMax*0.25) }}</text>
                <text x="30" y="214">0</text>
            </g>
            <text x="12" y="120" transform="rotate(-90,12,120)" fill="#6b7280" font-size="10" text-anchor="middle">{{ $hourYLabel }}</text>
            <?php
            $startX = 40; $endX = 780; $totalW = $endX - $startX;
            $baseY = 210;
            $barras = 24;
            $slot = $totalW / $barras;
            $barW = max(4, $slot * 0.62);
            $offset = ($slot - $barW) / 2;
            $mostrarHoras = [0,2,4,6,8,10,12,14,16,18,20,22];
            ?>
            @foreach($actividadHora as $h => $count)
                <?php
                $hx = $startX + $slot*$h + $offset;
                $hh = ($count / $hourMax) * 190;
                ?>
                <rect x="{{ $hx }}" y="{{ $baseY - $hh }}" width="{{ $barW }}" height="{{ $hh }}" rx="2" fill="#22d3ee" opacity="0.55"/>
                @if(in_array($h, $mostrarHoras, true))
                    <text x="{{ $startX + $slot*$h + $slot/2 }}" y="232" fill="#6b7280" font-size="10" text-anchor="middle">{{ $h }}</text>
                @endif
            @endforeach
        </svg>
    </div>
</div>

<!-- Detailed Tables (condicionales por tipo) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    @if(in_array($_tipo, ['all','accesos','usuarios'], true))
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Usuarios Más Activos</h3>
            <a href="{{ route('users.index') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Usuario</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Accesos</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Último</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topUsuarios as $u)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                @if(!empty($u->foto))
                                    <img src="{{ $u->foto }}" alt="User" class="w-6 h-6 rounded-full object-cover">
                                @else
                                    <div class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-[10px] font-bold">
                                        {{ $u->iniciales }}
                                    </div>
                                @endif
                                <span class="text-white text-sm">{{ $u->nombre_completo }}</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm font-mono">{{ number_format($u->accesos) }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">{{ $u->ultimo }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 px-3 text-center text-gray-500 text-sm">
                            <i class="fas fa-users-slash text-3xl mb-2 opacity-50 block"></i>
                            No hay actividad de usuarios en el período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(in_array($_tipo, ['all','accesos','alertas'], true))
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Resumen de Alertas</h3>
            <a href="{{ route('alertas') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">Ver detalles</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Tipo</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Total</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Resueltas</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenAlertas as $row)
                    <?php $isTotal = $row->key === 'TOTAL'; ?>
                    <tr class="{{ $loop->remaining === 1 ? '' : 'border-b border-gray-800' }}">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full" style="background: {{ $row->color }}"></div>
                                <span class="{{ $isTotal ? 'font-bold text-cyan-400' : 'text-white' }} text-sm">{{ $row->label }}</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="{{ $isTotal ? 'font-semibold' : '' }} text-white text-sm">{{ number_format($row->total) }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="{{ $isTotal ? 'font-semibold' : '' }} text-green-400 text-sm">{{ number_format($row->resueltas) }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="{{ $isTotal ? 'font-semibold' : '' }} text-yellow-400 text-sm">{{ number_format($row->pendientes) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($_tipo === 'vehiculos')
    <div class="card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Top Vehículos del Período</h3>
            <span class="text-xs text-gray-500 bg-gray-800/60 px-3 py-1 rounded-full">Primeros 10</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Placa</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Accesos</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Entradas/Salidas</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">% Autorizado</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Última detección</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($primaryTabla instanceof \Illuminate\Support\Collection ? $primaryTabla->take(10) : collect([])) as $v)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3 text-cyan-400 font-mono font-bold text-sm">
                            <i class="fas fa-car mr-1 text-xs text-gray-500"></i>{{ $v->vehicle_plate }}
                        </td>
                        <td class="py-2 px-3 text-white text-sm font-mono">{{ number_format($v->total_accesos ?? 0) }}</td>
                        <td class="py-2 px-3 text-sm">
                            <span class="text-green-400 mr-2">E {{ number_format($v->entradas ?? 0) }}</span>
                            <span class="text-yellow-400">S {{ number_format($v->salidas ?? 0) }}</span>
                        </td>
                        <td class="py-2 px-3 text-sm text-green-400">
                            @php $t = max(1,(int)($v->total_accesos ?? 0)); @endphp
                            {{ round((($v->autorizados ?? 0) * 100) / $t, 1) }}%
                        </td>
                        <td class="py-2 px-3 text-gray-400 text-sm">
                            {{ !empty($v->ultimo_acceso) ? Carbon\Carbon::parse($v->ultimo_acceso)->diffForHumans() : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 px-3 text-center text-gray-500 text-sm">
                            <i class="fas fa-car text-3xl mb-2 opacity-50 block"></i>
                            Sin vehículos en el período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($_tipo === 'usuarios')
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Distribución por Rol</h3>
            <span class="text-xs text-gray-500 bg-gray-800/60 px-3 py-1 rounded-full">Usuarios totales: {{ number_format($stats->total_usuarios) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Rol</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Usuarios</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">% del total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribucion as $d)
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full" style="background: {{ $d['color'] }}"></div>
                                <span class="text-white text-sm">{{ $d['label'] }}</span>
                            </div>
                        </td>
                        <td class="py-2 px-3 text-white text-sm font-mono">{{ number_format((int)($d['value'] ?? 0)) }}</td>
                        <td class="py-2 px-3 text-gray-400 text-sm">
                            {{ $distribucionPct[$d['key']] ?? 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 px-3 text-center text-gray-500 text-sm">Sin distribución por rol.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg max-w-md w-full border border-gray-700">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Exportar Reporte</h3>
                <button onclick="closeExportModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Formato</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="format" value="pdf" class="mr-2" checked>
                            <span class="text-gray-300">PDF (Recomendado para impresión)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="format" value="excel" class="mr-2">
                            <span class="text-gray-300">Excel (Para análisis de datos)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="format" value="csv" class="mr-2">
                            <span class="text-gray-300">CSV (Datos brutos)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Incluir</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="include_charts" class="mr-2" checked>
                            <span class="text-gray-300">Gráficos y visualizaciones</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="include_tables" class="mr-2" checked>
                            <span class="text-gray-300">Tablas detalladas</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="include_summary" class="mr-2" checked>
                            <span class="text-gray-300">Resumen ejecutivo</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button onclick="closeExportModal()" class="btn-secondary">Cancelar</button>
                <button onclick="confirmExport()" class="btn-primary">
                    <i class="fas fa-download mr-2"></i>Exportar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Helper notificaciones (compatibilidad)
window.showNotification = window.showNotification || function(message, type) {
    const colors = { success:'bg-green-500', warning:'bg-yellow-500', danger:'bg-red-500', info:'bg-blue-500', error:'bg-red-500' };
    const icons  = { success:'fa-check-circle', warning:'fa-exclamation-triangle', danger:'fa-times-circle', info:'fa-info-circle', error:'fa-times-circle' };
    const color = colors[type] || colors.info;
    const icon  = icons[type]  || icons.info;
    const n = document.createElement('div');
    n.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${color} text-white`;
    n.innerHTML = `<div class="flex items-center space-x-3"><i class="fas ${icon}"></i><span>${message}</span></div>`;
    document.body.appendChild(n);
    setTimeout(()=> n.style.transform = 'translateX(0)', 80);
    setTimeout(()=>{
        n.style.transform = 'translateX(140%)';
        setTimeout(()=> n.remove(), 400);
    }, 3000);
};

function exportReport(format) {
    const modal = document.getElementById('exportModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const radios = document.querySelectorAll('input[name="format"]');
    radios.forEach(r => r.checked = (r.value === format));
}
function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}
function confirmExport() {
    const format = document.querySelector('input[name="format"]:checked').value;
    const charts = document.querySelector('input[name="include_charts"]').checked ? '1' : '0';
    const tables = document.querySelector('input[name="include_tables"]').checked ? '1' : '0';
    const summary = document.querySelector('input[name="include_summary"]').checked ? '1' : '0';
    const params = new URLSearchParams({
        format, charts, tables, summary,
        period: '{{ $currentPeriod }}',
        tipo:   '{{ $currentTipo }}',
        start_date: '{{ $currentStart }}',
        end_date:   '{{ $currentEnd }}',
    });
    showNotification(`Generando reporte en formato ${format.toUpperCase()}...`, 'info');
    setTimeout(() => {
        closeExportModal();
        let base;
        if (format === 'pdf') {
            base = "{{ route('reportes.export.pdf') }}";
        } else if (format === 'excel') {
            base = "{{ route('reportes.export.excel') }}";
        } else {
            base = "{{ route('reportes.export.csv') }}";
        }
        window.location.href = `${base}?${params.toString()}`;
        showNotification(`Reporte ${format.toUpperCase()} generado exitosamente`, 'success');
    }, 1000);
}

// Filtros (auto submit)
const periodSelect = document.getElementById('periodSelect');
if (periodSelect) {
    periodSelect.addEventListener('change', () => {
        const custom = document.getElementById('customDateRange');
        if (periodSelect.value === 'custom') custom.classList.remove('hidden');
        else custom.classList.add('hidden');
        if (periodSelect.value !== 'custom') {
            document.getElementById('report-filter-form').submit();
        }
    });
}
document.querySelectorAll('#report-filter-form select[name="tipo"]').forEach(s => {
    s.addEventListener('change', () => document.getElementById('report-filter-form').submit());
});

// Cierre modal con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeExportModal();
});
</script>
@endsection
