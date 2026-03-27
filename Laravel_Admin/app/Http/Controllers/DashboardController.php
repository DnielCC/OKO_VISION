<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\AccessLog;
use App\Models\Alert;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Forzar valores a 0 si las tablas no existen para evitar error 500
        try {
            $accesos_hoy = AccessLog::whereDate('access_time', now()->toDateString())->count();
            $vehiculos_registrados = Vehicle::count();
            $alertas_activas = Alert::where('is_resolved', false)->count();
            $ultimos_accesos = AccessLog::latest('access_time')->take(5)->get();
            $ultimas_alertas = Alert::latest('created_at')->take(4)->get();
        } catch (\Exception $e) {
            $accesos_hoy = 0;
            $vehiculos_registrados = 0;
            $alertas_activas = 0;
            $ultimos_accesos = collect([]);
            $ultimas_alertas = collect([]);
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
        $usuarios = User::all();
        return view('usuarios', compact('usuarios'));
    }

    public function alertas()
    {
        $alertas = Alert::latest('created_at')->get();
        return view('alertas', compact('alertas'));
    }

    public function reportes()
    {
        $accesos = AccessLog::latest('access_time')->get();
        return view('reportes', compact('accesos'));
    }
}
