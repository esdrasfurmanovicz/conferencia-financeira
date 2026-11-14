<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateCard extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function items()
    {
        return $this->hasMany(RateCardItem::class);
    }

    public function activeItems()
    {
        $today = now()->toDateString();

        return $this->items()
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
    }
}
