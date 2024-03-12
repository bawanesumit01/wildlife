<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalList extends Model
{
    use HasFactory;
    protected $table = 'animal_list';

    protected $fillable = [
        'animal_name', 'status_id', 'created_at', 'updated_at'
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
