<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type_user',
        'phone',
        'address',
        'point',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function carried_out()
    {
        return $this->hasMany(CarriedOut::class);
    }
    public function Day_Of_user()
    {
        return $this->hasMany(Day_Of_user::class);
    }
    public function Volunteer_work()
    {
        return $this->hasMany(Volunteer_work::class);
    }
    public function Favourite()
    {
        return $this->hasMany(Favourite::class);
    }
    public function Category_of_user()
    {
        return $this->hasMany(Categoryofuser::class);
    }
}
