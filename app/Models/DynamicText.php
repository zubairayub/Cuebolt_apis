<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicText extends Model
{
    // Set the table name if different from default (optional)
    protected $table = 'dynamic_texts';

    // Allow mass assignment on these columns
    protected $fillable = ['key', 'text','screen'];
}
