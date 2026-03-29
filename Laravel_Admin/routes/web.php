<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    try {
        $apiService = app(\App\Services\ApiService::class);
        $result = $apiService->login([
            'email' => $request->email,
            'password' => $request->password
        ]);
        
        $userData = $result['user'];
        // Crear usuario manual para sesión de Laravel
        $user = new \App\Models\User([
            'id' => $userData->id,
            'email' => $userData->email,
            'nombre' => $userData->nombre,
            'apellidos' => $userData->apellidos,
            'id_rol' => $userData->id_rol,
            'identificador' => $userData->username,
            'id_persona' => $userData->id_persona
        ]);
        
        // Guardamos el token en session (ya lo hace ApiService internamente pero confirmamos)
        session(['api_token' => $result['token']]);
        
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        
        return redirect()->intended('dashboard');
    } catch (\Exception $e) {
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros. ' . $e->getMessage(),
        ])->onlyInput('email');
    }
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alertas', [DashboardController::class, 'alertas'])->name('alertas');
    Route::get('/reportes', [DashboardController::class, 'reportes'])->name('reportes');
    
    // Rutas para el CRUD de usuarios
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Mantener la ruta anterior por compatibilidad temporal
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
});


Route::get('/home', function () {
    return redirect('/dashboard');
});