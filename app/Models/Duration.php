<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duration extends Model
{
    use HasFactory;

    // Table name if different from the default
    protected $table = 'durations';

    // Primary key
    protected $primaryKey = 'id';

    // Disable timestamps if your table does not have created_at/updated_at
    public $timestamps = true;

    // Fillable attributes for mass assignment
    protected $fillable = [
        'name',
        'description',
        'status',
        'duration_name',
        'duration_in_days',

    ];

    // Attributes you might want to hide from array or JSON responses
    protected $hidden = [
        // Any attributes you don't want to expose to API responses
    ];

    // Cast attributes to specific types if needed
    protected $casts = [
        'status' => 'string', // Cast the status as a string
    ];

    // You can define any relationships if needed
    // For example, if durations have many packages:
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
