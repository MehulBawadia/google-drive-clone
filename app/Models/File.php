<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;

class File extends Model
{
    use HasCreatorAndUpdater, HasFactory, NodeTrait, SoftDeletes;

    /**
     * The file or folder belongs to a single user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The file or folder belongs to a single folder.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    /**
     * Check if the folder is root folder.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent_id === null;
    }

    /**
     * Check if the file or folder is owned by the provided user id.
     *
     * @return bool
     */
    public function isOwnedBy($userId)
    {
        return $this->created_by === $userId;
    }

    /**
     * Do something with the model events.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->parent) {
                return;
            }

            $model->path = (! $model->parent->isRoot() ? $model->parent->path.'/' : '').Str::slug($model->name);
        });
    }
}
