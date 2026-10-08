<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'demande_id',
        'reference',
        'idempotency_key',
        'telephone',
        'operateur',
        'montant',
        'statut',
        'operateur_reference',
        'callback_received_at',
    ];

    protected $casts = [
        'callback_received_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }
}