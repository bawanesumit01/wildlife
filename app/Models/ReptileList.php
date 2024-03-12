<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReptileList extends Model
{
    use HasFactory;
    protected $table = 'reptile_list';

    protected $fillable = [
        'reptile_name', 'status_id', 'created_at', 'updated_at'
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
