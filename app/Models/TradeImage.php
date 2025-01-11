<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class TradeImage extends Model
{
    use HasFactory;
    protected $appends = ['picture_url'];

    protected $fillable = ['trade_id', 'image_path', 'image_name'];
    
    public function trade()
    {
        return $this->belongsTo(Trade::class, 'trade_id');
    }
    

    public function getPictureUrlAttribute()
    {
        // If image_path exists, use it to generate the full URL
        return $this->image_path 
            ? asset('storage/' . $this->image_path)  // Correct path for image
            : asset('storage/uploads/images/packages/default_package_picture.png');  // Default image URL
    }



}
