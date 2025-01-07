<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeScreen extends Model
{
    use HasFactory;

    protected $table = 'welcome_screen';

    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
        'key',
    ];
}
