<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    use HasFactory;

    // Define table name if different from default Laravel naming convention
    protected $table = 'user_reviews';  // Change if your table name is different

    // Define fillable attributes to protect against mass assignment
    protected $fillable = [
        'user_id',  // Reviewer ID
        'trader_id',  // Trader being reviewed
        'rating', 
        'review', 
        'reviewer_location'
    ];

    // Define relationships to the user and trader (user being reviewed)
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trader()
    {
        return $this->belongsTo(User::class, 'trader_id');
    }
}
