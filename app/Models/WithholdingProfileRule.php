<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithholdingProfileRule extends Model
{
    protected $fillable = ['withholding_profile_id', 'levy_id', 'applies', 'withheld'];

    protected $casts = [
        'applies' => 'boolean',
        'withheld' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(WithholdingProfile::class, 'withholding_profile_id');
    }

    public function levy()
    {
        return $this->belongsTo(Levy::class);
    }
}
