<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id', 'client_id', 'levy_estimate_id',
        'label', 'note', 'confirmed_at', 'confirmed_by', 'created_by',
    ];

    protected $casts = ['confirmed_at' => 'datetime'];

    public function reconciliation()
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function estimate()
    {
        return $this->belongsTo(LevyEstimate::class, 'levy_estimate_id');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
