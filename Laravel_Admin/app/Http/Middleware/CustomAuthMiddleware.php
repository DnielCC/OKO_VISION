<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si el usuario ya está autenticado, continuar
        if (Auth::check()) {
            return $next($request);
        }

        // Verificar credenciales con nuestra API
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $apiService = app(\App\Services\ApiService::class);
            $users = $apiService->getUsers();
            
            // Buscar usuario por email
            $user = null;
            if (is_array($users)) {
                foreach ($users as $userData) {
                    if (isset($userData['email']) && $userData['email'] === $credentials['email']) {
                        $user = new \App\Models\User((array) $userData);
                        break;
                    }
                }
            }

            if ($user) {
                // Autenticar manualmente
                Auth::login($user, $request);
                $request->session()->regenerate();
                
                return $next($request);
            }

        } catch (\Exception $e) {
            \Log::error('Auth error: ' . $e->getMessage());
        }

        // Si no se encuentra el usuario, intentar con hardcoded
        $hardcodedEmail = 'admin@oko.com';
        $hardcodedPass = '12345678';
        
        if ($credentials['email'] === $hardcodedEmail && $credentials['password'] === $hardcodedPass) {
            $fallbackUser = new \App\Models\User([
                'id' => 1,
                'email' => $hardcodedEmail,
                'nombre' => 'Administrador',
                'apellidos' => 'OKO',
                'id_rol' => 1,
                'identificador' => 'admin'
            ]);
            
            Auth::login($fallbackUser, $request);
            $request->session()->regenerate();
            
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Credenciales incorrectas');
    }
}
