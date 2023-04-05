<?php

namespace App\Http\Controllers\Api\GoogleDrive;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\FolderPermission;
use App\Models\GoogleDriveFolder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FolderController extends Controller
{
    public function createFolder(Request $request, Organization $organization, GoogleClient $googleClient)
    {
        //user validation here

        $rules = [
            'name' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $parentData = GoogleDriveFolder::where('folder_id', $request->input('folder_id'))->first();

        if (!$request->filled('folder_id')) {
            $parent = env('GOOGLE_DRIVE_ROOT_FOLDER_ID');
        } else {
            $parent = $request->input('folder_id');
            if (!$parentData) {
                return JsonResponse::make([], JsonResponse::INVALID_PARAMS, 'Folder not belong to organization.');
            }
        }

        $googleClient->initDriveService();
        $folder = $googleClient->createFolder(
            $request->input('name'),
            $organization,
            1,
            $parentData->id,
        );

        return;
    }

    public function renameFolder(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        //user validation here

        $rules = [
            'name' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $googleClient->initDriveService();
        $googleClient->renameFolder($folder->folder_id, $request->input('name'));

        $folder->name = $request->input('name');
        if ($folder->update()) {
            return JsonResponse::make($folder, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function transferFolder(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        //user validation here

        $rules = [
            'destination_id' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $destination = GoogleDriveFolder::find($request->input('destination_id'));
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
            return JsonResponse::make($folder, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function deleteFolder(Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        //user validation here

        $googleClient->initDriveService();
        $googleClient->deleteFolder($folder->folder_id);

        if ($folder->delete()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'Folder Deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function shareFolder(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        //user validation here

        $rules = [
            'email' => 'required|email'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $googleClient->initDriveService();
        $permissionId = $googleClient->shareFolder($folder->folder_id, $request->input('email'));

        $user = User::where('email', $request->input('email'))->first();

        $permission = new FolderPermission();
        $permission->user_id = $user->id;
        $permission->google_drive_folder_id = $folder->id;
        $permission->permission_id = $permissionId;

        if ($permission->save()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'Folder Shared to ' . $request->input('email'));
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }
    }

    public function removeAccess(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        //user validation here

        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $permission = FolderPermission::where('user_id', $request->input('user_id'))
            ->where('google_drive_file_id', $folder->id)
            ->first();

        $googleClient->initDriveService();
        $googleClient->removeAccess($folder->folder_id, $permission->permission_id);

        $permission->delete();

        return JsonResponse::make([], JsonResponse::SUCCESS, 'Folder access remove to');
    }
}
