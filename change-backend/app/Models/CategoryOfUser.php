<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryOfUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
    ];

    // Define the relationship between CategoryOfUser and User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship between CategoryOfUser and Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
