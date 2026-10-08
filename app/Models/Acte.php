<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acte extends Model
{
    protected $fillable = [
        'nom',
        'tarif_unitaire',
    ];

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }
}