<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateCardItem extends Model
{
    protected $fillable = ['rate_card_id', 'levy_id', 'percentage', 'valid_from', 'valid_until'];

    protected $casts = [
        'percentage' => 'float',
        'valid_from' => 'date:Y-m-d',
        'valid_until' => 'date:Y-m-d',
    ];

    public function rateCard()
    {
        return $this->belongsTo(RateCard::class);
    }

    public function levy()
    {
        return $this->belongsTo(Levy::class);
    }
}
