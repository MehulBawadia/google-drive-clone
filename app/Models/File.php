<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
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
     * A file has only one favorite record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function starred()
    {
        return $this->hasOne(StarredFile::class, 'file_id', 'id')
            ->where('user_id', auth()->id());
    }

    /**
     * Custom attribute for owner.
     */
    public function owner(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['created_by'] === auth()->id() ? 'Me' : $this->user->name;
            }
        );
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
     * Marks the file(s) and/or folder(s) as soft deleted.
     *
     * @return bool
     */
    public function moveToTrash()
    {
        $this->deleted_at = now();

        return $this->save();
    }

    /**
     * Delete forever the records that are soft deleted.
     *
     * @return void
     */
    public function deleteForever()
    {
        $this->deleteFilesFromStorage([$this]);
        $this->forceDelete();
    }

    /**
     * Delete the files that are stored locally for the model.
     *
     * @param  array  $files
     * @return void
     */
    public function deleteFilesFromStorage($files)
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->deleteFilesFromStorage($file->children);
            } else {
                Storage::delete($file->storage_path);
            }
        }
    }

    /**
     * Get the files/folders that are shared with me.
     * But returns only the query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getSharedWithMe()
    {
        return self::select('files.*')
            ->join('file_shares', 'file_shares.file_id', '=', 'files.id')
            ->where('file_shares.user_id', auth()->id())
            ->orderBy('file_shares.created_at', 'DESC')
            ->orderBy('files.id', 'DESC');
    }

    /**
     * Get the files/folders that are shared by me.
     * But returns only the query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getSharedByMe()
    {
        return self::select('files.*')
            ->join('file_shares', 'file_shares.file_id', '=', 'files.id')
            ->where('files.created_by', auth()->id())
            ->orderBy('file_shares.created_at', 'DESC')
            ->orderBy('files.id', 'DESC');
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

        // static::deleted(function ($model) {
        //     if (! $model->is_folder) {
        //         Storage::delete($model->storage_path);
        //     }
        // });
    }
}
