<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnakeBite extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'patient_name',
        'patient_number',
        'patient_address',
        'admit_date',
        'discharge_date',
        'patient_status',
        'snake_type',
        'snake_species',
        'hospital_name',
        'description',
        'patient_image',
        'ip_address',
        'latitude',
        'longitude',
    ];
}
