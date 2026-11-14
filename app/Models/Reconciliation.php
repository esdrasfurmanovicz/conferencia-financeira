<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    public const OPEN = 'open';
    public const CLOSED = 'closed';

    protected $fillable = ['period', 'status', 'closed_at', 'closed_by', 'created_by'];

    protected $casts = [
        'period' => 'date:Y-m-d',
        'closed_at' => 'datetime',
    ];

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function items()
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    public function events()
    {
        return $this->hasMany(ReconciliationEvent::class);
    }
}
