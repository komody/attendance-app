<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionApplication extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'corrected_clock_in_time',
        'corrected_clock_out_time',
        'remarks',
        'correction_status_id',
        'approved_admin_id',
        'approval_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function correctionStatus()
    {
        return $this->belongsTo(CorrectionStatus::class);
    }

    public function correctionBreaks()
    {
        return $this->hasMany(CorrectionBreak::class);
    }

    public function isPending(): bool
    {
        return $this->correctionStatus?->name === '承認待ち';
    }
}
