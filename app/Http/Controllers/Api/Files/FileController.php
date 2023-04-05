<?php

namespace App\Http\Controllers\Api\Files;

use App\Models\Brand;
use App\Models\Upload;
use App\Models\TempFile;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\GoogleDriveFile;
use Illuminate\Validation\Rule;
use App\Models\GoogleDriveFolder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function file(Upload $upload)
    {
        $organization = $upload->uploadable->organization_id;

        $user = auth()->user();

        if ($user->roles->first()->name !== 'superadmin') {
            $organization = $user->organization->where('id', $organization)->first();
            if (!$organization)
                return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');
        }

        return JsonResponse::make($upload, JsonResponse::SUCCESS);
    }

    public function updateName(Request $request, Upload $upload)
    {
        $rules = [
            'file_id' => 'required|string',
            'name' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $name = explode(".", $upload->file);
        $new_name = $request->input('name') . "." . $name[count($name) - 1];

        $upload->file = $new_name;

        if ($upload->save()) {
            return JsonResponse::make($upload, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function store(Request $request, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'parent_id' => 'nullable|string',
            'uploads.*' => 'required|file'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $folder = GoogleDriveFolder::find($request->parent_id);

        if (!$folder)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        $uploads = $request->files->get('uploads');
        $googleClient->initDriveService();
        foreach ($uploads as $key => $upload) {
            $file = $upload;
            $mime = $file->getClientMimeType();
            $content = $request->file('uploads')[$key]->get();
            $name = $file->getClientOriginalName();
            $size = $file->getSize();

            $fileId = $googleClient->uploadFile($folder->folder_id, $content, $name, $mime);

            $newFile = new GoogleDriveFile();
            $newFile->google_drive_folder_id = $folder->id;
            $newFile->organization_id = $organization->id;
            $newFile->file_id = $fileId;
            $newFile->name = $name;
            $newFile->file_size = $size;
            $upload_info[] = $newFile->save();

            activity()
                ->performedOn($newFile)
                ->causedBy(auth()->user())
                ->event('created')
                ->log('Organization File Upload');
        }

        return JsonResponse::make($upload_info, JsonResponse::SUCCESS);
    }

    public function uploadTemp(Request $request)
    {
        $rules = [
            'uploads.*' => 'required|file'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

        $uploads = $request->files->get('uploads');
        $upload_info = array();

        foreach ($uploads as $key => $upload) {
            $fileName = $upload->getClientOriginalName();
            try {
                $fileHash = basename(Storage::disk('local')->put('temp', $request->file('uploads')[$key]));
                $upload_info[] = TempFile::create([
                    'file' => $fileName,
                    'path' => 'temp/' . $fileHash,
                    'hash_name' => $fileHash,
                    'size' => $request->file('uploads')[$key]->getSize(),
                ]);
            } catch (Exception $e) {
                return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to upload', JsonResponse::SERVICE_UNAVAILABLE);
            }
        }
        return JsonResponse::make($upload_info, JsonResponse::SUCCESS);
    }

    public function destroy(GoogleDriveFile $file, Organization $organization, GoogleClient $googleClient)
    {
        if ($file->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        $googleClient->initDriveService();
        $googleClient->deleteFile($file->file_id);

        if ($file->delete()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'File deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function transfer(Request $request, GoogleDriveFile $file, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'folder_id' => 'required|numeric|exists:google_drive_folders,id'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if ($file->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        $folder = GoogleDriveFolder::find($request->input('folder_id'));

        if ($folder->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        $oldParent = GoogleDriveFolder::find($file->google_drive_folder_id);

        $googleClient->initDriveService();
        $googleClient->transferFile($file->file_id, $folder->folder_id, $oldParent->folder_id);

        $file->google_drive_folder_id = $request->input('folder_id');
        if ($file->update()) {
            activity()
                ->performedOn($file)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['attributes' => $request->all()])
                ->log('File transfer to another folder');

            return JsonResponse::make($file, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function rename(Request $request, GoogleDriveFile $file, Organization $organization, GoogleClient $googleClient)
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
            activity()
                ->performedOn($file)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['attributes' => $request->all()])
                ->log('File rename');

            return JsonResponse::make($file, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function link(GoogleDriveFile $file, Organization $organization, GoogleClient $googleClient)
    {
        if ($file->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');

        $googleClient->initDriveService();
        $url = $googleClient->generateUrl($file->file_id);

        return JsonResponse::make($url, JsonResponse::SUCCESS);
    }
}
