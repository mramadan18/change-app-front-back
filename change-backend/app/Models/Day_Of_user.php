<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day_Of_user extends Model
{
    use HasFactory;
    protected $table = 'day_of_users';
    protected $fillable = [
        'user_id',
        'day',
        'from_hour',
        'to_hour',
    ];

    // Define the relationship between DayOfUser and User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
