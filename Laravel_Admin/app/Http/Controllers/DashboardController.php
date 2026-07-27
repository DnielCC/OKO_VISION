<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccessLog;
use App\Models\Alert;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        try {
            $dashboardData = $this->buildDashboardData();
            extract($dashboardData);
        } catch (\Exception $e) {
            $accesos_hoy = 0;
            $vehiculos_registrados = 0;
            $alertas_activas = 0;
            $ultimos_accesos = collect();
            $ultimas_alertas = collect();
            $actividad_reciente = collect();
        }

        $api_token = session('api_token');

        return view('dashboard', compact(
            'accesos_hoy',
            'vehiculos_registrados',
            'alertas_activas',
            'ultimos_accesos',
            'ultimas_alertas',
            'actividad_reciente',
            'api_token'
        ));
    }

    public function getDashboardData()
    {
        try {
            $dashboardData = $this->buildDashboardData();

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function usuarios()
    {
        try {
            $usuarios = $this->apiService->getUsers();
            return view('usuarios', compact('usuarios'));
        } catch (\Exception $e) {
            return view('usuarios', ['usuarios' => []])->with('error', 'Error al cargar usuarios: ' . $e->getMessage());
        }
    }

    public function alertas(Request $request)
    {
        try {
            $filters = $request->only(['severity', 'date_range', 'status', 'search']);

            $stats = [
                'total'     => Alert::count(),
                'critical'  => Alert::where('severity', 'CRITICAL')->count(),
                'pending'   => Alert::whereIn('status', [Alert::STATUS_PENDING, Alert::STATUS_REVIEW])->count(),
                'resolved'  => Alert::where('status', Alert::STATUS_RESOLVED)->count(),
            ];
            $stats['resolved_pct'] = $stats['total'] > 0
                ? round(($stats['resolved'] / $stats['total']) * 100, 1)
                : 0;

            $perPage = (int) $request->get('per_page', 10);
            $alertas = Alert::query()
                ->applyFilters($filters)
                ->latest('created_at')
                ->paginate($perPage)
                ->withQueryString();

            $selectedFilters = (object) [
                'severity'   => $request->get('severity', ''),
                'date_range' => $request->get('date_range', ''),
                'status'     => $request->get('status', ''),
                'search'     => $request->get('search', ''),
            ];

            return view('alertas', compact('alertas', 'stats', 'selectedFilters'));
        } catch (\Exception $e) {
            $stats = ['total' => 0, 'critical' => 0, 'pending' => 0, 'resolved' => 0, 'resolved_pct' => 0];
            $alertas = collect([]);
            $selectedFilters = (object) ['severity' => '', 'date_range' => '', 'status' => '', 'search' => ''];
            return view('alertas', compact('alertas', 'stats', 'selectedFilters'))
                ->with('error', 'Error al cargar alertas: ' . $e->getMessage());
        }
    }

    public function showAlert(Alert $alert)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id'             => $alert->id,
                'formatted_id'   => $alert->formatted_id,
                'title'          => $alert->title,
                'description'    => $alert->description,
                'severity'       => $alert->severity,
                'severity_label' => $alert->severity_label,
                'status'         => $alert->status,
                'status_label'   => $alert->status_label,
                'vehicle_plate'  => $alert->vehicle_plate,
                'vehicle_owner'  => $alert->vehicle_owner,
                'location'       => $alert->location,
                'camera'         => $alert->camera,
                'is_resolved'    => (bool) $alert->is_resolved,
                'notes'          => $alert->notes,
                'image_url'      => $alert->image_url,
                'created_at'     => optional($alert->created_at)->format('d/m/Y H:i:s'),
                'created_date'   => optional($alert->created_at)->format('d/m/Y'),
                'created_time'   => optional($alert->created_at)->format('H:i:s'),
                'resolved_at'    => optional($alert->resolved_at)?->format('d/m/Y H:i:s'),
            ],
        ]);
    }

    public function validateAlert(Request $request, Alert $alert)
    {
        try {
            $alert->status = Alert::STATUS_VALIDATED;
            $alert->is_resolved = true;
            $alert->resolved_at = now();
            if ($request->filled('notes')) {
                $alert->notes = ($alert->notes ? $alert->notes . "\n" : '')
                    . '[' . now()->format('d/m/Y H:i') . '] Validación: ' . $request->input('notes');
            }
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'Alerta validada exitosamente',
                'data'    => [
                    'status'       => $alert->status,
                    'status_label' => $alert->status_label,
                    'is_resolved'  => true,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar la alerta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reportAlert(Request $request, Alert $alert)
    {
        try {
            $request->validate([
                'reason' => 'required|string|min:3|max:500',
            ]);

            $alert->status = Alert::STATUS_REPORTED;
            $alert->notes = ($alert->notes ? $alert->notes . "\n" : '')
                . '[' . now()->format('d/m/Y H:i') . '] Reporte: ' . $request->input('reason');
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'Alerta reportada exitosamente',
                'data'    => [
                    'status'       => $alert->status,
                    'status_label' => $alert->status_label,
                    'is_resolved'  => (bool) $alert->is_resolved,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reportar la alerta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resolveAlert(Alert $alert)
    {
        try {
            $alert->status = Alert::STATUS_RESOLVED;
            $alert->is_resolved = true;
            $alert->resolved_at = now();
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como resuelta',
                'data'    => [
                    'status'       => $alert->status,
                    'status_label' => $alert->status_label,
                    'is_resolved'  => true,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al resolver la alerta: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buildAccessBaseQuery($startDate, $endDate, $withSelectFull = false)
    {
        $base = AccessLog::query()
            ->leftJoin('usuarios', 'access_logs.user_id', '=', 'usuarios.id')
            ->leftJoin('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->whereBetween('access_logs.access_time', [$startDate, $endDate]);

        if ($withSelectFull) {
            $base->select(
                'access_logs.*',
                'personas.nombre as persona_nombre',
                'personas.apellidos as persona_apellidos'
            );
        }
        return $base;
    }

    public function reportes(Request $request)
    {
        try {
            [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
            $tipoReporte = $request->get('tipo', 'all');

            $accesosBaseFull = $this->buildAccessBaseQuery($startDate, $endDate, true);
            $accesosBaseAgg  = $this->buildAccessBaseQuery($startDate, $endDate, false);

            $includeAccesos = in_array($tipoReporte, ['all', 'accesos'], true);

            if ($includeAccesos) {
                $totalAccesos = (clone $accesosBaseFull)->count();
                $accesosTabla = (clone $accesosBaseFull)
                    ->orderByDesc('access_logs.access_time')
                    ->limit(25)
                    ->get();
            } else {
                $totalAccesos = 0;
                $accesosTabla = collect([]);
            }

            $stats = (object)[
                'total_accesos'      => $totalAccesos,
                'entradas'           => 0,
                'salidas'            => 0,
                'autorizados'        => 0,
                'denegados'          => 0,
                'visitantes'         => 0,
                'pendientes'         => 0,
                'total_usuarios'     => User::count(),
                'total_vehiculos'    => DB::table('vehicles')->count(),
                'total_alertas'      => Alert::count(),
            ];

            $computeStatsFrom = function ($baseAgg) use ($stats) {
                $entryExit = (clone $baseAgg)
                    ->selectRaw('access_logs.access_type, count(*) as total')
                    ->groupBy('access_logs.access_type')
                    ->pluck('total', 'access_type')
                    ->all();
                $stats->entradas = (int)($entryExit['ENTRY'] ?? 0);
                $stats->salidas  = (int)($entryExit['EXIT']  ?? 0);

                $authStats = (clone $baseAgg)
                    ->selectRaw('access_logs.is_authorized, count(*) as total')
                    ->groupBy('access_logs.is_authorized')
                    ->pluck('total', 'is_authorized')
                    ->all();
                $stats->autorizados = (int)($authStats[true] ?? $authStats[1] ?? 0);
                $stats->denegados   = (int)($authStats[false] ?? $authStats[0] ?? 0);

                $visitId = \App\Models\Role::idByName(\App\Models\Role::NAME_VISITANTE);
                if ($visitId !== null) {
                    $stats->visitantes = (int)(clone $baseAgg)
                        ->where('usuarios.id_rol', $visitId)
                        ->count();
                }
            };

            if ($totalAccesos > 0) {
                $computeStatsFrom($accesosBaseAgg);
            }

            if ($tipoReporte === 'usuarios') {
                $baseUsu = (clone $accesosBaseAgg);
                $stats->total_accesos = (clone $baseUsu)->count();
                if ($stats->total_accesos > 0) {
                    $computeStatsFrom($baseUsu);
                }
            }

            if ($tipoReporte === 'vehiculos') {
                $baseVeh = (clone $accesosBaseAgg)
                    ->whereNotNull('access_logs.vehicle_plate')
                    ->where('access_logs.vehicle_plate', '!=', '');
                $stats->total_accesos = (clone $baseVeh)->count();
                if ($stats->total_accesos > 0) {
                    $entryExit = (clone $baseVeh)
                        ->selectRaw('access_logs.access_type, count(*) as total')
                        ->groupBy('access_logs.access_type')
                        ->pluck('total', 'access_type')
                        ->all();
                    $stats->entradas = (int)($entryExit['ENTRY'] ?? 0);
                    $stats->salidas  = (int)($entryExit['EXIT']  ?? 0);

                    $authStats = (clone $baseVeh)
                        ->selectRaw('access_logs.is_authorized, count(*) as total')
                        ->groupBy('access_logs.is_authorized')
                        ->pluck('total', 'is_authorized')
                        ->all();
                    $stats->autorizados = (int)($authStats[true] ?? $authStats[1] ?? 0);
                    $stats->denegados   = (int)($authStats[false] ?? $authStats[0] ?? 0);

                    $visitId = \App\Models\Role::idByName(\App\Models\Role::NAME_VISITANTE);
                    if ($visitId !== null) {
                        $stats->visitantes = (int)(clone $baseVeh)
                            ->where('usuarios.id_rol', $visitId)
                            ->count();
                    }
                }
            }

            $contextStats = $this->buildContextStats($startDate, $endDate, $tipoReporte, $stats);

            [$primaryTitle, $primaryKind, $primaryTabla, $primaryCount, $primaryTotal] =
                $this->buildPrimaryDataset($tipoReporte, $startDate, $endDate, $accesosTabla, $stats);

            $trend    = $this->buildTrendGeneric($endDate, $tipoReporte);
            $donut    = $this->buildDonutGeneric($tipoReporte, $stats, $contextStats);
            $hourly   = $this->buildHourlyGeneric($startDate, $endDate, $tipoReporte);

            $tendencia      = $trend['series'];
            $topUsuarios    = $this->buildTopUsers($startDate, $endDate, $tipoReporte);
            $resumenAlertas = $this->buildAlertSummary($startDate, $endDate, $tipoReporte);
            $distribucion   = $donut['items'];
            $actividadHora  = $hourly['values'];

            $total = $distribucion->sum('value') ?: 1;
            $distribucionPct = $distribucion->mapWithKeys(function ($item) use ($total) {
                return [$item['key'] => round(($item['value'] * 100) / $total, 1)];
            });

            $periodOptions = [
                'today'    => 'Hoy',
                'week'     => 'Última Semana',
                'month'    => 'Último Mes',
                'quarter'  => 'Último Trimestre',
                'year'     => 'Último Año',
                'custom'   => 'Personalizado',
            ];
            $tipoOptions = [
                'all'       => 'Todos los datos',
                'accesos'   => 'Accesos',
                'alertas'   => 'Alertas',
                'usuarios'  => 'Usuarios',
                'vehiculos' => 'Vehículos',
            ];
            $currentPeriod = $request->get('period', 'week');
            $currentTipo   = $request->get('tipo', 'all');
            $currentStart  = $request->get('start_date', optional($startDate)->format('Y-m-d'));
            $currentEnd    = $request->get('end_date',   optional($endDate)->format('Y-m-d'));

            return view('reportes', compact(
                'accesosTabla',
                'primaryTabla',
                'primaryTitle',
                'primaryKind',
                'primaryCount',
                'primaryTotal',
                'stats',
                'contextStats',
                'tendencia',
                'trend',
                'donut',
                'hourly',
                'distribucion',
                'distribucionPct',
                'actividadHora',
                'topUsuarios',
                'resumenAlertas',
                'periodLabel',
                'periodOptions',
                'tipoOptions',
                'currentPeriod',
                'currentTipo',
                'currentStart',
                'currentEnd'
            ));
        } catch (\Exception $e) {
            report($e);
            $emptyStats = (object)[
                'total_accesos'=>0,'entradas'=>0,'salidas'=>0,'autorizados'=>0,
                'denegados'=>0,'visitantes'=>0,'pendientes'=>0,
                'total_usuarios'=>0,'total_vehiculos'=>0,'total_alertas'=>0,
            ];
            $ctx = (object)[
                'alertas_periodo'=>0,'alertas_pendientes_periodo'=>0,
                'usuarios_activos_periodo'=>0,'vehiculos_periodo'=>0,
                'show_alertas'=>0,'show_usuarios'=>0,'show_vehiculos'=>0,
            ];
            return view('reportes', [
                'accesosTabla'   => collect([]),
                'primaryTabla'   => collect([]),
                'primaryTitle'   => 'Sin datos',
                'primaryKind'    => 'access',
                'primaryCount'   => 0,
                'primaryTotal'   => 0,
                'stats'          => $emptyStats,
                'contextStats'   => $ctx,
                'tendencia'      => collect(),
                'trend'          => ['title'=>'', 'series'=>collect(), 'yLabel'=>'', 'legendA'=>'', 'legendB'=>'', 'colorA'=>'#22d3ee', 'colorB'=>'#10b981', 'max'=>1],
                'donut'          => ['title'=>'', 'centerLabel'=>'', 'centerValue'=>0, 'items'=>collect()],
                'hourly'         => ['title'=>'', 'yLabel'=>'', 'values'=>array_fill(0,24,0), 'max'=>1],
                'distribucion'   => collect(),
                'distribucionPct'=> collect(),
                'actividadHora'  => array_fill(0, 24, 0),
                'topUsuarios'    => collect(),
                'resumenAlertas' => collect(),
                'periodLabel'    => 'Error',
                'periodOptions'  => [],
                'tipoOptions'    => [],
                'currentPeriod'  => 'week',
                'currentTipo'    => 'all',
                'currentStart'   => now()->format('Y-m-d'),
                'currentEnd'     => now()->format('Y-m-d'),
                'error'          => 'Error al cargar reportes: ' . $e->getMessage(),
            ]);
        }
    }

    private function buildContextStats($startDate, $endDate, string $tipoReporte, object $stats): object
    {
        $ctx = (object)[
            'alertas_periodo'            => 0,
            'alertas_pendientes_periodo' => 0,
            'usuarios_activos_periodo'   => 0,
            'vehiculos_periodo'          => 0,
            'show_alertas'               => 0,
            'show_usuarios'              => 0,
            'show_vehiculos'             => 0,
        ];

        $showAll = $tipoReporte === 'all';

        if ($showAll || $tipoReporte === 'alertas') {
            $ctx->show_alertas = 1;
            $ctx->alertas_periodo = Alert::whereBetween('created_at', [$startDate, $endDate])->count();
            $ctx->alertas_pendientes_periodo = Alert::whereBetween('created_at', [$startDate, $endDate])
                ->where(function($q){
                    $q->whereIn('status', [Alert::STATUS_PENDING, Alert::STATUS_REVIEW, Alert::STATUS_REPORTED])
                      ->orWhere('is_resolved', false);
                })
                ->count();
        }

        if ($showAll || $tipoReporte === 'usuarios') {
            $ctx->show_usuarios = 1;
            $ctx->usuarios_activos_periodo = AccessLog::query()
                ->join('usuarios', 'access_logs.user_id', '=', 'usuarios.id')
                ->whereBetween('access_logs.access_time', [$startDate, $endDate])
                ->distinct('usuarios.id')
                ->count('usuarios.id');
        }

        if ($showAll || $tipoReporte === 'vehiculos') {
            $ctx->show_vehiculos = 1;
            $ctx->vehiculos_periodo = AccessLog::query()
                ->whereBetween('access_time', [$startDate, $endDate])
                ->whereNotNull('vehicle_plate')
                ->where('vehicle_plate', '!=', '')
                ->distinct('vehicle_plate')
                ->count('vehicle_plate');
        }

        return $ctx;
    }

    private function buildPrimaryDataset(string $tipo, $startDate, $endDate, $defaultAccesos, object $stats): array
    {
        switch ($tipo) {
            case 'alertas':
                $rows = Alert::with(['vehicle'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest('created_at')
                    ->limit(25)
                    ->get();
                $total = Alert::whereBetween('created_at', [$startDate, $endDate])->count();
                return ['Últimas Alertas', 'alert', $rows, $rows->count(), $total];

            case 'usuarios':
                $rows = User::with(['persona', 'role'])
                    ->withCount(['accessLogs as accesos_periodo' => function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('access_time', [$startDate, $endDate]);
                    }])
                    ->withMax(['accessLogs as ultimo_periodo' => function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('access_time', [$startDate, $endDate]);
                    }], 'access_time')
                    ->orderByDesc('accesos_periodo')
                    ->orderBy('id')
                    ->limit(25)
                    ->get()
                    ->map(function ($u) {
                        $u->loadMissing('persona', 'role');
                        return $u;
                    });
                $total = User::count();
                return ['Usuarios del Sistema (actividad en período)', 'user', $rows, $rows->count(), $total];

            case 'vehiculos':
                $rows = AccessLog::query()
                    ->select(
                        'vehicle_plate',
                        DB::raw('count(*) as total_accesos'),
                        DB::raw('max(access_time) as ultimo_acceso'),
                        DB::raw('min(access_time) as primer_acceso'),
                        DB::raw("sum(case when is_authorized = true then 1 else 0 end) as autorizados"),
                        DB::raw("sum(case when access_type = 'ENTRY' then 1 else 0 end) as entradas"),
                        DB::raw("sum(case when access_type = 'EXIT'  then 1 else 0 end) as salidas")
                    )
                    ->whereBetween('access_time', [$startDate, $endDate])
                    ->whereNotNull('vehicle_plate')
                    ->where('vehicle_plate', '!=', '')
                    ->groupBy('vehicle_plate')
                    ->orderByDesc('total_accesos')
                    ->limit(25)
                    ->get();
                $total = AccessLog::query()
                    ->whereBetween('access_time', [$startDate, $endDate])
                    ->whereNotNull('vehicle_plate')
                    ->where('vehicle_plate', '!=', '')
                    ->distinct('vehicle_plate')
                    ->count('vehicle_plate');
                return ['Vehículos Detectados', 'vehicle', $rows, $rows->count(), $total];

            case 'accesos':
                return ['Últimos Accesos', 'access', $defaultAccesos, $defaultAccesos->count(), $stats->total_accesos];

            case 'all':
            default:
                return ['Últimos Accesos', 'access', $defaultAccesos, $defaultAccesos->count(), $stats->total_accesos];
        }
    }

    private function buildTrendGeneric($endDate, string $tipo): array
    {
        $days = collect();
        $end = Carbon::parse($endDate)->startOfDay();
        for ($i = 6; $i >= 0; $i--) {
            $d = (clone $end)->subDays($i);
            $days->push((object)[
                'label'   => $d->format('d/m'),
                'dateKey' => $d->format('Y-m-d'),
                'a'       => 0,
                'b'       => 0,
            ]);
        }

        $default = [
            'title'   => 'Tendencia (últimos 7 días)',
            'series'  => $days,
            'yLabel'  => 'Registros',
            'legendA' => 'Serie A',
            'legendB' => 'Serie B',
            'colorA'  => '#22d3ee',
            'colorB'  => '#10b981',
            'max'     => 1,
        ];

        $windowStart = (clone $end)->subDays(6)->startOfDay();
        $windowEnd   = (clone $end)->endOfDay();

        if ($tipo === 'all' || $tipo === 'accesos') {
            $rows = AccessLog::query()
                ->selectRaw("DATE(access_time) as fecha, access_type, count(*) as total")
                ->whereBetween('access_time', [$windowStart, $windowEnd])
                ->groupBy('fecha', 'access_type')
                ->get();
            foreach ($days as $day) {
                foreach ($rows as $r) {
                    if ($r->fecha === $day->dateKey) {
                        if ($r->access_type === 'ENTRY') $day->a = (int)$r->total;
                        if ($r->access_type === 'EXIT')  $day->b = (int)$r->total;
                    }
                }
            }
            $default['title']   = 'Tendencia de Accesos';
            $default['yLabel']  = 'Accesos';
            $default['legendA'] = 'Entradas';
            $default['legendB'] = 'Salidas';
        } elseif ($tipo === 'alertas') {
            $rows = Alert::query()
                ->selectRaw("DATE(created_at) as fecha, severity, count(*) as total")
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->groupBy('fecha', 'severity')
                ->get();
            foreach ($days as $day) {
                foreach ($rows as $r) {
                    if ($r->fecha !== $day->dateKey) continue;
                    if (in_array($r->severity, ['CRITICAL', 'HIGH'], true)) $day->a += (int)$r->total;
                    else $day->b += (int)$r->total;
                }
            }
            $default['title']   = 'Tendencia de Alertas';
            $default['yLabel']  = 'Alertas';
            $default['legendA'] = 'Gravedad alta';
            $default['legendB'] = 'Gravedad media/baja';
            $default['colorA']  = '#f87171';
            $default['colorB']  = '#60a5fa';
        } elseif ($tipo === 'usuarios') {
            $rows = AccessLog::query()
                ->join('usuarios', 'access_logs.user_id', '=', 'usuarios.id')
                ->selectRaw("DATE(access_time) as fecha, usuarios.id_rol, count(distinct usuarios.id) as total")
                ->whereBetween('access_logs.access_time', [$windowStart, $windowEnd])
                ->groupBy('fecha', 'usuarios.id_rol')
                ->get();
            $estId = \App\Models\Role::idByName(\App\Models\Role::NAME_ESTUDIANTE);
            $docId = \App\Models\Role::idByName(\App\Models\Role::NAME_DOCENTE);
            foreach ($days as $day) {
                foreach ($rows as $r) {
                    if ($r->fecha !== $day->dateKey) continue;
                    if ((int)$r->id_rol === (int)$estId) $day->a += (int)$r->total;
                    elseif ((int)$r->id_rol === (int)$docId) $day->b += (int)$r->total;
                    else $day->a += (int)$r->total;
                }
            }
            $default['title']   = 'Tendencia de Usuarios Activos';
            $default['yLabel']  = 'Usuarios únicos';
            $default['legendA'] = 'Estudiantes';
            $default['legendB'] = 'Docentes';
            $default['colorA']  = '#22d3ee';
            $default['colorB']  = '#a78bfa';
        } elseif ($tipo === 'vehiculos') {
            $rows = AccessLog::query()
                ->selectRaw("DATE(access_time) as fecha, access_type, count(distinct vehicle_plate) as total")
                ->whereBetween('access_time', [$windowStart, $windowEnd])
                ->whereNotNull('vehicle_plate')
                ->where('vehicle_plate', '!=', '')
                ->groupBy('fecha', 'access_type')
                ->get();
            foreach ($days as $day) {
                foreach ($rows as $r) {
                    if ($r->fecha !== $day->dateKey) continue;
                    if ($r->access_type === 'ENTRY') $day->a += (int)$r->total;
                    else $day->b += (int)$r->total;
                }
            }
            $default['title']   = 'Tendencia de Vehículos';
            $default['yLabel']  = 'Vehículos únicos';
            $default['legendA'] = 'Entradas';
            $default['legendB'] = 'Salidas';
            $default['colorA']  = '#34d399';
            $default['colorB']  = '#f59e0b';
        }

        $maxA = (int)($days->max('a') ?? 0);
        $maxB = (int)($days->max('b') ?? 0);
        $default['max']    = max(1, $maxA, $maxB);
        $default['series'] = $days;

        return $default;
    }

    private function buildDonutGeneric(string $tipo, object $stats, object $ctx): array
    {
        if ($tipo === 'all' || $tipo === 'accesos') {
            $items = collect([
                ['key' => 'autorizados', 'label' => 'Autorizados', 'value' => $stats->autorizados, 'color' => '#22d3ee'],
                ['key' => 'denegados',   'label' => 'Denegados',   'value' => $stats->denegados,   'color' => '#f87171'],
                ['key' => 'visitantes',  'label' => 'Visitantes',  'value' => $stats->visitantes,  'color' => '#a78bfa'],
                ['key' => 'pendientes',  'label' => 'Pendientes',  'value' => $stats->pendientes,  'color' => '#fbbf24'],
            ]);
            return [
                'title'       => 'Distribución por Estado',
                'centerLabel' => 'Accesos',
                'centerValue' => $stats->total_accesos,
                'items'       => $items,
            ];
        }

        if ($tipo === 'alertas') {
            $severities = ['CRITICAL','HIGH','MEDIUM','LOW'];
            $labels = ['CRITICAL'=>'Críticas','HIGH'=>'Altas','MEDIUM'=>'Medias','LOW'=>'Bajas'];
            $colors = ['CRITICAL'=>'#f87171','HIGH'=>'#fbbf24','MEDIUM'=>'#60a5fa','LOW'=>'#9ca3af'];
            $agg = Alert::query()
                ->selectRaw('severity, count(*) as total')
                ->whereBetween('created_at', [
                    Carbon::now()->subDays(364)->startOfDay(),
                    Carbon::now()->endOfDay(),
                ])
                ->groupBy('severity')
                ->pluck('total', 'severity')
                ->all();
            $items = collect();
            foreach ($severities as $s) {
                $items->push([
                    'key'   => $s,
                    'label' => $labels[$s],
                    'value' => (int)($agg[$s] ?? 0),
                    'color' => $colors[$s],
                ]);
            }
            $total = (int)$items->sum('value');
            return [
                'title'       => 'Distribución por Severidad',
                'centerLabel' => 'Alertas',
                'centerValue' => $total,
                'items'       => $items,
            ];
        }

        if ($tipo === 'usuarios') {
            $roles = \App\Models\Role::query()
                ->select('id', 'nombre')
                ->get()
                ->keyBy('id');

            $rows = User::query()
                ->select('id_rol', DB::raw('count(*) as total'))
                ->groupBy('id_rol')
                ->pluck('total', 'id_rol')
                ->all();

            $colors = ['#22d3ee', '#a78bfa', '#34d399', '#f59e0b', '#f87171', '#60a5fa', '#fb7185'];
            $items = collect();
            $i = 0;
            foreach ($roles as $rid => $rol) {
                $items->push([
                    'key'   => 'rol_' . $rid,
                    'label' => $rol->nombre ?? ('Rol ' . $rid),
                    'value' => (int)($rows[$rid] ?? 0),
                    'color' => $colors[$i % count($colors)],
                ]);
                $i++;
            }
            return [
                'title'       => 'Distribución por Rol',
                'centerLabel' => 'Usuarios',
                'centerValue' => (int)User::count(),
                'items'       => $items,
            ];
        }

        if ($tipo === 'vehiculos') {
            $totalAutorizados = (int)AccessLog::query()
                ->whereBetween('access_time', [
                    Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay(),
                ])
                ->where('is_authorized', true)
                ->whereNotNull('vehicle_plate')
                ->where('vehicle_plate', '!=', '')
                ->count();

            $totalDenegados = (int)AccessLog::query()
                ->whereBetween('access_time', [
                    Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay(),
                ])
                ->where('is_authorized', false)
                ->whereNotNull('vehicle_plate')
                ->where('vehicle_plate', '!=', '')
                ->count();

            $conPlaca = DB::table('vehicles')->count();
            $items = collect([
                ['key' => 'autorizados', 'label' => 'Accesos Autorizados (30d)', 'value' => $totalAutorizados, 'color' => '#22d3ee'],
                ['key' => 'denegados',   'label' => 'Accesos Denegados (30d)',   'value' => $totalDenegados,   'color' => '#f87171'],
                ['key' => 'registrados', 'label' => 'Vehículos registrados',     'value' => $conPlaca,          'color' => '#a78bfa'],
            ]);
            return [
                'title'       => 'Resumen de Vehículos',
                'centerLabel' => 'Detectados',
                'centerValue' => (int)$ctx->vehiculos_periodo,
                'items'       => $items,
            ];
        }

        return [
            'title'       => 'Distribución',
            'centerLabel' => 'Total',
            'centerValue' => 0,
            'items'       => collect(),
        ];
    }

    private function buildHourlyGeneric($startDate, $endDate, string $tipo): array
    {
        $values = array_fill(0, 24, 0);

        $fill = function ($rawRows, $colIndex = 'h', $count = 'total') use (&$values) {
            foreach ($rawRows as $h => $c) {
                $idx = (int)$h;
                if ($idx >= 0 && $idx < 24) $values[$idx] += (int)$c;
            }
        };

        if ($tipo === 'all' || $tipo === 'accesos' || $tipo === 'vehiculos') {
            $selectColumn = ($tipo === 'vehiculos')
                ? 'count(distinct vehicle_plate) as total'
                : 'count(*) as total';
            $rows = AccessLog::query()
                ->selectRaw("EXTRACT(HOUR FROM access_time) as h, " . $selectColumn)
                ->whereBetween('access_time', [$startDate, $endDate])
                ->when($tipo === 'vehiculos', function($q){
                    $q->whereNotNull('vehicle_plate')->where('vehicle_plate', '!=', '');
                })
                ->groupBy('h')
                ->pluck('total', 'h')
                ->all();
            $fill($rows);
        } elseif ($tipo === 'alertas') {
            $rows = Alert::query()
                ->selectRaw("EXTRACT(HOUR FROM created_at) as h, count(*) as total")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('h')
                ->pluck('total', 'h')
                ->all();
            $fill($rows);
        } elseif ($tipo === 'usuarios') {
            $rows = AccessLog::query()
                ->join('usuarios', 'access_logs.user_id', '=', 'usuarios.id')
                ->selectRaw("EXTRACT(HOUR FROM access_time) as h, count(distinct usuarios.id) as total")
                ->whereBetween('access_logs.access_time', [$startDate, $endDate])
                ->groupBy('h')
                ->pluck('total', 'h')
                ->all();
            $fill($rows);
        }

        $labels = [
            'all'       => 'Actividad por Hora (accesos)',
            'accesos'   => 'Actividad por Hora del Día',
            'alertas'   => 'Alertas por Hora',
            'usuarios'  => 'Usuarios Activos por Hora',
            'vehiculos' => 'Vehículos Detectados por Hora',
        ];
        $yLabels = [
            'all'       => 'Accesos',
            'accesos'   => 'Accesos',
            'alertas'   => 'Alertas',
            'usuarios'  => 'Usuarios únicos',
            'vehiculos' => 'Vehículos únicos',
        ];

        return [
            'title'  => $labels[$tipo] ?? 'Actividad por Hora',
            'yLabel' => $yLabels[$tipo] ?? 'Registros',
            'values' => $values,
            'max'    => max(1, (int)max($values)),
        ];
    }

    private function parsePeriod(Request $request): array
    {
        $period = $request->get('period', 'week');
        $end = Carbon::now()->endOfDay();
        $start = Carbon::now()->startOfDay();
        $label = 'Última Semana';

        switch ($period) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $label = 'Hoy';
                break;
            case 'week':
                $start = Carbon::today()->subDays(6)->startOfDay();
                $label = 'Última Semana';
                break;
            case 'month':
                $start = Carbon::today()->subDays(29)->startOfDay();
                $label = 'Último Mes';
                break;
            case 'quarter':
                $start = Carbon::today()->subDays(89)->startOfDay();
                $label = 'Último Trimestre';
                break;
            case 'year':
                $start = Carbon::today()->subDays(364)->startOfDay();
                $label = 'Último Año';
                break;
            case 'custom':
                $sd = $request->get('start_date');
                $ed = $request->get('end_date');
                if ($sd) $start = Carbon::parse($sd)->startOfDay();
                if ($ed) $end   = Carbon::parse($ed)->endOfDay();
                $label = "Personalizado ({$start->format('d/m')} - {$end->format('d/m')})";
                break;
        }
        return [$start, $end, $label];
    }

    private function buildTrend7Days($startDate, $endDate, string $tipoReporte): \Illuminate\Support\Collection
    {
        $days = collect();
        $end = Carbon::parse($endDate)->startOfDay();
        for ($i = 6; $i >= 0; $i--) {
            $d = (clone $end)->subDays($i);
            $days->push((object)[
                'label'    => $d->format('d/m'),
                'dateKey'  => $d->format('Y-m-d'),
                'entradas' => 0,
                'salidas'  => 0,
            ]);
        }

        if ($tipoReporte !== 'all' && $tipoReporte !== 'accesos') {
            return $days;
        }

        $rows = AccessLog::query()
            ->selectRaw("DATE(access_time) as fecha, access_type, count(*) as total")
            ->whereBetween('access_time', [(clone $end)->subDays(6)->startOfDay(), (clone $end)->endOfDay()])
            ->groupBy('fecha', 'access_type')
            ->get();

        foreach ($days as $day) {
            foreach ($rows as $r) {
                if ($r->fecha === $day->dateKey) {
                    if ($r->access_type === 'ENTRY') $day->entradas = (int)$r->total;
                    if ($r->access_type === 'EXIT')  $day->salidas  = (int)$r->total;
                }
            }
        }
        return $days;
    }

    private function buildDistribution(object $stats): \Illuminate\Support\Collection
    {
        return collect([
            ['key' => 'autorizados', 'label' => 'Autorizados', 'value' => $stats->autorizados, 'color' => '#22d3ee'],
            ['key' => 'denegados',   'label' => 'Denegados',   'value' => $stats->denegados,   'color' => '#f87171'],
            ['key' => 'visitantes',  'label' => 'Visitantes',  'value' => $stats->visitantes,  'color' => '#a78bfa'],
            ['key' => 'pendientes',  'label' => 'Pendientes',  'value' => $stats->pendientes,  'color' => '#fbbf24'],
        ]);
    }

    private function buildHourlyActivity($startDate, $endDate, string $tipoReporte): array
    {
        $hours = array_fill(0, 24, 0);
        if ($tipoReporte !== 'all' && $tipoReporte !== 'accesos') {
            return $hours;
        }

        $rows = AccessLog::query()
            ->selectRaw("EXTRACT(HOUR FROM access_time) as h, count(*) as total")
            ->whereBetween('access_time', [$startDate, $endDate])
            ->groupBy('h')
            ->pluck('total', 'h')
            ->all();

        foreach ($rows as $h => $c) {
            $idx = (int)$h;
            if ($idx >= 0 && $idx < 24) $hours[$idx] = (int)$c;
        }
        return $hours;
    }

    private function buildTopUsers($startDate, $endDate, string $tipoReporte): \Illuminate\Support\Collection
    {
        if (!in_array($tipoReporte, ['all', 'accesos', 'usuarios'], true)) {
            return collect([]);
        }

        $rows = AccessLog::query()
            ->join('usuarios', 'access_logs.user_id', '=', 'usuarios.id')
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->whereBetween('access_logs.access_time', [$startDate, $endDate])
            ->select(
                'usuarios.id',
                'personas.nombre',
                'personas.apellidos',
                'personas.foto',
                DB::raw('count(access_logs.id) as total_accesos'),
                DB::raw('max(access_logs.access_time) as ultimo_acceso')
            )
            ->groupBy('usuarios.id', 'personas.nombre', 'personas.apellidos', 'personas.foto')
            ->orderByDesc('total_accesos')
            ->limit(5)
            ->get();

        return $rows->map(fn($r) => (object)[
            'id'              => $r->id,
            'nombre_completo' => trim(($r->nombre ?? '') . ' ' . ($r->apellidos ?? '')),
            'iniciales'       => strtoupper(substr((string)($r->nombre ?? '?'), 0, 1) . substr((string)($r->apellidos ?? '?'), 0, 1)),
            'foto'            => $r->foto ?? null,
            'accesos'         => (int)$r->total_accesos,
            'ultimo'          => $r->ultimo_acceso ? Carbon::parse($r->ultimo_acceso)->diffForHumans() : 'Nunca',
        ]);
    }

    private function buildAlertSummary($startDate, $endDate, string $tipoReporte): \Illuminate\Support\Collection
    {
        $empty = collect([
            (object)['key'=>'CRITICAL','label'=>'Críticas','color'=>'#f87171','total'=>0,'resueltas'=>0,'pendientes'=>0],
            (object)['key'=>'HIGH',    'label'=>'Altas',   'color'=>'#fbbf24','total'=>0,'resueltas'=>0,'pendientes'=>0],
            (object)['key'=>'MEDIUM',  'label'=>'Medias',  'color'=>'#60a5fa','total'=>0,'resueltas'=>0,'pendientes'=>0],
            (object)['key'=>'LOW',     'label'=>'Bajas',   'color'=>'#9ca3af','total'=>0,'resueltas'=>0,'pendientes'=>0],
            (object)['key'=>'TOTAL',   'label'=>'Total',   'color'=>'#22d3ee','total'=>0,'resueltas'=>0,'pendientes'=>0],
        ]);

        if (!in_array($tipoReporte, ['all', 'alertas'], true)) {
            return $empty;
        }

        $severities = ['CRITICAL','HIGH','MEDIUM','LOW'];
        $resolvedId = Alert::STATUS_RESOLVED;

        $agg = Alert::query()
            ->selectRaw(
                'severity, ' .
                'count(*) as total, ' .
                "sum(case when status = ? then 1 else 0 end) as resueltas",
                [$resolvedId]
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('severity')
            ->get()
            ->keyBy('severity');

        $colors = ['CRITICAL'=>'#f87171','HIGH'=>'#fbbf24','MEDIUM'=>'#60a5fa','LOW'=>'#9ca3af'];
        $labels = ['CRITICAL'=>'Críticas','HIGH'=>'Altas','MEDIUM'=>'Medias','LOW'=>'Bajas'];
        $out = collect();
        $tot = $resTot = $penTot = 0;
        foreach ($severities as $s) {
            $row = $agg->get($s);
            $t = $row ? (int)$row->total : 0;
            $r = $row ? (int)$row->resueltas : 0;
            $p = max(0, $t - $r);
            $tot += $t; $resTot += $r; $penTot += $p;
            $out->push((object)[
                'key' => $s, 'label' => $labels[$s], 'color' => $colors[$s],
                'total' => $t, 'resueltas' => $r, 'pendientes' => $p,
            ]);
        }
        $out->push((object)[
            'key' => 'TOTAL', 'label' => 'Total', 'color' => '#22d3ee',
            'total' => $tot, 'resueltas' => $resTot, 'pendientes' => $penTot,
        ]);
        return $out;
    }

    private function buildDashboardData(): array
    {
        $hoy = Carbon::today();

        $ultimos_accesos = AccessLog::with('user')
            ->orderByDesc('access_time')
            ->take(5)
            ->get();

        $ultimas_alertas = Alert::orderByDesc('created_at')
            ->take(3)
            ->get();

        $actividadAccesos = $ultimos_accesos->map(function ($acceso) {
            return [
                'tipo' => 'access',
                'titulo' => $acceso->is_authorized ? 'Acceso autorizado' : 'Acceso denegado',
                'descripcion' => sprintf(
                    'Vehículo %s con operación %s',
                    $acceso->vehicle_plate ?: 'sin placa',
                    $acceso->access_type ?: 'N/A'
                ),
                'nivel' => $acceso->is_authorized ? 'success' : 'warning',
                'created_at' => optional($acceso->access_time)->toIso8601String(),
                'time_human' => optional($acceso->access_time)?->diffForHumans(),
            ];
        });

        $actividadAlertas = $ultimas_alertas->map(function ($alerta) {
            $nivel = match (strtoupper((string) $alerta->severity)) {
                'CRITICAL' => 'danger',
                'HIGH' => 'warning',
                default => 'info',
            };

            return [
                'tipo' => 'alert',
                'titulo' => $alerta->title,
                'descripcion' => $alerta->description,
                'nivel' => $nivel,
                'created_at' => optional($alerta->created_at)->toIso8601String(),
                'time_human' => optional($alerta->created_at)?->diffForHumans(),
            ];
        });

        $actividad_reciente = $actividadAccesos->toBase()
            ->merge($actividadAlertas->toBase())
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        return [
            'accesos_hoy' => AccessLog::whereDate('access_time', $hoy)->count(),
            'vehiculos_registrados' => Vehicle::count(),
            'alertas_activas' => Alert::where('is_resolved', false)->count(),
            'ultimos_accesos' => $ultimos_accesos,
            'ultimas_alertas' => $ultimas_alertas,
            'actividad_reciente' => $actividad_reciente,
        ];
    }

    private function buildReportDataForExport(Request $request): array
    {
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
        $tipoReporte = $request->get('tipo', 'all');

        $accesosBaseAgg = $this->buildAccessBaseQuery($startDate, $endDate, false);
        $includeAccesos = in_array($tipoReporte, ['all', 'accesos'], true);

        $stats = (object)[
            'total_accesos'   => 0,
            'entradas'        => 0,
            'salidas'         => 0,
            'autorizados'     => 0,
            'denegados'       => 0,
            'visitantes'      => 0,
            'total_usuarios'  => User::count(),
            'total_vehiculos' => DB::table('vehicles')->count(),
            'total_alertas'   => Alert::count(),
            'alertas_activas' => Alert::where('is_resolved', false)->count(),
        ];

        if ($includeAccesos) {
            $stats->total_accesos = (clone $accesosBaseAgg)->count();

            if ($stats->total_accesos > 0) {
                $entryExit = (clone $accesosBaseAgg)
                    ->selectRaw('access_logs.access_type, count(*) as total')
                    ->groupBy('access_logs.access_type')
                    ->pluck('total', 'access_type')
                    ->all();
                $stats->entradas = (int)($entryExit['ENTRY'] ?? 0);
                $stats->salidas  = (int)($entryExit['EXIT']  ?? 0);

                $authStats = (clone $accesosBaseAgg)
                    ->selectRaw('access_logs.is_authorized, count(*) as total')
                    ->groupBy('access_logs.is_authorized')
                    ->pluck('total', 'is_authorized')
                    ->all();
                $stats->autorizados = (int)($authStats[true] ?? $authStats[1] ?? 0);
                $stats->denegados   = (int)($authStats[false] ?? $authStats[0] ?? 0);

                $visitId = \App\Models\Role::idByName(\App\Models\Role::NAME_VISITANTE);
                if ($visitId !== null) {
                    $stats->visitantes = (int)(clone $accesosBaseAgg)
                        ->where('usuarios.id_rol', $visitId)
                        ->count();
                }
            }
        }

        $accesosFull = collect([]);
        if ($includeAccesos) {
            $accesosFull = $this->buildAccessBaseQuery($startDate, $endDate, true)
                ->orderByDesc('access_logs.access_time')
                ->limit(100)
                ->get()
                ->map(function ($a) {
                    $nombre = trim(($a->persona_nombre ?? '') . ' ' . ($a->persona_apellidos ?? ''));
                    if (!$nombre) {
                        $nombre = $a->user_id ? ('Usuario #' . $a->user_id) : 'Desconocido';
                    }
                    return (object)[
                        'id'       => $a->id,
                        'usuario'  => $nombre,
                        'vehiculo' => $a->vehicle_plate ?: 'Sin vehículo',
                        'tipo'     => strtoupper((string)$a->access_type) === 'ENTRY' ? 'Entrada' : 'Salida',
                        'fecha'    => optional($a->access_time)->format('d/m/Y H:i:s'),
                        'estado'   => $a->is_authorized ? 'Autorizado' : 'Denegado',
                    ];
                });
        }

        $topUsuarios = $this->buildTopUsers($startDate, $endDate, $tipoReporte)
            ->map(fn($u) => (object)[
                'nombre'  => $u->nombre_completo ?: 'Usuario #' . $u->id,
                'accesos' => $u->accesos,
                'ultimo'  => $u->ultimo,
            ]);

        $summary = $this->buildAlertSummary($startDate, $endDate, $tipoReporte);
        $resumenAlertas = [];
        foreach ($summary as $row) {
            if ($row->key === 'TOTAL') continue;
            $resumenAlertas[$row->label] = [
                'total'      => $row->total,
                'resueltas'  => $row->resueltas,
                'pendientes' => $row->pendientes,
            ];
        }

        $totalAccesosPeriodo = $stats->entradas + $stats->salidas;
        $tasaIa = $totalAccesosPeriodo > 0
            ? round((($stats->autorizados + $stats->visitantes) / max(1, $totalAccesosPeriodo)) * 100, 1) . '%'
            : 'N/A';

        return [
            'fecha_generacion'  => now()->format('d/m/Y H:i:s'),
            'periodo_label'     => $periodLabel,
            'fecha_inicio'      => $startDate->format('d/m/Y'),
            'fecha_fin'         => $endDate->format('d/m/Y'),
            'tipo_reporte'      => $tipoReporte,
            'total_accesos_hoy' => $stats->total_accesos,
            'total_accesos'     => $stats->total_accesos,
            'entradas'          => $stats->entradas,
            'salidas'           => $stats->salidas,
            'autorizados'       => $stats->autorizados,
            'denegados'         => $stats->denegados,
            'visitantes'        => $stats->visitantes,
            'total_vehiculos'   => $stats->total_vehiculos,
            'total_usuarios'    => $stats->total_usuarios,
            'tasa_ia'           => $tasaIa,
            'alertas_activas'   => $stats->alertas_activas,
            'total_alertas'     => $stats->total_alertas,
            'ultimos_accesos'   => $accesosFull,
            'usuarios_activos'  => $topUsuarios,
            'resumen_alertas'   => $resumenAlertas,
        ];
    }

    public function exportarPdf(Request $request)
    {
        try {
            $includeCharts  = $request->get('charts', '1') === '1';
            $includeTables  = $request->get('tables', '1') === '1';
            $includeSummary = $request->get('summary', '1') === '1';

            $data = $this->buildReportDataForExport($request);
            $metrics = array_merge($data, [
                'include_charts'  => $includeCharts,
                'include_tables'  => $includeTables,
                'include_summary' => $includeSummary,
            ]);

            $pdf = Pdf::loadView('pdf.reporte', $metrics);
            $pdf->setPaper('A4', 'portrait');
            $filename = 'Reporte_OKOVISION_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    public function exportarCsv(Request $request)
    {
        try {
            $includeTables = $request->get('tables', '1') === '1';
            if (!$includeTables) {
                return redirect()->back()->with('warning', 'La exportación CSV requiere incluir tablas detalladas.');
            }

            [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
            $tipoReporte = $request->get('tipo', 'all');
            $includeAccesos = in_array($tipoReporte, ['all', 'accesos'], true);

            $filename = "Reporte_Accesos_" . now()->format('Y-m-d_H-i-s') . ".csv";
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0",
            ];

            $callback = function() use ($startDate, $endDate, $includeAccesos, $tipoReporte) {
                $file = fopen('php://output', 'w');
                fwrite($file, "\xEF\xBB\xBF");

                fputcsv($file, ['PERIODO SELECCIONADO', $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')]);
                fputcsv($file, ['TIPO DE REPORTE', $tipoReporte]);
                fputcsv($file, []);

                if ($includeAccesos) {
                    fputcsv($file, ['=== ACCESOS ===']);
                    fputcsv($file, ['ID', 'Usuario', 'Vehículo', 'Tipo', 'Fecha/Hora', 'Estado']);

                    $rows = $this->buildAccessBaseQuery($startDate, $endDate, true)
                        ->orderByDesc('access_logs.access_time')
                        ->limit(500)
                        ->get();

                    foreach ($rows as $a) {
                        $nombre = trim(($a->persona_nombre ?? '') . ' ' . ($a->persona_apellidos ?? ''));
                        if (!$nombre) $nombre = $a->user_id ? ('Usuario #' . $a->user_id) : 'Desconocido';
                        fputcsv($file, [
                            $a->id,
                            $nombre,
                            $a->vehicle_plate ?: 'Sin vehículo',
                            strtoupper((string)$a->access_type) === 'ENTRY' ? 'Entrada' : 'Salida',
                            optional($a->access_time)->format('d/m/Y H:i:s'),
                            $a->is_authorized ? 'Autorizado' : 'Denegado',
                        ]);
                    }
                    fputcsv($file, []);
                }

                if (in_array($tipoReporte, ['all', 'usuarios'], true)) {
                    fputcsv($file, ['=== USUARIOS MÁS ACTIVOS ===']);
                    fputcsv($file, ['Usuario', 'Total Accesos', 'Última Actividad']);
                    $top = $this->buildTopUsers($startDate, $endDate, $tipoReporte);
                    foreach ($top as $u) {
                        fputcsv($file, [$u->nombre_completo, $u->accesos, $u->ultimo]);
                    }
                    fputcsv($file, []);
                }

                if (in_array($tipoReporte, ['all', 'alertas'], true)) {
                    fputcsv($file, ['=== RESUMEN DE ALERTAS ===']);
                    fputcsv($file, ['Severidad', 'Total', 'Resueltas', 'Pendientes']);
                    $als = $this->buildAlertSummary($startDate, $endDate, $tipoReporte);
                    foreach ($als as $row) {
                        fputcsv($file, [$row->label, $row->total, $row->resueltas, $row->pendientes]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Error al generar CSV: ' . $e->getMessage());
        }
    }

    public function exportarAlertasExcel(Request $request)
    {
        try {
            $filters = $request->only(['severity', 'date_range', 'status', 'search']);

            $query = Alert::query()->applyFilters($filters)->latest('created_at');

            $counts = (clone $query)
                ->reorder()
                ->selectRaw('alerts.severity, count(*) as total')
                ->groupBy('alerts.severity')
                ->pluck('total', 'alerts.severity')
                ->all();

            $statusCounts = (clone $query)
                ->reorder()
                ->selectRaw('alerts.status, count(*) as total')
                ->groupBy('alerts.status')
                ->pluck('total', 'alerts.status')
                ->all();

            $total    = (clone $query)->count();
            $criticas = (int)($counts['CRITICAL'] ?? 0);
            $altas    = (int)($counts['HIGH']     ?? 0);
            $medias   = (int)($counts['MEDIUM']   ?? 0);
            $bajas    = (int)($counts['LOW']      ?? 0);

            $pendientes = (int)(($statusCounts['PENDING']  ?? 0) + ($statusCounts['REVIEW']   ?? 0));
            $resueltas   = (int)(($statusCounts['RESOLVED'] ?? 0) + ($statusCounts['VALIDATED'] ?? 0));
            $reportadas  = (int)($statusCounts['REPORTED']  ?? 0);

            $rows = (clone $query)->limit(2000)->get();

            $filename = "Alertas_OKOVISION_" . now()->format('Y-m-d_H-i-s') . ".xls";
            $headers = [
                "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0",
            ];

            $periodLabel = match (strtolower((string)$request->get('date_range'))) {
                '24h'   => 'Últimas 24 horas',
                '7d'    => 'Últimos 7 días',
                '30d'   => 'Últimos 30 días',
                'all'   => 'Todas',
                default => 'Últimas 24 horas',
            };
            $sevLabel = match (strtoupper((string)$request->get('severity'))) {
                'CRITICAL' => 'Críticas',
                'HIGH'     => 'Altas',
                'MEDIUM'   => 'Medias',
                'LOW'      => 'Bajas',
                default    => 'Todas',
            };
            $stLabel = match (strtolower((string)$request->get('status'))) {
                'pending'   => 'Pendientes',
                'review'    => 'En revisión',
                'validated' => 'Validadas',
                'reported'  => 'Reportadas',
                'resolved'  => 'Resueltas',
                default     => 'Todos los estados',
            };

            ob_start();
            echo "\xEF\xBB\xBF";
            ?>
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="UTF-8">
                <!--[if gte mso 9]><xml>
                <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Alertas</x:Name>
                <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
                </xml><![endif]-->
                <style>
                    table { border-collapse: collapse; }
                    th { background-color: #0D1B35; color: #fff; padding: 8px; text-align: left; font-weight: bold; }
                    td { padding: 6px; border: 1px solid #ccc; }
                    .title { font-size: 18px; font-weight: bold; color: #0D1B35; }
                    .subtitle { color: #666; }
                </style>
            </head>
            <body>
                <p class="title">OKO VISION - Reporte de Alertas</p>
                <p class="subtitle">Generado: <?php echo now()->format('d/m/Y H:i:s'); ?></p>
                <p class="subtitle">Período: <?php echo e($periodLabel); ?> | Severidad: <?php echo e($sevLabel); ?> | Estado: <?php echo e($stLabel); ?></p>
                <?php if ($request->filled('search')): ?>
                <p class="subtitle">Búsqueda: "<?php echo e($request->get('search')); ?>"</p>
                <?php endif; ?>
                <br/>

                <h3>Resumen</h3>
                <table>
                    <tr><th>Métrica</th><th>Valor</th></tr>
                    <tr><td>Total Alertas</td><td><?php echo $total; ?></td></tr>
                    <tr><td>Críticas</td><td><?php echo $criticas; ?></td></tr>
                    <tr><td>Altas</td><td><?php echo $altas; ?></td></tr>
                    <tr><td>Medias</td><td><?php echo $medias; ?></td></tr>
                    <tr><td>Bajas</td><td><?php echo $bajas; ?></td></tr>
                    <tr><td>Pendientes/Revisión</td><td><?php echo $pendientes; ?></td></tr>
                    <tr><td>Resueltas/Validadas</td><td><?php echo $resueltas; ?></td></tr>
                    <tr><td>Reportadas</td><td><?php echo $reportadas; ?></td></tr>
                </table>
                <br/>

                <h3>Detalle de Alertas (<?php echo $total; ?> registros)</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Folio</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Severidad</th>
                        <th>Estado</th>
                        <th>Ubicación</th>
                        <th>Cámara</th>
                        <th>Placa</th>
                        <th>Propietario</th>
                        <th>Agente</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Resolución</th>
                        <th>Notas</th>
                    </tr>
                    <?php foreach ($rows as $a): ?>
                    <tr>
                        <td><?php echo e($a->id); ?></td>
                        <td><?php echo e($a->formatted_id); ?></td>
                        <td><?php echo e($a->title); ?></td>
                        <td><?php echo e($a->description); ?></td>
                        <td><?php echo e($a->severity_label); ?></td>
                        <td><?php echo e($a->status_label); ?></td>
                        <td><?php echo e($a->location ?? ''); ?></td>
                        <td><?php echo e($a->camera ?? ''); ?></td>
                        <td><?php echo e($a->vehicle_plate ?? ''); ?></td>
                        <td><?php echo e($a->vehicle_owner ?? ''); ?></td>
                        <td><?php echo e($a->agent_type === 'user' ? ('Usuario #' . $a->agent_id) : ($a->agent_type ?: 'Sistema')); ?></td>
                        <td><?php echo e(optional($a->created_at)->format('d/m/Y H:i:s')); ?></td>
                        <td><?php echo e(optional($a->resolved_at)?->format('d/m/Y H:i:s')); ?></td>
                        <td><?php echo e($a->notes ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </body>
            </html>
            <?php
            $content = ob_get_clean();

            return response()->stream(function () use ($content) {
                echo $content;
            }, 200, $headers);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Error al exportar alertas: ' . $e->getMessage());
        }
    }

    public function exportarExcel(Request $request)
    {
        try {
            $includeTables  = $request->get('tables', '1') === '1';
            $includeSummary = $request->get('summary', '1') === '1';

            [$startDate, $endDate, $periodLabel] = $this->parsePeriod($request);
            $tipoReporte = $request->get('tipo', 'all');
            $includeAccesos = in_array($tipoReporte, ['all', 'accesos'], true);

            $filename = "Reporte_OKOVISION_" . now()->format('Y-m-d_H-i-s') . ".xls";
            $headers = [
                "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0",
            ];

            ob_start();
            echo "\xEF\xBB\xBF";
            ?>
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="UTF-8">
                <!--[if gte mso 9]><xml>
                <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Reporte</x:Name>
                <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
                </xml><![endif]-->
                <style>
                    table { border-collapse: collapse; }
                    th { background-color: #0D1B35; color: #fff; padding: 8px; text-align: left; font-weight: bold; }
                    td { padding: 6px; border: 1px solid #ccc; }
                    .title { font-size: 18px; font-weight: bold; color: #0D1B35; }
                    .subtitle { color: #666; }
                </style>
            </head>
            <body>
                <p class="title">OKO VISION - Reporte General</p>
                <p class="subtitle">Generado: <?php echo now()->format('d/m/Y H:i:s'); ?></p>
                <p class="subtitle">Período: <?php echo $periodLabel; ?> (<?php echo $startDate->format('d/m/Y'); ?> a <?php echo $endDate->format('d/m/Y'); ?>)</p>
                <p class="subtitle">Tipo: <?php echo $tipoReporte; ?></p>
                <br/>

                <?php if ($includeSummary): ?>
                <h3>Resumen del Período</h3>
                <?php
                $data = $this->buildReportDataForExport($request);
                ?>
                <table>
                    <tr><th>Métrica</th><th>Valor</th></tr>
                    <tr><td>Total Accesos</td><td><?php echo $data['total_accesos']; ?></td></tr>
                    <tr><td>Entradas</td><td><?php echo $data['entradas']; ?></td></tr>
                    <tr><td>Salidas</td><td><?php echo $data['salidas']; ?></td></tr>
                    <tr><td>Autorizados</td><td><?php echo $data['autorizados']; ?></td></tr>
                    <tr><td>Denegados</td><td><?php echo $data['denegados']; ?></td></tr>
                    <tr><td>Visitantes</td><td><?php echo $data['visitantes']; ?></td></tr>
                    <tr><td>Total Usuarios</td><td><?php echo $data['total_usuarios']; ?></td></tr>
                    <tr><td>Total Vehículos</td><td><?php echo $data['total_vehiculos']; ?></td></tr>
                    <tr><td>Total Alertas</td><td><?php echo $data['total_alertas']; ?></td></tr>
                    <tr><td>Alertas Activas</td><td><?php echo $data['alertas_activas']; ?></td></tr>
                </table>
                <br/>
                <?php endif; ?>

                <?php if ($includeTables && $includeAccesos): ?>
                <h3>Accesos (primeros 500)</h3>
                <table>
                    <tr>
                        <th>ID</th><th>Usuario</th><th>Vehículo</th><th>Tipo</th><th>Fecha/Hora</th><th>Estado</th>
                    </tr>
                    <?php
                    $rows = $this->buildAccessBaseQuery($startDate, $endDate, true)
                        ->orderByDesc('access_logs.access_time')
                        ->limit(500)
                        ->get();
                    foreach ($rows as $a) {
                        $nombre = trim(($a->persona_nombre ?? '') . ' ' . ($a->persona_apellidos ?? ''));
                        if (!$nombre) $nombre = $a->user_id ? ('Usuario #' . $a->user_id) : 'Desconocido';
                        echo '<tr>';
                        echo '<td>' . e($a->id) . '</td>';
                        echo '<td>' . e($nombre) . '</td>';
                        echo '<td>' . e($a->vehicle_plate ?: 'Sin vehículo') . '</td>';
                        echo '<td>' . (strtoupper((string)$a->access_type) === 'ENTRY' ? 'Entrada' : 'Salida') . '</td>';
                        echo '<td>' . e(optional($a->access_time)->format('d/m/Y H:i:s')) . '</td>';
                        echo '<td>' . ($a->is_authorized ? 'Autorizado' : 'Denegado') . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
                <br/>
                <?php endif; ?>

                <?php if ($includeTables && in_array($tipoReporte, ['all', 'usuarios'], true)): ?>
                <h3>Usuarios Más Activos</h3>
                <table>
                    <tr><th>Usuario</th><th>Total Accesos</th><th>Última Actividad</th></tr>
                    <?php
                    $top = $this->buildTopUsers($startDate, $endDate, $tipoReporte);
                    foreach ($top as $u) {
                        echo '<tr>';
                        echo '<td>' . e($u->nombre_completo) . '</td>';
                        echo '<td>' . $u->accesos . '</td>';
                        echo '<td>' . e($u->ultimo) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
                <br/>
                <?php endif; ?>

                <?php if ($includeSummary && in_array($tipoReporte, ['all', 'alertas'], true)): ?>
                <h3>Resumen de Alertas</h3>
                <table>
                    <tr><th>Severidad</th><th>Total</th><th>Resueltas</th><th>Pendientes</th></tr>
                    <?php
                    $als = $this->buildAlertSummary($startDate, $endDate, $tipoReporte);
                    foreach ($als as $row) {
                        echo '<tr>';
                        echo '<td><strong>' . e($row->label) . '</strong></td>';
                        echo '<td>' . $row->total . '</td>';
                        echo '<td>' . $row->resueltas . '</td>';
                        echo '<td>' . $row->pendientes . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
                <?php endif; ?>
            </body>
            </html>
            <?php
            $content = ob_get_clean();

            return response($content, 200, $headers);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Error al generar Excel: ' . $e->getMessage());
        }
    }
}
