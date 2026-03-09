<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionBreak extends Model
{
    protected $fillable = [
        'correction_application_id',
        'break_id',
        'corrected_break_start',
        'corrected_break_end',
    ];

    public function correctionApplication()
    {
        return $this->belongsTo(CorrectionApplication::class);
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }
}
