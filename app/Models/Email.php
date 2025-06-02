<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'tags', 'processed'];
    
    protected $casts = [
        'tags' => 'array',
        'processed' => 'boolean',
    ];
}
