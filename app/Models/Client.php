<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'document', 'withholding_profile_id', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function withholdingProfile()
    {
        return $this->belongsTo(WithholdingProfile::class);
    }

    public function estimates()
    {
        return $this->hasMany(LevyEstimate::class);
    }

    public function latestEstimate()
    {
        return $this->hasOne(LevyEstimate::class)->latestOfMany();
    }
}
