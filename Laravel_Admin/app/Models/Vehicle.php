<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['plate', 'brand', 'model', 'color', 'owner_id'];
    public $timestamps = false;

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
