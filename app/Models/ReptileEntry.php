<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReptileEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'caller_name',
        'caller_number',
        'caller_address',
        'caller_aadhar_number',
        'rescued_reptile_type',
        'snake',
        'venom',
        'reptile_condition',
        'reptile_sex',
        'reptile_description',
        'charges',
        'reptile_image',
        'ip_address',
        'latitude',
        'longitude',
    ];
}
