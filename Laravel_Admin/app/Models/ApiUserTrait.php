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
                'id_rol' => $this->id_rol ?? Role::idByName(Role::NAME_ESTUDIANTE) ?? 18,
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

    // ====== Métodos de roles (tabla real de roles por nombre) ======

    public function isAdmin(): bool
    {
        $adminId = Role::idByName(Role::NAME_ADMIN);
        if ($adminId === null) return false;
        return (int) ($this->id_rol ?? 0) === (int) $adminId;
    }

    public function isVisitante(): bool
    {
        $visitId = Role::idByName(Role::NAME_VISITANTE);
        if ($visitId === null) return false;
        return (int) ($this->id_rol ?? 0) === (int) $visitId;
    }

    public function isEstudiante(): bool
    {
        $id = Role::idByName(Role::NAME_ESTUDIANTE);
        if ($id === null) return false;
        return (int) ($this->id_rol ?? 0) === (int) $id;
    }

    public function isDocente(): bool
    {
        $id = Role::idByName(Role::NAME_DOCENTE);
        if ($id === null) return false;
        return (int) ($this->id_rol ?? 0) === (int) $id;
    }

    public function isPersonalAdmin(): bool
    {
        $id = Role::idByName(Role::NAME_PERSONAL_ADMIN);
        if ($id === null) return false;
        return (int) ($this->id_rol ?? 0) === (int) $id;
    }

    // Compatibilidad con vistas antiguas que llaman isUsuario()
    public function isUsuario(): bool
    {
        return !$this->isAdmin() && !$this->isVisitante();
    }

    public function getRoleLabel(): string
    {
        return (string) ($this->role_name ?? 'Desconocido');
    }

    public function getRoleKeyAttribute(): string
    {
        $nombre = mb_strtolower((string) ($this->role_name ?? ''));
        return match ($nombre) {
            'administrador'          => 'admin',
            'estudiante'             => 'estudiante',
            'docente'                => 'docente',
            'personal administrativo' => 'personal_admin',
            'visitante'              => 'visitante',
            default                  => 'unknown',
        };
    }

    public function getRoleBadgeAttribute(): string
    {
        $key = $this->role_key;
        $label = e($this->getRoleLabel());

        return match ($key) {
            'admin'          => '<span class="bg-red-400/10 text-red-400 text-[10px] px-2 py-1 rounded-full border border-red-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
            'docente'        => '<span class="bg-orange-400/10 text-orange-400 text-[10px] px-2 py-1 rounded-full border border-orange-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
            'personal_admin' => '<span class="bg-violet-400/10 text-violet-400 text-[10px] px-2 py-1 rounded-full border border-violet-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
            'visitante'      => '<span class="bg-purple-400/10 text-purple-400 text-[10px] px-2 py-1 rounded-full border border-purple-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
            'estudiante'     => '<span class="bg-cyan-400/10 text-cyan-400 text-[10px] px-2 py-1 rounded-full border border-cyan-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
            default          => '<span class="bg-gray-400/10 text-gray-400 text-[10px] px-2 py-1 rounded-full border border-gray-400/20 uppercase font-bold text-center inline-block w-36">' . $label . '</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->activo) {
            return '<span class="bg-green-500/10 text-green-400 text-[10px] px-2 py-1 rounded-full border border-green-400/20 uppercase font-bold">Activo</span>';
        }
        return '<span class="bg-red-500/10 text-red-400 text-[10px] px-2 py-1 rounded-full border border-red-400/20 uppercase font-bold">Inactivo</span>';
    }
}
