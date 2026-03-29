<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Support\Facades\Log;

class ApiUserProvider implements UserProviderContract
{
    protected $model;

    public function __construct(Authenticatable $model)
    {
        $this->model = $model;
    }

    public function retrieveByIdentifier($identifier)
    {
        // Para usuarios creados por API, el identifier es el email
        try {
            $user = \App\Models\User::whereEmail($identifier);
            return $user;
        } catch (\Exception $e) {
            Log::error('Error retrieving user: ' . $e->getMessage());
            return null;
        }
    }

    public function retrieveByCredentials(array $credentials)
    {
        try {
            $user = \App\Models\User::whereEmail($credentials['email']);
            
            if ($user) {
                // Aquí se debería verificar el password con la API
                // Por ahora, si el usuario existe, consideramos válido
                return $user;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error retrieving user by credentials: ' . $e->getMessage());
            return null;
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Aquí se debería implementar la validación real con la API
        // Por ahora, siempre retornamos true si el usuario existe
        return true;
    }

    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function getRememberToken()
    {
        return $this->model->getRememberToken();
    }
}
