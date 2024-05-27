<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarriedOut extends Model
{
    use HasFactory;

    protected $table = 'carried_outs';

    protected $fillable = [
        'user_id',
        'volunteer_work_id',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function volunteerWork(): BelongsTo
    {
        return $this->belongsTo(Volunteer_work::class);
    }
}
