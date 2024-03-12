<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnakeSpecies extends Model
{
    use HasFactory;
    protected $table = 'snake_species';

    protected $fillable = [
        'snake_species_name', 'status_id', 'created_at', 'updated_at'
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
