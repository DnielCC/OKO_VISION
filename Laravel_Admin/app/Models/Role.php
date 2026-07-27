<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre'];

    public $timestamps = false;

    // Nombres de roles semánticos conocidos (para la lógica de negocio)
    public const NAME_ADMIN           = 'Administrador';
    public const NAME_ESTUDIANTE      = 'Estudiante';
    public const NAME_DOCENTE         = 'Docente';
    public const NAME_PERSONAL_ADMIN  = 'Personal Administrativo';
    public const NAME_VISITANTE       = 'Visitante';

    public static function idByName(string $name): ?int
    {
        static $cache = null;
        if ($cache === null) {
            $cache = self::pluck('id', 'nombre')->all();
        }
        return $cache[$name] ?? null;
    }

    public static function allOrdered(): \Illuminate\Support\Collection
    {
        return self::orderBy('id')->pluck('nombre', 'id');
    }
}
