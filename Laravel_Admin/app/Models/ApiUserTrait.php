<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Services\ApiService;

trait ApiUserTrait
{
    protected static $apiService;

    public static function getApiService()
    {
        if (!self::$apiService) {
            self::$apiService = app(ApiService::class);
        }
        return self::$apiService;
    }

    // Override Eloquent methods to use API
    public static function all($columns = ['*'])
    {
        try {
            $users = self::getApiService()->getUsers();
            return collect($users);
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function save(array $options = [])
    {
        if ($this->id) {
            return $this->update($this->toArray());
        } else {
            // Crear usuario en API
            $apiData = [
                'id_persona' => $this->id_persona ?? 1,
                'id_rol' => $this->id_rol ?? 2,
                'identificador' => $this->identificador ?? $this->email,
            ];

            try {
                $result = self::getApiService()->createUser($apiData);
                $this->fill((array) $result);
                return $this;
            } catch (\Exception $e) {
                throw new \Exception("Error creating user: " . $e->getMessage());
            }
        }
    }

    public function delete()
    {
        try {
            self::getApiService()->deleteUser($this->id);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error deleting user: " . $e->getMessage());
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $apiData = [
            'id_persona' => $attributes['id_persona'] ?? $this->id_persona,
            'id_rol' => $attributes['id_rol'] ?? $this->id_rol,
            'identificador' => $attributes['identificador'] ?? $this->identificador,
        ];

        if (!empty($attributes['password'])) {
            $apiData['password'] = $attributes['password'];
        }

        try {
            $result = self::getApiService()->patchUser($this->id, $apiData);
            $this->fill((array) $result);
            return $this;
        } catch (\Exception $e) {
            throw new \Exception("Error updating user: " . $e->getMessage());
        }
    }

    // Métodos de rol
    public function isAdmin(): bool
    {
        return $this->id_rol === 1;
    }

    public function isUsuario(): bool
    {
        return $this->id_rol === 2;
    }

    public function isVisitante(): bool
    {
        return $this->id_rol === 3;
    }

    public function getRoleLabel(): string
    {
        return match($this->id_rol) {
            1 => 'Administrador',
            2 => 'Usuario',
            3 => 'Visitante',
            default => 'Desconocido',
        };
    }
}
