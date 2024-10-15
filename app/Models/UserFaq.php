<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFaq extends Model
{
    use HasFactory;

    // Define table name if different from default Laravel naming convention
    protected $table = 'user_faqs';  // Change if your table name is different

    // Define fillable attributes to protect against mass assignment
    protected $fillable = [
        'user_id', 
        'question', 
        'answer'
    ];

    // Define relationships to the user table
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
