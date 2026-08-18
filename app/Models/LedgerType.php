<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerType extends Model
{
    protected $table = 'ledger_types';

    protected $fillable = [
        'text',
    ];
}
