<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeScreen extends Model
{
    use HasFactory;

    protected $table = 'welcome_screen';
    protected $appends = ['picture_url'];

    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
        'key',
    ];


    public function getPictureUrlAttribute()
    {
        // Generate the full URL for the picture, or fallback to the default image
        return $this->image 
            ? asset('storage/' . $this->image) // Adjust the path as needed
            : asset('storage/app/public/uploads/images/packages/default_package_picture.png');
    }

}
