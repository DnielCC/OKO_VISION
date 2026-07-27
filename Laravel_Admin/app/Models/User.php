<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\ApiUserTrait;

class User extends Authenticatable
{
    use ApiUserTrait;

    protected $table = 'usuarios';

    protected $fillable = [
        'id_persona',
        'id_carrera',
        'id_departamento',
        'id_rol',
        'identificador',
        'password',
        'activo',
    ];

    public function persona()
    {
        return $this->belongsTo('App\Models\Persona', 'id_persona', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class, 'user_id');
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->nombre;
    }

    public function getEmailAttribute()
    {
        $val = $this->attributes['email'] ?? null;
        if ($val) return $val;
        return $this->id_persona
            ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('mail')
            : null;
    }

    public function getNombreAttribute()
    {
        $val = $this->attributes['nombre'] ?? null;
        if ($val) return $val;
        return $this->id_persona
            ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('nombre')
            : null;
    }

    public function getApellidosAttribute()
    {
        $val = $this->attributes['apellidos'] ?? null;
        if ($val) return $val;
        return $this->id_persona
            ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('apellidos')
            : null;
    }

    public function getTelefonoAttribute()
    {
        $val = $this->attributes['telefono'] ?? null;
        if ($val) return $val;
        return $this->id_persona
            ? \Illuminate\Support\Facades\DB::table('personas')->where('id', $this->id_persona)->value('telefono')
            : null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id'              => 'integer',
        'id_persona'      => 'integer',
        'id_carrera'      => 'integer',
        'id_departamento' => 'integer',
        'id_rol'          => 'integer',
        'activo'          => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    protected $attributes = [
        'activo' => true,
    ];
}
