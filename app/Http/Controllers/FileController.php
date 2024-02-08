<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToFavouritesRequest;
use App\Http\Requests\CreateFolderRequest;
use App\Http\Requests\FileActionsRequest;
use App\Http\Requests\ShareFilesRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\TrashFileRequest;
use App\Http\Resources\FileResource;
use App\Mail\ShareFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class FileController extends Controller
{
    /**
     * Display the My Files page.
     *
     * @return \Inertia\Inertia
     */
    public function index(?string $folder = null)
    {
        $search = request()->search;

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

        $favourites = request()->exists('favourites');
        $query = File::select('files.*')
            ->where('created_by', auth()->id())
            ->orderBy('is_folder', 'DESC')
            ->orderBy('files.created_at', 'DESC')
            ->orderBy('files.id', 'DESC')
            ->with(['starred:id,user_id,file_id,created_at']);
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        } else {
            $query->where('parent_id', $folder->id);
        }
        if ($favourites) {
            $query->join('starred_files', 'starred_files.file_id', '=', 'files.id')
                ->where('starred_files.user_id', auth()->id());
        }
        $files = $query->paginate(10);

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
            'favourites' => $favourites,
            'search' => $search,
        ]);
    }

    /**
     * Display the trash page.
     *
     * @return \Inertia\Inertia
     */
    public function trash()
    {
        $search = request()->search;
        $query = File::onlyTrashed()
            ->where('created_by', auth()->id())
            ->orderBy('is_folder', 'DESC')
            ->orderBy('deleted_at', 'DESC');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $files = $query->paginate(10);

        $files = FileResource::collection($files);

        if (request()->wantsJson()) {
            return $files;
        }

        return Inertia::render('Trash', [
            'files' => $files,
            'search' => $search,
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
                $child->moveToTrash();
            }
        } else {
            foreach ($payload['ids'] ?? [] as $id) {
                File::find($id)->moveToTrash();
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    /**
     * Restore the file and/or folder.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(TrashFileRequest $request)
    {
        $payload = $request->validated();
        $ids = $payload['ids'] ?? [];

        if ($payload['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->restore();
            }
        }

        return to_route('trash');
    }

    /**
     * Permanently delete the file and/or folder.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteForever(TrashFileRequest $request)
    {
        $payload = $request->validated();
        $ids = $payload['ids'] ?? [];

        if ($payload['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        }

        return to_route('trash');
    }

    /**
     * Add the file(s) or folder(s) to favourites.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleFavourite(AddToFavouritesRequest $request)
    {
        $payload = $request->validated();

        $id = $payload['id'];
        $file = File::find($id);

        $hasFavourited = StarredFile::where('file_id', $file->id)->where('user_id', auth()->id())->first();
        if (! $hasFavourited) {
            $data = [
                'file_id' => $file->id,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            StarredFile::create($data);
        } else {
            $hasFavourited->delete();
        }

        return back();
    }

    /**
     * Share the selected file(s) and/or folder(s).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function share(ShareFilesRequest $request)
    {
        $payload = $request->validated();
        $parent = $request->parent;

        $all = $payload['all'] ?? false;
        $ids = $payload['ids'] ?? [];
        $email = $payload['email'];

        $user = User::where('email', $email)->first();
        if (! $user) {
            return back();
        }

        if (! $all && empty($ids)) {
            return ['message' => 'Please select at least file or folder to share.'];
        }

        if ($all) {
            $files = $parent->children;
        } else {
            $files = File::find($ids);
        }

        $selectedFileIds = $files->pluck('id')->toArray();
        $sharedFiles = FileShare::whereIn('file_id', $selectedFileIds)
            ->where('user_id', $user->id)
            ->pluck('file_id')
            ->toArray();

        $data = [];
        foreach ($files as $file) {
            if (in_array($file->id, $sharedFiles)) {
                continue;
            }

            $data[] = [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        FileShare::insert($data);

        Mail::to($user)
            ->send(new ShareFilesMail($user, auth()->user(), $files));

        return back();
    }

    /**
     * Display the files/folders list that are shared with me.
     *
     * @return \Inertia\Inertia
     */
    public function sharedWithMe()
    {
        $search = request()->search;
        $query = File::getSharedWithMe();
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $files = $query->paginate(10);

        $files = FileResource::collection($files);

        if (request()->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedWithMe', [
            'files' => $files,
            'search' => $search,
        ]);
    }

    /**
     * Donwload the file(s) or folder(s) that are shared by me.
     *
     * @return array
     */
    public function downloadSharedByMe(FileActionsRequest $request)
    {
        $payload = $request->validated();

        $all = $payload['all'] ?? false;
        $ids = $payload['ids'] ?? [];

        if (! $all && empty($ids)) {
            return ['message' => 'please select file to download'];
        }

        $zipFileName = 'shared-by-me';
        if ($all) {
            $files = File::getSharedByMe()->get();
            $url = $this->createZip($files);
            $filename = $zipFileName.'.zip';
        } else {
            [$url, $filename] = $this->getDownloadUrl($ids, $zipFileName);
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
    }

    /**
     * Display the files/folders list that are shared by me.
     *
     * @return \Inertia\Inertia
     */
    public function sharedByMe()
    {
        $search = request()->search;
        $query = File::getSharedByMe();
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $files = $query->paginate(10);

        $files = FileResource::collection($files);

        if (request()->wantsJson()) {
            return $files;
        }

        return Inertia::render('SharedByMe', [
            'files' => $files,
            'search' => $search,
        ]);
    }
}
