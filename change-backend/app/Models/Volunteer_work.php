<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer_work extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'start_date',
        'end_date',
        'address',
        'point',
        'count_worker',
        'status',
        'user_id',
        'category_id',

    ];


    public function favourite()
    {
        return $this->hasMany(Favourite::class);
    }
    public function CarriedOut()
    {
        return $this->hasMany(CarriedOut::class);
    }
    public function Day_of_vlunteer()
    {
        return $this->hasMany(Day_of_vlunteer::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
