<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadKill extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'rescuer_name',
        'rescued_type',
        'description',
        'image',
        'ip_address',
        'latitude',
        'longitude',
    ];
}
