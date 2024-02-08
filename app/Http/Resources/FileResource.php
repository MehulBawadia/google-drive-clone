<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class FileResource extends JsonResource
{
    public static $wrap = false;

    /**
     * The total file size holder.
     *
     * @var int
     */
    public $totalSize = 0;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $size = $this->getTotalSize($this);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'parent_id' => $this->parent_id,
            'is_folder' => $this->is_folder,
            'mime' => $this->mime,
            'size' => Number::fileSize($size, 2),
            'owner' => $this->owner,
            'is_favourite' => (bool) $this->starred,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * Get the total size in bytes for the given file or folder.
     *
     * @param \App\Models\File  $content
     * @return int
     */
    public function getTotalSize($content)
    {
        if ($content->is_folder) {
            foreach ($content->children as $child) {
                if ($child->is_folder) {
                    $this->getTotalSize($child);
                }

                $this->totalSize += $child->size;
            }
        } else {
            $this->totalSize += $content->size;
        }

        return $this->totalSize;
    }
}
