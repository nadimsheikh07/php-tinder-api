<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLike extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'liked_user_id'
    ];

    // Define the user relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the liked user relationship
    public function likedUser()
    {
        return $this->belongsTo(User::class, 'liked_user_id');
    }
}
