<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFolderRequest;
use App\Http\Requests\FileActionsRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    /**
     * Temporary delete the files and/or folders.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FileActionsRequest $request)
    {
        $payload = $request->validated();
        $parent = $request->parent;

        if ($payload['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->delete();
            }
        } else {
            foreach ($payload['ids'] ?? [] as $id) {
                File::find($id)->delete();
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    /**
     * Donwload the file(s) or folder(s).
     *
     * @return array
     */
    public function download(FileActionsRequest $request)
    {
        $payload = $request->validated();
        $parent = $request->parent;

        $all = $payload['all'] ?? false;
        $ids = $payload['ids'] ?? [];

        if (! $all && empty($ids)) {
            return ['message' => 'please select file to download'];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name.'.zip';
        } else {
            if (count($ids) === 1) {
                $file = File::find($ids[0]);
                if ($file->is_folder) {
                    if ($file->children->count() === 0) {
                        return ['message' => 'The folder is empty.'];
                    }

                    $url = $this->createZip($file->children);
                    $filename = $file->name.'.zip';
                } else {
                    $destination = 'public/'.pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $destination);

                    $url = asset(Storage::url($destination));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()
                    ->whereIn('id', $ids)
                    ->get();
                $url = $this->createZip($files);
                $filename = $parent->name.'.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
    }

    /**
     * Create the zip containing files and/or folders that the user
     * has requested to download.
     *
     * @param  array  $files
     * @return string
     */
    public function createZip($files)
    {
        $zipPath = 'zip/'.Str::random().'.zip';
        $publicPath = "public/$zipPath";

        if (! is_dir(dirname($publicPath))) {
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    /**
     * Add the given files into the provided zip archive.
     *
     * @param  \ZipArchive  $zip
     * @param  array  $files
     * @param  string  $ancestors
     * @return void
     */
    private function addFilesToZip($zip, $files, $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors.$file->name.'/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors.$file->name);
            }
        }
    }
}
