<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionStatus extends Model
{
    protected $fillable = ['name'];

    public function correctionApplications()
    {
        return $this->hasMany(CorrectionApplication::class);
    }
}
