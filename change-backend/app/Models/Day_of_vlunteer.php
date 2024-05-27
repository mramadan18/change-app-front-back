<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day_of_vlunteer extends Model
{
    use HasFactory;
    protected $table = 'day_of_volunteers';
    protected $fillable = [
        'volunteer_work_id',
        'day_of_week',
    ];

    public function volunteerWork()
    {
        return $this->belongsTo(Volunteer_work::class);
    }
}
