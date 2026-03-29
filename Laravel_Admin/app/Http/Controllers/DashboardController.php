<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

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
}
