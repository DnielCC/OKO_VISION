<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General OKO VISION</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 0; font-size: 12px; }
        .header { background-color: #0D1B35; color: #fff; padding: 18px 20px; text-align: center; }
        .header h1 { margin: 0; color: #00F2FF; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 11px; color: #A0AEC0; }
        .content { padding: 25px 30px; }
        .section-title { border-bottom: 2px solid #00F2FF; color: #0D1B35; padding-bottom: 4px; margin-top: 24px; margin-bottom: 12px; font-size: 16px; }

        .meta { background: #F7FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 4px; margin-bottom: 8px; font-size: 11px; }
        .meta-row { margin: 2px 0; }
        .meta-label { color: #718096; display: inline-block; width: 130px; font-weight: 600; }
        .meta-value { color: #2D3748; }

        .metric-row { width: 100%; margin: 0 -5px; }
        .metric-box { box-sizing: border-box; border: 1px solid #E2E8F0; padding: 10px 8px; margin: 5px; border-radius: 4px; text-align: center; background-color: #F7FAFC; width: calc(25% - 10px); display: inline-block; vertical-align: top; }
        .metric-value { font-size: 20px; font-weight: bold; color: #0D1B35; margin: 4px 0; }
        .metric-label { font-size: 10px; color: #718096; text-transform: uppercase; letter-spacing: 0.3px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 11px; }
        th { background-color: #0D1B35; color: #fff; text-align: left; padding: 8px; }
        td { padding: 7px 8px; border-bottom: 1px solid #E2E8F0; }
        tr:nth-child(even) { background-color: #F7FAFC; }

        .badge { padding: 3px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #fff; }
        .badge-green { background-color: #48BB78; }
        .badge-red { background-color: #F56565; }
        .badge-cyan { background-color: #0BC5EA; color: #fff; }
        .badge-yellow { background-color: #ECC94B; color: #744210; }
        .badge-gray { background-color: #A0AEC0; color: #fff; }

        .placeholder-box { background-color: #F7FAFC; border: 1px dashed #A0AEC0; padding: 30px; text-align: center; color: #718096; border-radius: 8px; font-size: 12px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #718096; padding: 8px 0; border-top: 1px solid #E2E8F0; background: #fff; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <h1>OKO VISION</h1>
        <p>Sistema Inteligente de Control de Acceso</p>
        <p>Reporte General de Métricas y Accesos</p>
    </div>

    <div class="content">
        <div class="meta">
            <div class="meta-row"><span class="meta-label">Fecha Generación:</span><span class="meta-value">{{ $fecha_generacion ?? now()->format('d/m/Y H:i:s') }}</span></div>
            <div class="meta-row"><span class="meta-label">Período:</span><span class="meta-value">{{ $periodo_label ?? '—' }} ({{ $fecha_inicio ?? '' }} al {{ $fecha_fin ?? '' }})</span></div>
            <div class="meta-row"><span class="meta-label">Tipo Reporte:</span><span class="meta-value">{{ strtoupper($tipo_reporte ?? 'all') }}</span></div>
        </div>

        @if(!empty($include_summary))
        <h2 class="section-title">Resumen del Período</h2>
        <div class="metric-row">
            <div class="metric-box">
                <div class="metric-label">Accesos Totales</div>
                <div class="metric-value">{{ $total_accesos_hoy ?? $total_accesos ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Entradas</div>
                <div class="metric-value" style="color:#38B2AC;">{{ $entradas ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Salidas</div>
                <div class="metric-value" style="color:#3182CE;">{{ $salidas ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Tasa IA</div>
                <div class="metric-value" style="color:#48BB78;">{{ $tasa_ia ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="metric-row">
            <div class="metric-box">
                <div class="metric-label">Autorizados</div>
                <div class="metric-value" style="color:#48BB78;">{{ $autorizados ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Denegados</div>
                <div class="metric-value" style="color:#F56565;">{{ $denegados ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Vehículos</div>
                <div class="metric-value">{{ $total_vehiculos ?? 0 }}</div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Alertas Activas</div>
                <div class="metric-value" style="color:#F56565;">{{ $alertas_activas ?? 0 }}</div>
            </div>
        </div>
        <div class="metric-row">
            <div class="metric-box" style="width:calc(33.33% - 10px);">
                <div class="metric-label">Visitantes</div>
                <div class="metric-value" style="color:#9F7AEA;">{{ $visitantes ?? 0 }}</div>
            </div>
            <div class="metric-box" style="width:calc(33.33% - 10px);">
                <div class="metric-label">Total Usuarios</div>
                <div class="metric-value">{{ $total_usuarios ?? 0 }}</div>
            </div>
            <div class="metric-box" style="width:calc(33.33% - 10px);">
                <div class="metric-label">Total Alertas</div>
                <div class="metric-value">{{ $total_alertas ?? 0 }}</div>
            </div>
        </div>
        @endif

        @if(!empty($include_charts))
        <h2 class="section-title">Análisis Visual (Referencia)</h2>
        <div class="placeholder-box">
            <p style="font-size: 15px; font-weight: bold; margin: 0 0 6px 0;">📊 Gráficos de Tendencia y Distribución</p>
            <p style="font-size: 11px; margin: 0;">Consulte el panel web para visualizaciones interactivas de:
                <strong>tendencia 7 días</strong>, <strong>distribución donut</strong> y <strong>actividad por hora</strong>.
            </p>
            <p style="margin-top: 10px; font-size: 10px; color:#A0AEC0;">
                Entradas: {{ $entradas ?? 0 }} · Salidas: {{ $salidas ?? 0 }} · Autorizados: {{ $autorizados ?? 0 }} · Denegados: {{ $denegados ?? 0 }}
            </p>
        </div>
        @endif

        @if(!empty($include_tables))
        <h2 class="section-title">Últimos Accesos ({{ count($ultimos_accesos ?? []) }} registros)</h2>
        @if(empty($ultimos_accesos) || count($ultimos_accesos) === 0)
            <p style="color:#718096; text-align:center; padding: 16px;">No hay registros de accesos en el período seleccionado.</p>
        @else
        <table>
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>Usuario</th>
                    <th>Vehículo</th>
                    <th style="width:60px;">Tipo</th>
                    <th style="width:130px;">Fecha/Hora</th>
                    <th style="width:70px;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimos_accesos as $acceso)
                <tr>
                    <td>#{{ $acceso->id ?? '—' }}</td>
                    <td>{{ $acceso->usuario ?? ($acceso->nombre ?? 'Desconocido') }}</td>
                    <td>{{ $acceso->vehiculo ?? ($acceso->vehicle_plate ?? 'Sin vehículo') }}</td>
                    <td>
                        <span class="badge {{ (($acceso->tipo ?? '') === 'Entrada' || ($acceso->access_type ?? '') === 'ENTRY') ? 'badge-cyan' : 'badge-yellow' }}">
                            {{ $acceso->tipo ?? ((($acceso->access_type ?? '') === 'ENTRY') ? 'Entrada' : 'Salida') }}
                        </span>
                    </td>
                    <td>{{ $acceso->fecha ?? ($acceso->access_time ? \Carbon\Carbon::parse($acceso->access_time)->format('d/m/Y H:i') : '—') }}</td>
                    <td>
                        @if(($acceso->estado ?? '') === 'Autorizado' || ($acceso->is_authorized ?? null) === true || ($acceso->is_authorized ?? null) === 1)
                            <span class="badge badge-green">Autorizado</span>
                        @else
                            <span class="badge badge-red">Denegado</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <h2 class="section-title">Top Usuarios Más Activos</h2>
        @if(empty($usuarios_activos) || count($usuarios_activos) === 0)
            <p style="color:#718096; text-align:center; padding: 16px;">No hay actividad de usuarios en el período.</p>
        @else
        <table>
            <thead>
                <tr>
                    <th>Nombre de Usuario</th>
                    <th style="width:100px;">Total Accesos</th>
                    <th>Última Actividad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios_activos as $user)
                <tr>
                    <td>{{ $user->nombre ?? ($user->nombre_completo ?? 'Usuario') }}</td>
                    <td style="text-align:center; font-weight:600;">{{ $user->accesos ?? 0 }}</td>
                    <td>{{ $user->ultimo ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endif

        @if(!empty($include_summary))
        <h2 class="section-title">Resumen de Alertas</h2>
        @if(empty($resumen_alertas) || count($resumen_alertas) === 0)
            <p style="color:#718096; text-align:center; padding: 16px;">No hay alertas en el período.</p>
        @else
        <table>
            <thead>
                <tr>
                    <th>Nivel de Severidad</th>
                    <th style="width:80px;">Total</th>
                    <th style="width:90px;">Resueltas</th>
                    <th style="width:90px;">Pendientes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumen_alertas as $nivel => $datos)
                <tr>
                    <td><strong>{{ $nivel }}</strong></td>
                    <td style="text-align:center;">{{ $datos['total'] ?? 0 }}</td>
                    <td style="text-align:center;">
                        <span class="badge {{ ($datos['total'] ?? 0) == ($datos['resueltas'] ?? 0) ? 'badge-green' : 'badge-gray' }}">
                            {{ $datos['resueltas'] ?? 0 }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @if(($datos['pendientes'] ?? 0) > 0)
                            <span class="badge badge-red">{{ $datos['pendientes'] }}</span>
                        @else
                            <span class="badge badge-green">0</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endif
    </div>

    <div class="footer">
        OKO VISION — Sistema Inteligente de Control de Acceso | Documento de uso interno.
    </div>

</body>
</html>
