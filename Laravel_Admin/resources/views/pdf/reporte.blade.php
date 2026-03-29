<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General OKO VISION</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 0; font-size: 14px; }
        .header { background-color: #0D1B35; color: #fff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; color: #00F2FF; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #A0AEC0; }
        .content { padding: 30px; }
        .section-title { border-bottom: 2px solid #00F2FF; color: #0D1B35; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; font-size: 18px; }
        
        .metric-box { border: 1px solid #E2E8F0; padding: 15px; margin-bottom: 15px; border-radius: 5px; text-align: center; background-color: #F7FAFC; width: 45%; display: inline-block; vertical-align: top; }
        .metric-box.right { float: right; }
        .metric-value { font-size: 28px; font-weight: bold; color: #0D1B35; margin: 10px 0; }
        .metric-label { font-size: 12px; color: #718096; text-transform: uppercase; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th { background-color: #0D1B35; color: #fff; text-align: left; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #E2E8F0; }
        tr:nth-child(even) { background-color: #F7FAFC; }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #fff; }
        .badge-green { background-color: #48BB78; }
        .badge-red { background-color: #F56565; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #718096; padding: 10px 0; border-top: 1px solid #E2E8F0; }
    </style>
</head>
<body>

    <div class="header">
        <h1>OKO VISION</h1>
        <p>Reporte General de Métricas y Accesos</p>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <div class="content">

        @if($include_summary)
        <h2 class="section-title">Resumen del Periodo</h2>
        <div>
            <div class="metric-box">
                <div class="metric-label">Accesos Totales (Día)</div>
                <div class="metric-value">{{ $total_accesos_hoy }}</div>
            </div>
            <div class="metric-box right">
                <div class="metric-label">Tasa de Detección IA</div>
                <div class="metric-value" style="color: #48BB78;">{{ $tasa_ia }}</div>
            </div>
        </div>
        <div style="clear: both;"></div>

        <div>
            <div class="metric-box">
                <div class="metric-label">Vehículos Registrados</div>
                <div class="metric-value">{{ $total_vehiculos }}</div>
            </div>
            <div class="metric-box right">
                <div class="metric-label">Alertas Activas</div>
                <div class="metric-value" style="color: #F56565;">{{ $alertas_activas }}</div>
            </div>
        </div>
        <div style="clear: both;"></div>
        @endif

        @if($include_charts)
        <h2 class="section-title">Análisis Visual</h2>
        <div style="background-color: #F7FAFC; border: 1px dashed #A0AEC0; padding: 40px; text-align: center; color: #718096; border-radius: 10px;">
            <p style="font-size: 16px; font-weight: bold;">[ Gráficos de Tendencia ]</p>
            <p style="font-size: 12px;">Visualización de flujo de accesos por hora y día</p>
            <div style="margin-top: 20px; height: 100px; width: 100%; border-bottom: 2px solid #CBD5E0; position: relative;">
                <div style="position: absolute; bottom: 0; left: 10%; height: 40%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
                <div style="position: absolute; bottom: 0; left: 25%; height: 70%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
                <div style="position: absolute; bottom: 0; left: 40%; height: 55%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
                <div style="position: absolute; bottom: 0; left: 55%; height: 90%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
                <div style="position: absolute; bottom: 0; left: 70%; height: 30%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
                <div style="position: absolute; bottom: 0; left: 85%; height: 65%; width: 10%; background-color: #00F2FF; opacity: 0.5;"></div>
            </div>
        </div>
        @endif

        @if($include_tables)
        <h2 class="section-title">Últimos Accesos (Muestra)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Vehículo</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimos_accesos as $acceso)
                <tr>
                    <td>#{{ $acceso->id }}</td>
                    <td>{{ $acceso->usuario }}</td>
                    <td>{{ $acceso->vehiculo }}</td>
                    <td>{{ $acceso->tipo }}</td>
                    <td>{{ $acceso->fecha }}</td>
                    <td>
                        @if($acceso->estado == 'Autorizado')
                            <span class="badge badge-green">Autorizado</span>
                        @else
                            <span class="badge badge-red">Denegado</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h2 class="section-title">Top Usuarios Activos</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre de Usuario</th>
                    <th>Total Accesos</th>
                    <th>Última Actividad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios_activos as $user)
                <tr>
                    <td>{{ $user->nombre }}</td>
                    <td>{{ $user->accesos }}</td>
                    <td>{{ $user->ultimo }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($include_summary)
        <h2 class="section-title">Resumen de Alertas</h2>
        <table>
            <thead>
                <tr>
                    <th>Nivel de Severidad</th>
                    <th>Total</th>
                    <th>Resueltas</th>
                    <th>Pendientes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumen_alertas as $nivel => $datos)
                <tr>
                    <td><strong>{{ $nivel }}</strong></td>
                    <td>{{ $datos['total'] }}</td>
                    <td>{{ $datos['resueltas'] }}</td>
                    <td>
                        @if($datos['pendientes'] > 0)
                            <span style="color: #F56565; font-weight: bold;">{{ $datos['pendientes'] }}</span>
                        @else
                            {{ $datos['pendientes'] }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    </div>

    <div class="footer">
        OKO VISION - Sistema Inteligente de Control de Acceso. 
    </div>

</body>
</html>
