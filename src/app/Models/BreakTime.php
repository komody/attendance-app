<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start_time',
        'break_end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
