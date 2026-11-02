<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasUpdateLogs;
use App\Models\User;
use App\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkLedgerEntry extends Model
{
    use SoftDeletes, HasUpdateLogs;

    protected $table = 'bulk_ledger_entries';

    protected $fillable = [
        'status',
        'description',
        'created_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'updated_by',
        'deleted_by',
    ];

    public function Entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'bulk_ledger_entry_id');
    }

    public function CreatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ApprovedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function RejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function UpdatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function DeletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->Entries->sum(function ($entry) {
            return $entry->quantity * $entry->unit_amount;
        });
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }
}
