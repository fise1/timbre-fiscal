<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Demande extends Model
{
    protected $fillable = [
        'user_id',
        'acte_id',
        'nombre_copies',
        'montant_total',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acte(): BelongsTo
    {
        return $this->belongsTo(Acte::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}