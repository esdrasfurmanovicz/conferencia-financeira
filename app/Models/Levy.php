<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Levy extends Model
{
    protected $fillable = [
        'code', 'name', 'withholding_group', 'withholding_floor', 'active',
    ];

    protected $casts = [
        'withholding_floor' => 'float',
        'active' => 'boolean',
    ];

    public function rateCardItems()
    {
        return $this->hasMany(RateCardItem::class);
    }

    public function profileRules()
    {
        return $this->hasMany(WithholdingProfileRule::class);
    }
}
