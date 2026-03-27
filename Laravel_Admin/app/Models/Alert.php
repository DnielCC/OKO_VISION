<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['title', 'description', 'severity', 'created_at', 'is_resolved'];
    public $timestamps = false;
    protected $casts = [
        'created_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];
}
