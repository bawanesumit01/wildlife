<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panchnama extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'rescuer_name',
        'forest_department_name',
        'date',
        'location',
        'staff_name',
        'description',
        'panchnama_image',
        'ip_address',
        'latitude',
        'longitude',
    ];
}
