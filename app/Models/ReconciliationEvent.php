<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationEvent extends Model
{
    public const CLOSED = 'closed';
    public const REOPENED = 'reopened';

    public $timestamps = false;

    protected $fillable = [
        'reconciliation_id', 'action', 'user_id', 'reason', 'total_items', 'confirmed_items', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function reconciliation()
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
