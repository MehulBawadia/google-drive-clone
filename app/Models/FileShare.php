<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assinable.
     *
     * @var array
     */
    protected $fillable = [
        'file_id', 'user_id',
    ];
}
