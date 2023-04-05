<?php

namespace App\Http\Controllers\Api\GoogleDrive;

use App\Models\FilePermission;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\GoogleDriveFile;
use App\Models\GoogleDriveFolder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function files(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        $googleClient->initDriveService();
        $files = $googleClient->listFilesFolders($folder->folder_id, 'files');

        return JsonResponse::make($files, JsonResponse::SUCCESS);
    }

    public function upload(Request $request, Organization $organization, GoogleDriveFolder $folder, GoogleClient $googleClient)
    {
        $rules = [
            'file' => 'required|file'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $file = $request->file('file');
        $mime = $file->getClientMimeType();
        $content = $file->get();
        $name = $file->getClientOriginalName();
        $size = $file->getSize();

        $googleClient->initDriveService();
        $fileId = $googleClient->uploadFile($folder->folder_id, $content, $name, $mime);

        $newFile = new GoogleDriveFile();
        $newFile->google_drive_folder_id = $folder->id;
        $newFile->organization_id = $organization->id;
        $newFile->file_id = $fileId;
        $newFile->name = $name;
        $newFile->file_size = $size;

        if ($newFile->save()) {
            return JsonResponse::make($newFile, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function rename(Request $request, Organization $organization, GoogleDriveFile $file, GoogleClient $googleClient)
    {
        $rules = [
            'name' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $googleClient->initDriveService();
        $googleClient->renameFile($file->file_id, $request->input('name'));

        $file->name = $request->input('name');
        if ($file->update()) {
            return JsonResponse::make($file, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function transfer(Request $request, Organization $organization, GoogleDriveFile $file, GoogleClient $googleClient)
    {
        $rules = [
            'folder_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $folder = GoogleDriveFolder::find($request->input('folder_id'));

        $oldParent = GoogleDriveFolder::find($file->google_drive_folder_id);

        $googleClient->initDriveService();
        $googleClient->transferFile($file->file_id, $folder->folder_id, $oldParent->folder_id);

        $file->google_drive_folder_id = $request->input('folder_id');
        if ($file->update()) {
            return JsonResponse::make($file, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function delete(Organization $organization, GoogleDriveFile $file, GoogleClient $googleClient)
    {
        $googleClient->initDriveService();
        $googleClient->deleteFile($file->file_id);

        if ($file->delete()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'File deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function share(Request $request, Organization $organization, GoogleDriveFile $file, GoogleClient $googleClient)
    {
        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = User::find($request->input('user_id'));

        $googleClient->initDriveService();
        $shareId = $googleClient->shareFile($file->file_id, $user->email);

        $permission = new FilePermission();
        $permission->user_id = $request->input('user_id');
        $permission->google_drive_file_id = $file->id;
        $permission->file_id = $shareId;

        if ($permission->save()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'Permission granted to user.');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function removeAccess(Request $request, Organization $organization, GoogleDriveFile $file, GoogleClient $googleClient)
    {
        $rules = [
            'user_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $permission = FilePermission::where('user_id', $request->input('user_id'))->where('goolgle_drive_file_id')->first();

        $googleClient->initDriveService();
        $googleClient->removeFileAccess($file->file_id, $permission->permission_id);

        $permission->delete();

        return JsonResponse::make([], JsonResponse::SUCCESS, 'File access removed!');
    }
}
