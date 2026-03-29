<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\ApiUserTrait;

class User extends Authenticatable
{
    use ApiUserTrait;

    protected $table = 'usuarios';
    
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
        'activo',
        'password'
    ];

    // Relación con Personas
    public function persona()
    {
        return $this->belongsTo('App\Models\Persona', 'id_persona', 'id');
    }

    public function getEmailAttribute()
    {
        // En Eloquent real si no lo trae el query, consultar a través de API
        // O si ya está seteado (por el auth login), lo regresa
        return $this->attributes['email'] ?? ($this->id_persona ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('mail') : null);
    }

    public function getNombreAttribute()
    {
        return $this->attributes['nombre'] ?? ($this->id_persona ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('nombre') : null);
    }

    public function getApellidosAttribute()
    {
        return $this->attributes['apellidos'] ?? ($this->id_persona ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('apellidos') : null);
    }

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
}
