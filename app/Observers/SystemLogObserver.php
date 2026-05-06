<?php

namespace App\Observers;

use App\Models\SystemLog;

class SystemLogObserver
{
    public function created($model): void
    {
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_value' => null,
            'new_value' => collect($model->getAttributes())->except(['created_at', 'updated_at'])->toArray(),
        ]);
    }

    public function updated($model): void
    {
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_value' => collect($model->getOriginal())->except(['created_at', 'updated_at'])->toArray(),
            'new_value' => collect($model->getAttributes())->except(['created_at', 'updated_at'])->toArray(),
        ]);
    }

    public function deleted($model): void
    {
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_value' => collect($model->getAttributes())->except(['created_at', 'updated_at'])->toArray(),
            'new_value' => null,
        ]);
    }
}
