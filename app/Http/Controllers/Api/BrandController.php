<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Brand;
use App\Models\Upload;
use App\Models\BrandColors;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\GoogleDriveFile;
use Illuminate\Validation\Rule;
use App\Models\GoogleDriveFolder;
use App\Models\BrandSocialAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request, Organization $organization)
    {
        if ($request->input('sortBy') === "brand_name") {
            $sort = "brand_name";
        } else {
            $sort = 'id';
        }

        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;

        // $brands = brand::whereRaw('1=1');
        // if (! auth()->user()->hasRole('superadmin'))
        //     $brands = auth()->user()->whereRaw('1=1');
        // else

        if (!empty($request->org_id))
            $brands = Brand::whereRaw('1=1')->orderBy($sort, 'asc');
        else {
            $brands = $organization->brands()->orderBy($sort, 'asc'); //Brand::whereRaw('1=1');
        }

        // // Get current user organization id
        // if (! empty($request->user()->organization)) {
        //     $brands->where('organization_id', $request->user()->organization()->first()->id);
        // } elseif (! empty($request->user()->employer)) {
        //     $brands->where('organization_id', $request->user()->employer()->first()->organization_id);
        // } elseif (! auth()->user()->hasRole('superadmin')) {

        // } else {
        //     return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        // }

        if (!empty($search))
            $brands->where('title', 'LIKE', '%' . $search . '%');
        // if (! empty($status))
        //     $brands->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($brands->paginate($perPage));
    }

    public function indexWithArchived(Request $request, Organization $organization)
    {
        if ($request->input('sortBy') === "brand_name") {
            $sort = "brand_name";
        } else {
            $sort = 'id';
        }

        $perPage = $request->per_page ?? 10;

        $search = $request->search;
        $status = $request->status;

        if (!empty($request->org_id))
            $brands = Brand::onlyTrashed()->whereRaw('1=1')->orderBy($sort, 'asc');
        else {
            $brands = $organization->brands()->onlyTrashed()->orderBy($sort, 'asc');
        }

        if (!empty($search))
            $brands->where('title', 'LIKE', '%' . $search . '%');

        return JsonResponse::make($brands->paginate($perPage));
    }

    public function indexArchived(Request $request, Organization $organization)
    {
        if ($request->input('sortBy') === "brand_name") {
            $sort = "brand_name";
        } else {
            $sort = 'id';
        }

        $perPage = $request->per_page ?? 10;

        $search = $request->search;
        $status = $request->status;

        if (!empty($request->org_id))
            $brands = Brand::withTrashed()->whereRaw('1=1')->orderBy($sort, 'asc');
        else {
            $brands = $organization->brands()->withTrashed()->orderBy($sort, 'asc');
        }

        if (!empty($search))
            $brands->where('title', 'LIKE', '%' . $search . '%');

        return JsonResponse::make($brands->paginate($perPage));
    }

    public function store(Request $request, Organization $organization, GoogleClient $googleClient)
    {
        $rules = [
            'brand_name' => 'required|string',
            'brand_type' => 'required|integer|exists:brand_categories,id',
            'website' => 'string',
            'tagline' => 'string|nullable',
            'description' => 'string|nullable',
            'target_audience' => 'string|nullable',
            'competitors' => 'string|nullable',
            'value_proposition' => 'string|nullable',
            'social_accounts' => 'array',
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand = new Brand();

        $brand->organization_id = $organization->id;
        $brand->brand_name = $request->brand_name;
        $brand->brand_type = $request->brand_type;
        $brand->website = $request->website;
        $brand->tagline = $request->tagline;
        $brand->description = $request->description;
        $brand->target_audience = $request->target_audience;
        $brand->competitors = $request->competitors;
        $brand->value_proposition = $request->value_proposition;
        $brand->social_accounts = $request->input('social_accounts');

        $googleClient->initDriveService();
        $folder = $googleClient->createFolder(
            $request->brand_name,
            $organization,
            1,
            $organization->brands_folder_id,
        );

        $brand->folder_id = $folder->id;

        if ($brand->save()) {
            activity()
                ->performedOn($brand)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $request->all()])
                ->event('created')
                ->log('Brand Created');

            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function update(Request $request, Organization $organization, Brand $brand)
    {
        $rules = [
            'brand_name' => 'required|string',
            'brand_type' => 'required|integer|exists:brand_categories,id',
            'website' => 'string',
            'tagline' => 'string|nullable',
            'description' => 'string|nullable',
            'target_audience' => 'string|nullable',
            'competitors' => 'string|nullable',
            'value_proposition' => 'string|nullable',
            'social_accounts' => 'array',
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand->organization_id = $organization->id;
        $brand->brand_name = $request->brand_name;
        $brand->brand_type = $request->brand_type;
        $brand->website = $request->website;
        $brand->tagline = $request->tagline;
        $brand->description = $request->description;
        $brand->target_audience = $request->target_audience;
        $brand->competitors = $request->competitors;
        $brand->value_proposition = $request->value_proposition;
        $brand->social_accounts = $request->input('social_accounts');

        if ($brand->update()) {
            activity()
                ->performedOn($brand)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $request->all()])
                ->event('updated')
                ->log('Brand Updated');

            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }


    public function googlefonts(Request $request, Organization $organization, Brand $brand)
    {
        $rules = [
            'googlefonts' => 'array',
            'googlefonts.*' => 'string',
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand->googlefonts = $request->googlefonts;

        if ($brand->update()) {
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function upload(Request $request, Organization $organization, Brand $brand, GoogleClient $googleClient)
    {
        $rules = [
            'target_path' => [
                'required',
                'string',
                Rule::in(['brand_logo', 'brand_fonts', 'brand_icon', 'brand_images', 'graphic_elements']),
            ],
            'uploads' => 'required',
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
                return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors(), JsonResponse::SERVICE_UNAVAILABLE);

            $googleClient->initDriveService();
            $folderExist = GoogleDriveFolder::where('parent_id', $brand->folder_id)->where('name', $request->target_path)->first();
            if ($folderExist) {
                $folder = $folderExist;
            } else {
                $folder = $googleClient->createFolder(
                    $request->target_path,
                    $organization,
                    1,
                    $brand->folder_id,
                );
            }

            $uploads = $request->files->get('uploads');
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
                    ->log('Brand File Upload');
            }

            $brand->refresh();

            return JsonResponse::make($upload_info, JsonResponse::SUCCESS);
        } catch (Exception $e) {
            return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to upload.', JsonResponse::SERVICE_UNAVAILABLE);
        }
    }

    private function fileValidation($target)
    {
        if ($target === 'brand_logo') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:ai,eps,psd,svg,png,gif,jpeg|max:10240',
            ];
        }

        if ($target === 'brand_fonts') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|files|mimes:woff,woff2,ttf,otf,svg|max:10240'
            ];
        }

        if ($target === 'brand_icon') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:svg,png,ico|max:5120'
            ];
        }

        if ($target === 'brand_images') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:svg,png,jpeg,ico,gif|max:10240'
            ];
        }

        if ($target === 'graphic_elements') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:ai,eps,psd,svg,png,gif,jpeg|max:10240'
            ];
        }
    }

    public function color(Request $request, Organization $organization, Brand $brand)
    {
        $rules = [
            'brand_colors' => 'array',
            'brand_colors.*.*' =>  [
                'regex:/^(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))$/i',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand->brand_colors = $request->input('brand_colors');

        if ($brand->update()) {
            activity()
                ->performedOn($brand)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $request->all()])
                ->event('updated')
                ->log('Updated brand color');

            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function socialAccounts(Request $request, Organization $organization, Brand $brand)
    {
        $rules = [
            'social_accounts' => 'array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand->social_accounts = $request->input('social_accounts');

        if ($brand->update()) {
            activity()
                ->performedOn($brand)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $request->all()])
                ->event('updated')
                ->log('Updated brand social accounts');

            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function updateAvatar(Request $request, Organization $organization, Brand $brand)
    {
        $brand->avatar = $request->input('avatar');

        if ($brand->update()) {
            $brand->refresh();
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function step1(Request $request)
    {
        $rules = [
            'organization_id' => 'required|integer|exists:organizations,id',
            'brand_name' => 'required|string',
            'brand_type' => 'required|integer|exists:brand_categories,id',
            'website' => 'string',
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand = new Brand();

        $brand->organization_id = $request->organization_id;
        $brand->brand_name = $request->brand_name;
        $brand->brand_type = $request->brand_type;
        $brand->website = $request->website;


        if ($brand->save()) {
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function step2(Request $request, Brand $brand)
    {
        $rules = [
            [
                'uploads.*' => 'required|mimes:jpg,jpeg,png,bmp|max:20000'
            ], [
                'uploads.*.required' => 'Please upload an image',
                'uploads.*.mimes' => 'Only jpeg,png and bmp images are allowed',
                'uploads.*.max' => 'Sorry! Maximum allowed size for an image is 20MB',
            ],
        ];

        if (!empty($request->brand_colors)) {
            $rules['brand_colors'] = 'array';
            $rules['brand_colors.*.*'] = 'string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        // $brand = Brand::find($id);
        $brand_id = $brand->id;
        $targetPath = 'organizations/' . $brand->organization->id . '/brands/' . $brand->id;
        $uploads = $request->files->get('uploads');


        foreach ($uploads as $u => $upload) {
            $fileName = $upload->getClientOriginalName();
            $filePath = Storage::put($targetPath, $request->file('uploads')[$u]);

            $brand->uploads()->create([
                'uploadable_id' => $brand_id,
                'uploadable_type' => Brand::class,
                'path' => $filePath,
                'file' => $fileName,
                'uploader_id' => $request->user()->id,
            ]);
        }

        foreach ($request->all() as $key => $value) {
            if ($key == 'brand_colors')
                continue;

            $brand->{$key} = $value;
        }

        if ($brand->save()) {
            if (!empty($request->brand_colors)) {
                foreach ($request->brand_colors as $c => $color) {
                    $brand_col = new BrandColors();
                    $brand_col->brand_id = $brand->id;
                    $brand_col->color = $color;
                    $brand_col->type = $c;
                    $brand_col->save();
                }
            }
            $brand->refresh();

            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function step3(Request $request, Brand $brand)
    {
        $rules['social_accounts'] = 'required|array';
        $rules['social_accounts.*.*'] = 'string';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        foreach ($request->social_accounts as $s => $social) {
            $brand_soc = new BrandSocialAccount();
            $brand_soc->brand_id = $brand->id;
            $brand_soc->username = $social;
            $brand_soc->type = $s;
            $brand_soc->save();
        }

        $brand->refresh();

        return JsonResponse::make($brand, JsonResponse::SUCCESS);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Organization $organization, Brand $brand)
    {
        if ($brand->organization->id != $organization->id)
            return JsonResponse::make([], JsonResponse::NOT_FOUND);

        $brand->makeHidden(['organization']);

        return JsonResponse::make($brand);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function archive(Organization $organization, Brand $brand)
    {
        if ($brand->organization->id != $organization->id)
            return JsonResponse::make([], JsonResponse::NOT_FOUND);

        if ($brand->delete()) {
            activity()
                ->performedOn($brand)
                ->causedBy(auth()->user())
                ->event('archived')
                ->log('Brand archived');

            return JsonResponse::make([], JsonResponse::SUCCESS, 'Brand move to archived.');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error.');
        }
    }

    public function restore(Organization $organization, $id)
    {
        $record = Brand::onlyTrashed()->find($id);

        if ($record) {
            if ($record->organization->id != $organization->id)
                return JsonResponse::make([], JsonResponse::NOT_FOUND);

            if ($record->restore()) {
                activity()
                    ->performedOn($record)
                    ->causedBy(auth()->user())
                    ->event('restore')
                    ->log('Brand restored');

                return JsonResponse::make([], JsonResponse::SUCCESS, 'Brand Restored.');
            } else {
                return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error.');
            }
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error.');
        }
    }

    public function destroy(Organization $organization, $id, GoogleClient $googleClient)
    {
        $record = Brand::onlyTrashed()->find($id);
        $folder = GoogleDriveFolder::find($record->folder_id);

        if ($record) {
            $toDel = $record;

            if ($record->forceDelete()) {
                $this->deleteFolderContent($folder->id);

                activity()
                    ->causedBy(auth()->user())
                    ->event('deleted')
                    ->log('Delete brand named' . $toDel->brand_name);

                $googleClient->initDriveService();
                $googleClient->deleteFolder($folder->folder_id);
                GoogleDriveFile::where('google_drive_folder_id', $folder->id)->delete();
                $folder->delete();
                return JsonResponse::make([], JsonResponse::SUCCESS, 'Deleted successfully.');
            } else {
                return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error.');
            }
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error.');
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
}
