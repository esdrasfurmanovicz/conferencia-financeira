<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LevyEstimate extends Model
{
    protected $fillable = [
        'client_id', 'rate_card_id', 'withholding_profile_id',
        'rate_card_name', 'withholding_profile_name',
        'base_amount', 'levies_total', 'withheld_total', 'net_amount',
        'breakdown', 'notes', 'created_by',
    ];

    protected $casts = [
        'base_amount' => 'float',
        'levies_total' => 'float',
        'withheld_total' => 'float',
        'net_amount' => 'float',
        'breakdown' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function rateCard()
    {
        return $this->belongsTo(RateCard::class);
    }

    public function withholdingProfile()
    {
        return $this->belongsTo(WithholdingProfile::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function toPanelArray(): array
    {
        return [
            'id' => $this->id,
            'rate_card_id' => $this->rate_card_id,
            'rate_card_name' => $this->rate_card_name,
            'withholding_profile_id' => $this->withholding_profile_id,
            'withholding_profile_name' => $this->withholding_profile_name,
            'base_amount' => $this->base_amount,
            'levies_total' => $this->levies_total,
            'withheld_total' => $this->withheld_total,
            'net_amount' => $this->net_amount,
            'breakdown' => $this->breakdown,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'created_by' => $this->creator?->name,
        ];
    }
}
