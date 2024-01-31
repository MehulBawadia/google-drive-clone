<?php

namespace App\Traits;

trait HasCreatorAndUpdater
{
    /**
     * Map the created_by and updated_by to the authenticated user's id
     * while creating and updating the model.
     *
     * @return void
     */
    protected static function bootHasCreatorAndUpdater()
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}
