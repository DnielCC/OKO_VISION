<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = ['user_id', 'vehicle_plate', 'access_time', 'access_type', 'is_authorized'];
    public $timestamps = false;
    protected $casts = [
        'access_time' => 'datetime',
        'is_authorized' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
