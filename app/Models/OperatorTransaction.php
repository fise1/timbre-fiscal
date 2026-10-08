<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorTransaction extends Model
{
    protected $fillable = [
        'payment_reference',
        'operateur',
        'montant',
        'statut',
        'operator_reference',
    ];
}
