<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Inertia\Inertia;

class MyFilesController extends Controller
{
    /**
     * Display the My Files page.
     *
     * @return \Inertia\Inertia
     */
    public function index(?string $folder = null)
    {
        if ($folder) {
            $folder = File::query()
                ->where([
                    'created_by' => auth()->id(),
                    'path' => $folder,
                ])
                ->firstOrFail();
        }
        if (! $folder) {
            $folder = $this->getRoot();
        }

        $files = File::where([
            'parent_id' => $folder->id,
            'created_by' => auth()->id(),
        ])
            ->orderBy('is_folder', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $files = FileResource::collection($files);

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('MyFiles', [
            'rootFolder' => $folder,
            'files' => $files,
            'ancestors' => $ancestors,
        ]);
    }

    /**
     * Create the folder with the provided details.
     *
     * @return void
     */
    public function createFolder(CreateFolderRequest $request)
    {
        $payload = $request->validated();
        $parent = $request->parent;
        if (! $parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = true;
        $file->name = $payload['name'];

        $parent->appendNode($file);
    }

    /**
     * Get the root folder of the authenticated user's id.
     *
     * @return \App\Models\File|null
     */
    private function getRoot()
    {
        return File::query()
            ->where('created_by', auth()->id())
            ->whereIsRoot()
            ->firstOrFail();
    }
}
