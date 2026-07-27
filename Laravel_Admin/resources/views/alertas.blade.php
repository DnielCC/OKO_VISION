@extends('layouts.app')

@php
    $stats = (object) ($stats ?? [
        'total' => 0, 'critical' => 0, 'pending' => 0, 'resolved' => 0, 'resolved_pct' => 0,
    ]);
    $selectedFilters = $selectedFilters ?? (object) ['severity' => '', 'date_range' => '', 'status' => '', 'search' => ''];
@endphp

@section('content')
<!-- Filters Section -->
<div class="card mb-6">
    <form id="filters-form" method="GET" action="{{ route('alertas') }}" class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <select name="severity" class="input-field text-sm filter-select" style="width: auto;">
                <option value="">Todas las Alertas</option>
                <option value="CRITICAL" {{ $selectedFilters->severity === 'CRITICAL' ? 'selected' : '' }}>Críticas</option>
                <option value="HIGH"     {{ $selectedFilters->severity === 'HIGH'     ? 'selected' : '' }}>Altas</option>
                <option value="MEDIUM"   {{ $selectedFilters->severity === 'MEDIUM'   ? 'selected' : '' }}>Medias</option>
                <option value="LOW"      {{ $selectedFilters->severity === 'LOW'      ? 'selected' : '' }}>Bajas</option>
            </select>

            <select name="date_range" class="input-field text-sm filter-select" style="width: auto;">
                <option value="">Últimas 24 horas</option>
                <option value="24h"  {{ $selectedFilters->date_range === '24h'  ? 'selected' : '' }}>Últimas 24 horas</option>
                <option value="7d"   {{ $selectedFilters->date_range === '7d'   ? 'selected' : '' }}>Últimos 7 días</option>
                <option value="30d"  {{ $selectedFilters->date_range === '30d'  ? 'selected' : '' }}>Últimos 30 días</option>
                <option value="all"  {{ $selectedFilters->date_range === 'all'  ? 'selected' : '' }}>Todas</option>
            </select>

            <select name="status" class="input-field text-sm filter-select" style="width: auto;">
                <option value="">Todos los estados</option>
                <option value="pending"   {{ $selectedFilters->status === 'pending'   ? 'selected' : '' }}>Pendientes</option>
                <option value="review"    {{ $selectedFilters->status === 'review'    ? 'selected' : '' }}>En revisión</option>
                <option value="validated" {{ $selectedFilters->status === 'validated' ? 'selected' : '' }}>Validadas</option>
                <option value="reported"  {{ $selectedFilters->status === 'reported'  ? 'selected' : '' }}>Reportadas</option>
                <option value="resolved"  {{ $selectedFilters->status === 'resolved'  ? 'selected' : '' }}>Resueltas</option>
            </select>

            <a href="{{ route('alertas') }}" class="btn-secondary text-sm !px-3 !py-2" title="Limpiar filtros">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    id="search-input"
                    value="{{ e($selectedFilters->search) }}"
                    placeholder="Buscar alertas..."
                    class="input-field text-sm pl-10"
                    style="width: 250px;">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <button type="submit" class="btn-secondary text-sm">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.getElementById('filters-form');
                var exportBtn = form ? form.querySelector('button[type="submit"]') : null;
                var searchInput = document.getElementById('search-input');
                var searchTimer;

                function buildParams(extra) {
                    var p = new URLSearchParams();
                    var fields = ['severity','date_range','status','search','per_page'];
                    fields.forEach(function (f) {
                        var el = form.querySelector('[name="' + f + '"]');
                        if (el && el.value) p.set(f, el.value);
                    });
                    if (extra) Object.keys(extra).forEach(function (k) {
                        if (extra[k] != null && extra[k] !== '') p.set(k, extra[k]);
                    });
                    return p.toString();
                }

                if (exportBtn) {
                    exportBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        var qs = buildParams();
                        var url = "{{ route('alertas.export.excel') }}" + (qs ? '?' + qs : '');
                        window.location.href = url;
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function () {
                            var qs = buildParams();
                            window.location.href = "{{ route('alertas') }}" + (qs ? '?' + qs : '');
                        }, 500);
                    });
                    searchInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            var qs = buildParams();
                            window.location.href = "{{ route('alertas') }}" + (qs ? '?' + qs : '');
                        }
                    });
                }
            });
            </script>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Alertas</p>
                <p class="text-2xl font-bold text-white">{{ $stats->total }}</p>
                <p class="text-gray-400 text-xs mt-1">En sistema</p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Críticas</p>
                <p class="text-2xl font-bold text-red-400">{{ $stats->critical }}</p>
                <p class="text-red-400 text-xs mt-1">Requieren acción</p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-fire text-red-400 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-400">{{ $stats->pending }}</p>
                <p class="text-yellow-400 text-xs mt-1">Por validar</p>
            </div>
            <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-400 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Resueltas</p>
                <p class="text-2xl font-bold text-green-400">{{ $stats->resolved }}</p>
                <p class="text-green-400 text-xs mt-1">{{ $stats->resolved_pct }}% del total</p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Single Alerts Table: Registro de Incidencias -->
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">Registro de Incidencias</h3>
        <div class="flex items-center gap-2">
            <button class="text-gray-400 hover:text-white transition-colors" title="Vista tarjetas (próximamente)">
                <i class="fas fa-th-large"></i>
            </button>
            <button class="text-cyan-400" title="Vista tabla">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">ID</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Fecha/Hora</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Tipo</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Descripción</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Vehículo</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Ubicación</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Estado</th>
                    <th class="text-left py-3 px-4 text-cyan-400 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody id="alertas-tbody">
                @if($alertas && $alertas->count() > 0)
                    @foreach($alertas as $alerta)
                        @php
                            // Severidad visual
                            $sevMap = [
                                'CRITICAL' => ['badge' => 'badge-danger',          'label' => 'Crítico'],
                                'HIGH'     => ['badge' => 'badge-warning',         'label' => 'Alta'],
                                'MEDIUM'   => ['class' => 'bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold', 'label' => 'Media'],
                                'LOW'      => ['class' => 'bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold',    'label' => 'Baja'],
                            ];
                            $sev = $sevMap[strtoupper($alerta->severity)] ?? $sevMap['LOW'];
                            $severityBadge = isset($sev['badge'])
                                ? '<span class="' . $sev['badge'] . '">' . e($sev['label']) . '</span>'
                                : '<span class="' . $sev['class'] . '">' . e($sev['label']) . '</span>';

                            // Estado visual
                            $stMap = [
                                'PENDING'   => ['badge' => 'badge-danger',          'label' => 'Pendiente'],
                                'REVIEW'    => ['badge' => 'badge-warning',         'label' => 'En revisión'],
                                'VALIDATED' => ['class' => 'bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold', 'label' => 'Validada'],
                                'REPORTED'  => ['class' => 'bg-orange-500/20 text-orange-400 px-2 py-1 rounded-full text-xs font-semibold', 'label' => 'Reportada'],
                                'RESOLVED'  => ['badge' => 'badge-success',         'label' => 'Resuelta'],
                            ];
                            $st = $stMap[$alerta->status] ?? $stMap['PENDING'];
                            $statusBadge = isset($st['badge'])
                                ? '<span class="' . $st['badge'] . '">' . e($st['label']) . '</span>'
                                : '<span class="' . $st['class'] . '">' . e($st['label']) . '</span>';

                            $puedeValidar  = in_array($alerta->status, ['PENDING', 'REVIEW', 'REPORTED'], true);
                            $puedeReportar = $alerta->status !== 'RESOLVED';
                        @endphp
                        <tr
                            class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                            data-alert-id="{{ $alerta->id }}"
                            data-alert-status="{{ $alerta->status }}">
                            <td class="py-3 px-4">
                                <span class="text-gray-400 text-sm">#{{ $alerta->formatted_id }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div>
                                    <p class="text-white text-sm">{{ optional($alerta->created_at)->format('d/m/Y') }}</p>
                                    <p class="text-gray-400 text-xs">{{ optional($alerta->created_at)->format('H:i:s') }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                {!! $severityBadge !!}
                            </td>
                            <td class="py-3 px-4">
                                <p class="text-white text-sm font-medium">{{ $alerta->title }}</p>
                                <p class="text-gray-400 text-xs mt-0.5 max-w-md truncate">{{ $alerta->description }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    @if($alerta->vehicle_plate)
                                        <div class="w-8 h-8 bg-cyan-400/10 rounded flex items-center justify-center">
                                            <i class="fas fa-car text-cyan-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-white text-sm font-medium">{{ $alerta->vehicle_plate }}</p>
                                            <p class="text-gray-400 text-xs">{{ $alerta->vehicle_owner ?? 'Sin propietario' }}</p>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center">
                                            <i class="fas fa-ban text-gray-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-white text-sm font-medium">N/A</p>
                                            <p class="text-gray-400 text-xs">{{ $alerta->vehicle_owner ?? '—' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <p class="text-white text-sm">{{ $alerta->location ?? 'No definida' }}</p>
                                <p class="text-gray-400 text-xs">{{ $alerta->camera ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4 alert-status-col">
                                {!! $statusBadge !!}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <button class="btn-secondary text-xs px-2 py-1" onclick="viewAlert({{ $alerta->id }})">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </button>
                                    @if($puedeValidar)
                                        <button class="btn-primary text-xs px-2 py-1 alert-validate-btn" onclick="validateAlert({{ $alerta->id }})">
                                            <i class="fas fa-check mr-1"></i>Validar
                                        </button>
                                    @else
                                        <button class="text-gray-400 text-xs px-2 py-1" disabled title="Alerta ya procesada">
                                            <i class="fas fa-check mr-1"></i>Validado
                                        </button>
                                    @endif
                                    @if($puedeReportar)
                                        <button class="btn-secondary text-xs px-2 py-1 alert-report-btn" onclick="reportAlert({{ $alerta->id }})">
                                            <i class="fas fa-flag mr-1"></i>Reportar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-14 h-14 bg-gray-700/40 rounded-full flex items-center justify-center">
                                    <i class="fas fa-inbox text-gray-500 text-xl"></i>
                                </div>
                                <p class="text-white text-md font-semibold">No se encontraron alertas</p>
                                <p class="text-gray-400 text-sm">Intenta ajustar los filtros o limpiarlos para ver todos los registros.</p>
                                <a href="{{ route('alertas') }}" class="btn-secondary text-sm mt-2">Limpiar filtros</a>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Paginación dinámica -->
    @if($alertas && $alertas->hasPages())
        <div class="flex flex-wrap items-center justify-between mt-6 gap-4">
            <div class="text-sm text-gray-400">
                Mostrando
                <span class="text-white">{{ $alertas->firstItem() ?? 0 }}-{{ $alertas->lastItem() ?? 0 }}</span>
                de
                <span class="text-white">{{ $alertas->total() }}</span>
                alertas
            </div>
            <div class="flex items-center space-x-1">
                @if($alertas->onFirstPage())
                    <span class="px-3 py-1 text-gray-600 cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $alertas->previousPageUrl() }}" class="px-3 py-1 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                @php
                    $pageWindow = 2;
                    $currentPage = $alertas->currentPage();
                    $lastPage = $alertas->lastPage();
                    $pages = [];
                    for ($i = max(1, $currentPage - $pageWindow); $i <= min($lastPage, $currentPage + $pageWindow); $i++) {
                        $pages[] = $i;
                    }
                    if (!in_array(1, $pages, true)) {
                        array_unshift($pages, 1, '...');
                    }
                    if (!in_array($lastPage, $pages, true)) {
                        array_push($pages, '...', $lastPage);
                    }
                @endphp

                @foreach($pages as $p)
                    @if($p === '...')
                        <span class="px-2 text-gray-600">...</span>
                    @elseif($p == $currentPage)
                        <button class="px-3 py-1 bg-cyan-400 text-black rounded font-medium">{{ $p }}</button>
                    @else
                        <a href="{{ $alertas->url($p) }}" class="px-3 py-1 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors">
                            {{ $p }}
                        </a>
                    @endif
                @endforeach

                @if($alertas->hasMorePages())
                    <a href="{{ $alertas->nextPageUrl() }}" class="px-3 py-1 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-1 text-gray-600 cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Alert Detail Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">Detalles de Alerta</h3>
                <button onclick="closeAlertModal()" class="text-gray-400 hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="modalContent">
                <div class="flex items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mr-3"></i>
                    Cargando detalles...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const ROUTE_ALERTAS_SHOW     = @json(route('alertas.show', '__ID__'));
const ROUTE_ALERTAS_VALIDATE = @json(route('alertas.validate', '__ID__'));
const ROUTE_ALERTAS_REPORT   = @json(route('alertas.report', '__ID__'));

function resolveUrl(template, id) {
    return template.replace('__ID__', encodeURIComponent(id));
}

// Filtros: auto-submit al cambiar selects, debounce en búsqueda
document.querySelectorAll('.filter-select').forEach(sel => {
    sel.addEventListener('change', () => {
        const form = document.getElementById('filters-form');
        if (!form) return;
        const p = new URLSearchParams();
        ['severity','date_range','status','search','per_page'].forEach(f => {
            const el = form.querySelector('[name="' + f + '"]');
            if (el && el.value) p.set(f, el.value);
        });
        const qs = p.toString();
        window.location.href = "{{ route('alertas') }}" + (qs ? '?' + qs : '');
    });
});

// ====== VER alerta ======
async function viewAlert(alertId) {
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
        <div class="flex items-center justify-center py-12 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mr-3"></i>
            Cargando detalles...
        </div>
    `;
    document.getElementById('alertModal').classList.remove('hidden');

    try {
        const res = await fetch(resolveUrl(ROUTE_ALERTAS_SHOW, alertId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Error al cargar la alerta');
        renderAlertModal(json.data);
    } catch (err) {
        modalContent.innerHTML = `
            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i>
                No se pudieron cargar los detalles: ${err.message}
            </div>
        `;
    }
}

function renderAlertModal(a) {
    const severityBadge = {
        'CRITICAL': '<span class="badge-danger">Crítica</span>',
        'HIGH':     '<span class="badge-warning">Alta</span>',
        'MEDIUM':   '<span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold">Media</span>',
        'LOW':      '<span class="bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full text-xs font-semibold">Baja</span>',
    }[a.severity] || a.severity_label;

    const statusBadge = {
        'PENDING':   '<span class="badge-danger">Pendiente</span>',
        'REVIEW':    '<span class="badge-warning">En revisión</span>',
        'VALIDATED': '<span class="bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold">Validada</span>',
        'REPORTED':  '<span class="bg-orange-500/20 text-orange-400 px-2 py-1 rounded-full text-xs font-semibold">Reportada</span>',
        'RESOLVED':  '<span class="badge-success">Resuelta</span>',
    }[a.status] || a.status_label;

    const puedeValidar = ['PENDING', 'REVIEW', 'REPORTED'].includes(a.status);
    const puedeReportar = a.status !== 'RESOLVED';

    document.getElementById('modalContent').innerHTML = `
        <div class="space-y-4">
            <div class="bg-gray-800 rounded-lg p-4 min-h-[200px] flex items-center justify-center text-center overflow-hidden">
                ${a.image_url
                    ? `<img src="${a.image_url}" alt="Captura de alerta" class="w-full max-h-72 object-contain rounded-lg">`
                    : `<div class="py-10 text-gray-500">
                         <i class="fas fa-image text-4xl mb-3 opacity-50"></i>
                         <p class="text-sm">Sin captura de cámara disponible</p>
                       </div>`
                }
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">ID Alerta</p>
                    <p class="text-white font-semibold">#${a.formatted_id}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">Fecha/Hora</p>
                    <p class="text-white font-semibold">${a.created_date} ${a.created_time}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">Gravedad</p>
                    ${severityBadge}
                </div>
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">Estado</p>
                    ${statusBadge}
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-4">
                <p class="text-gray-400 text-sm mb-1">Título</p>
                <p class="text-white font-medium">${escapeHtml(a.title)}</p>
                <p class="text-gray-400 text-sm mt-2">${escapeHtml(a.description)}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">Vehículo</p>
                    <p class="text-white font-semibold">${a.vehicle_plate ? escapeHtml(a.vehicle_plate) : 'N/A'}</p>
                    <p class="text-gray-400 text-sm">${a.vehicle_owner ? escapeHtml(a.vehicle_owner) : '—'}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">Ubicación</p>
                    <p class="text-white font-semibold">${a.location ? escapeHtml(a.location) : 'No definida'}</p>
                    <p class="text-gray-400 text-sm">${a.camera ? escapeHtml(a.camera) : '—'}</p>
                </div>
            </div>

            ${a.notes ? `
            <div class="bg-gray-800 rounded-lg p-4">
                <p class="text-gray-400 text-sm mb-1">Notas / Historial</p>
                <pre class="text-white text-sm whitespace-pre-wrap font-sans">${escapeHtml(a.notes)}</pre>
            </div>` : ''}

            ${a.resolved_at ? `
            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-3 text-green-300 text-sm">
                <i class="fas fa-check-circle mr-2"></i>
                Resuelta el ${a.resolved_at}
            </div>` : ''}

            ${(puedeValidar || puedeReportar) ? `
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-700">
                ${puedeValidar ? `
                    <button class="btn-primary" onclick="validateFromModal(${a.id})">
                        <i class="fas fa-check mr-2"></i>Validar Alerta
                    </button>` : ''}
                ${puedeReportar ? `
                    <button class="btn-secondary" onclick="reportFromModal(${a.id})">
                        <i class="fas fa-flag mr-2"></i>Reportar
                    </button>` : ''}
            </div>
            ` : ''}
        </div>
    `;
}

// ====== VALIDAR alerta ======
async function validateAlert(alertId) {
    if (!confirm(`¿Validar la alerta #ALT-${String(alertId).padStart(3,'0')}?\nEsta acción marcará la alerta como revisada y resuelta.`)) return;
    try {
        const res = await fetch(resolveUrl(ROUTE_ALERTAS_VALIDATE, alertId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ notes: 'Validación manual desde panel de administración' }),
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Error');
        showNotification(json.message, 'success');
        updateRowStatus(alertId, json.data);
        setTimeout(() => { if (!document.getElementById('alertModal').classList.contains('hidden')) closeAlertModal(); }, 600);
    } catch (err) {
        showNotification('Error al validar: ' + err.message, 'error');
    }
}
function validateFromModal(id) { validateAlert(id); }

// ====== REPORTAR alerta ======
async function reportAlert(alertId) {
    const reason = prompt(`¿Por qué razón desea reportar la alerta #ALT-${String(alertId).padStart(3,'0')}?\n\nEj: Falso positivo, información incorrecta, requiere investigación adicional, etc.`);
    if (!reason) return;
    try {
        const res = await fetch(resolveUrl(ROUTE_ALERTAS_REPORT, alertId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ reason }),
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Error');
        showNotification(json.message, 'warning');
        updateRowStatus(alertId, json.data);
        setTimeout(() => { if (!document.getElementById('alertModal').classList.contains('hidden')) closeAlertModal(); }, 600);
    } catch (err) {
        showNotification('Error al reportar: ' + err.message, 'error');
    }
}
function reportFromModal(id) { reportAlert(id); }

// Actualiza la fila visualmente tras validar/reportar (sin recarga)
function updateRowStatus(alertId, data) {
    const row = document.querySelector(`tr[data-alert-id="${alertId}"]`);
    if (!row) return;

    const statusCol = row.querySelector('.alert-status-col');
    if (statusCol) {
        const badgeMap = {
            'PENDING':   '<span class="badge-danger">Pendiente</span>',
            'REVIEW':    '<span class="badge-warning">En revisión</span>',
            'VALIDATED': '<span class="bg-green-500/20 text-green-400 px-2 py-1 rounded-full text-xs font-semibold">Validada</span>',
            'REPORTED':  '<span class="bg-orange-500/20 text-orange-400 px-2 py-1 rounded-full text-xs font-semibold">Reportada</span>',
            'RESOLVED':  '<span class="badge-success">Resuelta</span>',
        };
        statusCol.innerHTML = badgeMap[data.status] || statusCol.innerHTML;
    }
    row.setAttribute('data-alert-status', data.status);

    // Botones: si ya está validada/resuelta, desactivar Validar
    const finalStates = ['VALIDATED', 'RESOLVED'];
    if (finalStates.includes(data.status)) {
        const vBtn = row.querySelector('.alert-validate-btn');
        if (vBtn) {
            const replacement = document.createElement('button');
            replacement.className = 'text-gray-400 text-xs px-2 py-1';
            replacement.disabled = true;
            replacement.title = 'Alerta ya procesada';
            replacement.innerHTML = '<i class="fas fa-check mr-1"></i>Validado';
            vBtn.replaceWith(replacement);
        }
        const rBtn = row.querySelector('.alert-report-btn');
        if (rBtn) rBtn.remove();
    }
}

// ====== Notificaciones ======
function showNotification(message, type) {
    const wrapper = document.createElement('div');
    const bg = type === 'success' ? 'bg-green-500'
             : type === 'warning' ? 'bg-yellow-500'
             : 'bg-red-500';
    const icon = type === 'success' ? 'check-circle'
               : type === 'warning' ? 'exclamation-triangle'
               : 'times-circle';
    wrapper.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${bg} text-white max-w-xs`;
    wrapper.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${icon} text-lg"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    document.body.appendChild(wrapper);
    requestAnimationFrame(() => { wrapper.style.transform = 'translateX(0)'; });
    setTimeout(() => {
        wrapper.style.transform = 'translateX(140%)';
        setTimeout(() => wrapper.remove(), 400);
    }, 3200);
}

function closeAlertModal() {
    document.getElementById('alertModal').classList.add('hidden');
}

// Cerrar modal haciendo clic afuera
document.getElementById('alertModal').addEventListener('click', function (e) {
    if (e.target === this) closeAlertModal();
});

// Util: escapar HTML en JS
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>
@endsection
