<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\ApiService;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'id',
        'username',
        'email',
        'nombre',
        'apellidos',
        'id_persona',
        'id_rol',
        'identificador',
        'telefono',
        'activo'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'integer',
        'id_persona' => 'integer',
        'id_rol' => 'integer',
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    private static $apiService;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // Métodos mágicos para manejar propiedades dinámicas
    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        
        if (array_key_exists($name, $this->dynamicAttributes)) {
            return $this->dynamicAttributes[$name];
        }
        
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $this->dynamicAttributes[$name] = $value;
        return parent::__set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->dynamicAttributes[$name]) || isset($this->attributes[$name]) || parent::__isset($name);
    }

    public static function getApiService()
    {
        if (!self::$apiService) {
            self::$apiService = app(ApiService::class);
        }
        return self::$apiService;
    }

    // Métodos estáticos para simular Eloquent pero usando API
    public static function all($columns = ['*'])
    {
        try {
            $users = self::getApiService()->getUsers();
            return collect($users)->map(function ($userData) {
                $user = new self();
                $user->fill((array) $userData);
                return $user;
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public static function find($id)
    {
        try {
            $userData = self::getApiService()->getUser($id);
            if ($userData) {
                $user = new self();
                $user->fill((array) $userData);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function where($field, $value)
    {
        try {
            $users = self::getApiService()->getUsers();
            $filtered = collect($users)->where($field, $value)->first();
            if ($filtered) {
                $user = new self();
                $user->fill((array) $filtered);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function whereEmail($email)
    {
        return self::where('email', $email);
    }

    public static function create(array $data)
    {
        try {
            // Preparar datos para la API
            $apiData = [
                'id_persona' => $data['id_persona'] ?? 1,
                'id_rol' => $data['id_rol'] ?? 2,
                'identificador' => $data['username'] ?? $data['email'],
            ];

            $result = self::getApiService()->createUser($apiData);
            $user = new self();
            $user->fill((array) $result);
            return $user;
        } catch (\Exception $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }

    public function update(array $data)
    {
        try {
            $apiData = [
                'id_persona' => $data['id_persona'] ?? $this->id_persona,
                'id_rol' => $data['id_rol'] ?? $this->id_rol,
                'identificador' => $data['username'] ?? $data['identificador'] ?? $this->identificador,
            ];

            $result = self::getApiService()->updateUser($this->id, $apiData);
            $this->fill((array) $result);
            return $this;
        } catch (\Exception $e) {
            throw new \Exception("Error updating user: " . $e->getMessage());
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

    public function save()
    {
        if ($this->id) {
            return $this->update($this->toArray());
        } else {
            $created = self::create($this->toArray());
            $this->fill($created->toArray());
            return $this;
        }
    }

    // Métodos de autenticación
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    // Métodos de rol (basados en id_rol)
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

    // Métodos adicionales
    public function toArray()
    {
        return array_merge($this->attributes, $this->dynamicAttributes);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->dynamicAttributes[$key] = $value;
            }
        }
        return $this;
    }

    // Simulación de paginación
    public static function paginate($perPage = 10)
    {
        $users = self::all();
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $items = $users->slice($offset, $perPage);
        $total = $users->count();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }
}
