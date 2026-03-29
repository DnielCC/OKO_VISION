<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class ApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = env('API_URL', 'http://api_backend:8000');
        $this->token = session('api_token', '');
    }

    private function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->when($this->token, function ($http) {
                return $http->withToken($this->token);
            })->{$method}("{$this->baseUrl}{$endpoint}", $data);

            if (!$response->successful()) {
                Log::error("API Request Failed", [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                // En lugar de lanzar excepción, devolver array vacío para login
                if ($method === 'GET' && str_contains($endpoint, '/usuarios')) {
                    return [];
                }
                
                throw new \Exception("API Request Failed: {$response->status()}");
            }

            // Return as objects instead of associative arrays to match Blade -> syntax
            return json_decode($response->body(), false);
        } catch (RequestException $e) {
            Log::error("API Connection Error", [
                'error' => $e->getMessage(),
                'method' => $method,
                'endpoint' => $endpoint
            ]);
            
            // Para login, devolver array vacío en caso de error de conexión
            if ($method === 'GET' && str_contains($endpoint, '/usuarios')) {
                return [];
            }
            
            throw new \Exception("API Connection Error: {$e->getMessage()}");
        }
    }

    // Personas
    public function getPeople()
    {
        return $this->request('GET', '/personas/');
    }

    public function getPerson(int $id)
    {
        return $this->request('GET', "/personas/{$id}");
    }

    public function createPerson(array $data)
    {
        return $this->request('POST', '/personas/', $data);
    }

    public function updatePerson(int $id, array $data)
    {
        return $this->request('PUT', "/personas/{$id}", $data);
    }

    public function patchPerson(int $id, array $data)
    {
        return $this->request('PATCH', "/personas/{$id}", $data);
    }

    public function deletePerson(int $id)
    {
        return $this->request('DELETE', "/personas/{$id}");
    }

    // Usuarios
    public function getUsers()
    {
        return $this->request('GET', '/usuarios/');
    }

    public function getUser(int $id)
    {
        return $this->request('GET', "/usuarios/{$id}");
    }

    public function createUser(array $data)
    {
        return $this->request('POST', '/usuarios/', $data);
    }

    public function updateUser(int $id, array $data)
    {
        return $this->request('PUT', "/usuarios/{$id}", $data);
    }

    public function patchUser(int $id, array $data)
    {
        return $this->request('PATCH', "/usuarios/{$id}", $data);
    }

    public function deleteUser(int $id)
    {
        return $this->request('DELETE', "/usuarios/{$id}");
    }

    // Vehículos
    public function getVehicles()
    {
        return $this->request('GET', '/vehiculos/');
    }

    public function getVehicle(int $id)
    {
        return $this->request('GET', "/vehiculos/{$id}");
    }

    public function createVehicle(array $data)
    {
        return $this->request('POST', '/vehiculos/', $data);
    }

    public function updateVehicle(int $id, array $data)
    {
        return $this->request('PUT', "/vehiculos/{$id}", $data);
    }

    public function deleteVehicle(int $id)
    {
        return $this->request('DELETE', "/vehiculos/{$id}");
    }

    // Accesos
    public function getAccessLogs()
    {
        return $this->request('GET', '/accesos/');
    }

    public function getAccessLog(int $id)
    {
        return $this->request('GET', "/accesos/{$id}");
    }

    public function createAccessLog(array $data)
    {
        return $this->request('POST', '/accesos/', $data);
    }

    // Health Check
    public function healthCheck()
    {
        try {
            return $this->request('GET', '/');
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Autenticación (si la API tuviera endpoints de auth)
    public function login(array $credentials)
    {
        try {
            $response = $this->request('POST', '/auth/login', [
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ]);
            
            // Si funciona, devolver el usuario y un token simulado (hasta que se implemente JWT en backend)
            return [
                'user' => $response,
                'token' => 'simulated_token_' . $response->id
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error de autenticación: ' . $e->getMessage());
        }
    }

    public function setToken(string $token)
    {
        $this->token = $token;
        session(['api_token' => $token]);
    }
}
