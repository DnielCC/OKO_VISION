<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;

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
            // Obtener datos desde la API
            $usuarios = $this->apiService->getUsers();
            $vehiculos = $this->apiService->getVehicles();
            $accesos = $this->apiService->getAccessLogs();
            
            // Contar y obtener últimos registros
            $accesos_hoy = is_array($accesos) ? count($accesos) : 0;
            $vehiculos_registrados = is_array($vehiculos) ? count($vehiculos) : 0;
            $alertas_activas = 0; // Por ahora, no hay endpoint de alertas
            $ultimos_accesos = is_array($accesos) ? array_slice($accesos, 0, 5) : [];
            $ultimas_alertas = []; // Por ahora, no hay alertas
            
        } catch (\Exception $e) {
            $accesos_hoy = 0;
            $vehiculos_registrados = 0;
            $alertas_activas = 0;
            $ultimos_accesos = [];
            $ultimas_alertas = [];
        }
        
        return view('dashboard', compact(
            'accesos_hoy', 
            'vehiculos_registrados', 
            'alertas_activas',
            'ultimos_accesos',
            'ultimas_alertas'
        ));
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

    public function alertas()
    {
        try {
            // Por ahora, simulamos alertas ya que no hay endpoint
            $alertasData = [
                (object) [
                    'id' => 1,
                    'title' => 'Acceso Denegado',
                    'description' => 'Intento de acceso no autorizado',
                    'severity' => 'HIGH',
                    'is_resolved' => false,
                    'created_at' => now()
                ],
                (object) [
                    'id' => 2,
                    'title' => 'Cámara Offline',
                    'description' => 'La cámara principal no responde',
                    'severity' => 'CRITICAL',
                    'is_resolved' => true,
                    'created_at' => now()->subMinutes(30)
                ]
            ];
            $alertas = collect($alertasData);
            return view('alertas', compact('alertas'));
        } catch (\Exception $e) {
            return view('alertas', ['alertas' => collect([])])->with('error', 'Error al cargar alertas: ' . $e->getMessage());
        }
    }

    public function reportes()
    {
        try {
            $accesosRaw = $this->apiService->getAccessLogs();
            $accesos = collect(is_array($accesosRaw) ? $accesosRaw : []);
            return view('reportes', compact('accesos'));
        } catch (\Exception $e) {
            return view('reportes', ['accesos' => collect([])])->with('error', 'Error al cargar reportes: ' . $e->getMessage());
        }
    }

    public function exportarPdf()
    {
        // Datos de relleno simulando la información de las tablas:
        // Accesos, Usuarios, Vehículos, Alertas.
        
        $metrics = [
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'total_accesos_hoy' => 247,
            'total_vehiculos' => 156,
            'tasa_ia' => '98.7%',
            'alertas_activas' => 12,
            
            'ultimos_accesos' => [
                (object)['id' => 1, 'usuario' => 'Juan Díaz', 'vehiculo' => 'ABC-1234', 'tipo' => 'Entrada', 'fecha' => now()->subMinutes(5)->format('d/m/Y H:i:s'), 'estado' => 'Autorizado'],
                (object)['id' => 2, 'usuario' => 'María García', 'vehiculo' => 'XYZ-9876', 'tipo' => 'Salida', 'fecha' => now()->subMinutes(15)->format('d/m/Y H:i:s'), 'estado' => 'Autorizado'],
                (object)['id' => 3, 'usuario' => 'Carlos Rodríguez', 'vehiculo' => 'Sin vehículo', 'tipo' => 'Entrada', 'fecha' => now()->subMinutes(25)->format('d/m/Y H:i:s'), 'estado' => 'Denegado'],
                (object)['id' => 4, 'usuario' => 'Ana López', 'vehiculo' => 'LMN-4567', 'tipo' => 'Entrada', 'fecha' => now()->subMinutes(40)->format('d/m/Y H:i:s'), 'estado' => 'Autorizado'],
                (object)['id' => 5, 'usuario' => 'Roberto Silva', 'vehiculo' => 'Sin vehículo', 'tipo' => 'Salida', 'fecha' => now()->subMinutes(55)->format('d/m/Y H:i:s'), 'estado' => 'Autorizado']
            ],
            
            'usuarios_activos' => [
                (object)['nombre' => 'Juan Díaz', 'accesos' => 247, 'ultimo' => 'Hoy 14:30'],
                (object)['nombre' => 'María García', 'accesos' => 189, 'ultimo' => 'Hoy 13:15'],
                (object)['nombre' => 'Carlos Rodríguez', 'accesos' => 156, 'ultimo' => 'Hoy 11:45']
            ],
            
            'resumen_alertas' => [
                'Criticas' => ['total' => 47, 'resueltas' => 35, 'pendientes' => 12],
                'Altas' => ['total' => 89, 'resueltas' => 67, 'pendientes' => 22],
                'Medias' => ['total' => 156, 'resueltas' => 142, 'pendientes' => 14]
            ]
        ];

        $pdf = Pdf::loadView('pdf.reporte', $metrics);
        // Opcional: configurar formato de hoja
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Reporte_General_OkoVision.pdf');
    }
}
