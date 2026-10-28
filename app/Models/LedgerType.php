<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerType extends Model
{
    use SoftDeletes;

    protected $table = 'ledger_types';

    protected $fillable = [
        'text',
    ];

    /**
     * The list of non-deletable default ledger types.
     */
    public const PROTECTED_TYPES = [
        'Cash Advance',
        'Accounts Receivable',
    ];

    /**
     * Check if this record is protected from deletion.
     */
    public function isProtected(): bool
    {
        return in_array($this->text, self::PROTECTED_TYPES);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (LedgerType $ledgerType) {
            if ($ledgerType->isProtected()) {
                throw new \Exception("Cannot delete protected ledger type: {$ledgerType->text}");
            }
        });
    }
}

