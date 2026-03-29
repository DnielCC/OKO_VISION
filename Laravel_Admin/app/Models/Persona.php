<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    // The table associated with the model.
    protected $table = 'personas';

    // Indicates if the model should be timestamped.
    public $timestamps = false;

    // The attributes that are mass assignable.
    protected $fillable = [
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'foto',
        'telefono',
        'mail'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_persona', 'id');
    }
}
