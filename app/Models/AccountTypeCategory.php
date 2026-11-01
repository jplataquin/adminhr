<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountTypeCategory extends Model
{
    use SoftDeletes;

    protected $table = 'account_type_categories';

    protected $fillable = [
        'name',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(LedgerAccount::class, 'type_category', 'name');
    }
}

// Alias for convenience and alternate naming convention
if (!class_exists('App\Models\LedgerAccountTypeCategory', false)) {
    class_alias(AccountTypeCategory::class, 'App\Models\LedgerAccountTypeCategory');
}
