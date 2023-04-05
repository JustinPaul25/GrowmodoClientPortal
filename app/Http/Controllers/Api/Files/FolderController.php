<?php

namespace App\Http\Controllers\Api\Files;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\GoogleDriveFile;
use App\Models\GoogleDriveFolder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FolderController extends Controller
{
    public function store(Request $request, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'name' => 'required|string',
            'parent_id' => 'required|numeric|exists:google_drive_folders,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $parent = GoogleDriveFolder::find($request->parent_id);

        if ($parent->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $user = auth()->user();

        $googleClient->initDriveService();
        $folder = $googleClient->createFolder(
            $request->name,
            $organization,
            $user->id,
            $request->parent_id
        );

        activity()
            ->performedOn($folder)
            ->causedBy(auth()->user())
            ->event('created')
            ->log('Create folder');

        return JsonResponse::make($folder, JsonResponse::SUCCESS);
    }

    public function transfer(Request $request, GoogleDriveFolder $folder, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'destination_id' => 'required|numeric|exists:google_drive_folders,id'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $destination = GoogleDriveFolder::find($request->input('destination_id'));

        if ($destination->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $parent = GoogleDriveFolder::find($folder->parent_id);
        $oldParentId = '';
        if ($parent) {
            $oldParent = GoogleDriveFolder::find($parent->id);
            $oldParentId = $oldParent->folder_id;
        }

        $googleClient->initDriveService();
        $id = $googleClient->transferFolder($folder->folder_id, $destination->folder_id, $oldParentId);

        $folder->folder_id = $id;
        $folder->parent_id = $destination->id;

        if ($folder->update()) {
            activity()
                ->performedOn($folder)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['attributes' => $request->all()])
                ->log('Transfer folder');

            return JsonResponse::make($folder, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function rename(Request $request, GoogleDriveFolder $folder, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'name' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $googleClient->initDriveService();
        $googleClient->renameFolder($folder->folder_id, $request->input('name'));

        $folder->name = $request->input('name');
        if ($folder->update()) {
            activity()
                ->performedOn($folder)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['attributes' => $request->all()])
                ->log('Rename folder');

            return JsonResponse::make($folder, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function destroy(GoogleDriveFolder $folder, Organization $organization, GoogleClient $googleClient)
    {
        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, JsonResponse::SERVICE_UNAVAILABLE);

        $this->deleteFolderContent($folder->id);

        $googleClient->initDriveService();
        $googleClient->deleteFolder($folder->folder_id);
        GoogleDriveFile::where('google_drive_folder_id', $folder->id)->delete();

        $name = $folder->name;

        if ($folder->delete()) {
            activity()
                ->causedBy(auth()->user())
                ->event('deleted')
                ->log('Folder named ' . $name . ' deleted and it ');

            return JsonResponse::make([], JsonResponse::SUCCESS, 'Folder Deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function deleteFolderContent($folderId)
    {
        $folders = GoogleDriveFolder::where('parent_id', $folderId)->get();

        foreach ($folders as $folder) {
            $this->deleteFolderContent($folder->id);
            GoogleDriveFile::where('google_drive_folder_id', $folder->id)->delete();
            $folder->delete();
        }
    }

    public function child(GoogleDriveFolder $folder, Organization $organization)
    {
        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, JsonResponse::SERVICE_UNAVAILABLE);

        $folders = GoogleDriveFolder::where('parent_id', $folder->id)->get();

        return JsonResponse::make($folders, JsonResponse::SUCCESS);
    }

    public function files(GoogleDriveFolder $folder, Organization $organization)
    {
        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, JsonResponse::SERVICE_UNAVAILABLE);

        $files = GoogleDriveFile::where('google_drive_folder_id', $folder->id)->get();

        return JsonResponse::make($files, JsonResponse::SUCCESS);
    }
}
