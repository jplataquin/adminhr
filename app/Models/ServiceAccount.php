<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class ServiceAccount extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = 'service_accounts';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * Get the user who created this service account.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (ServiceAccount $serviceAccount) {
            $serviceAccount->tokens()->delete();
        });
    }
}
