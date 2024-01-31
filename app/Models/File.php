<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class File extends Model
{
    use HasCreatorAndUpdater, HasFactory, NodeTrait, SoftDeletes;

    /**
     * Check if the file or folder is owned by the provided user id.
     *
     * @return bool
     */
    public function isOwnedBy($userId)
    {
        return $this->created_by === $userId;
    }
}
