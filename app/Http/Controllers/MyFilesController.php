<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFolderRequest;
use App\Http\Requests\StoreFileRequest;
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

        if (request()->wantsJson()) {
            return $files;
        }

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
     * Store the uploaded files.
     */
    public function storeFiles(StoreFileRequest $request)
    {
        $payload = $request->validated();
        $fileTree = $request->file_tree;
        $parent = $request->parent;
        $user = $request->user();

        if (! $parent) {
            $parent = $this->getRoot();
        }

        if (! empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            foreach ($payload['files'] as $file) {
                $this->saveFile($file, $parent, $user);
            }
        }
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

    /**
     * Save the file tree.
     *
     * @param  array  $tree
     * @param  \App\Models\File  $parent
     * @param  \App\Models\User  $user
     * @return void
     */
    public function saveFileTree($tree, $parent, $user)
    {
        foreach ($tree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = true;
                $folder->name = $name;

                $parent->appendNode($folder);

                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->saveFile($file, $parent, $user);
            }
        }
    }

    /**
     * Save the individial file.
     *
     * @param  array  $tree
     * @param  \App\Models\File  $parent
     * @param  \App\Models\User  $user
     * @return void
     */
    public function saveFile($file, $parent, $user)
    {
        $path = $file->store('/files/'.$user->id);

        $model = new File();
        $model->is_folder = false;
        $model->storage_path = $path;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $parent->appendNode($model);
    }
}
