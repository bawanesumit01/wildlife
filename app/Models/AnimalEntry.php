<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'caller_name',
        'caller_number',
        'caller_address',
        'caller_aadhar_number',
        'rescued_animal_type',
        'animal_condition',
        'animal_sex',
        'animal_description',
        'charges',
        'animal_image',
        'ip_address',
        'latitude',
        'longitude',
    ];
    
}
