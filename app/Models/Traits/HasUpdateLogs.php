<?php

namespace App\Models\Traits;

use App\Models\LedgerUpdateLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasUpdateLogs
{
    public static function bootHasUpdateLogs(): void
    {
        static::updating(function ($model) {
            $dirty = $model->getDirty();
            unset($dirty['updated_at']);

            if (empty($dirty)) {
                return;
            }

            $oldValues = [];
            $newValues = [];

            foreach ($dirty as $key => $newValue) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $newValue;
            }

            $userId = Auth::id() ?? ($model->updated_by ?? null);

            LedgerUpdateLog::create([
                'loggable_type' => get_class($model),
                'loggable_id'   => $model->id,
                'user_id'       => $userId,
                'old_values'    => $oldValues,
                'new_values'    => $newValues,
            ]);
        });
    }

    public function updateLogs(): MorphMany
    {
        return $this->morphMany(LedgerUpdateLog::class, 'loggable')->orderBy('created_at', 'desc');
    }
}
