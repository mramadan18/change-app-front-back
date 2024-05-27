<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discription',
    ];

    public function Volunteer_work()
    {
        return $this->hasMany(Volunteer_work::class);
    }

    public function Category_of_user()
    {
        return $this->hasMany(Categoryofuser::class);
    }
}
