<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'otp_email',
        'otp_phone',
        'expired',
    ];
}
