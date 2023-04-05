<?php

namespace App\Http\Controllers\Api\Admin;

use Exception;
use App\Models\Brand;
use App\Models\BrandColors;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\BrandSocialAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $rules = [
            'brand_name' => 'required|string',
            'brand_type' => 'required|integer|exists:brand_categories,id',
            'website' => 'url',
        ];

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $brand = new Brand();

        $brand->organization_id = $organization->id;
        $brand->brand_name = $request->brand_name;
        $brand->brand_type = $request->brand_type;
        $brand->website = $request->website;


        if ($brand->save()) {
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
            'website' => 'url',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $brand->organization_id = $organization->id;
        $brand->brand_name = $request->brand_name;
        $brand->brand_type = $request->brand_type;
        $brand->website = $request->website;


        if ($brand->update()) {
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }

    }

    public function upload(Request $request, Organization $organization, Brand $brand)
    {
        $rules = [
            'target_path' => [
                    'required',
                    'string',
                    Rule::in(['brand_logo', 'brand_fonts', 'brand_icon', 'brand_images', 'graphic_elements']),
                ]
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        // $rules = $this->fileValidation($request->input('target_path'));

        // $validator = Validator::make($request->all(), $rules);

        // if ($validator->fails())
        //     return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $brand_id = $brand->id;
        $targetPath = env('APP_ENV') . '/organizations/' . $brand->organization->id . '/brands/' . $brand->id . '/' . $request->input('target_path');
        $uploads = $request->files->get('uploads');

        foreach ( $uploads as $key => $upload ) {
            $fileName = $upload->getClientOriginalName();

            try {
                $filePath = Storage::put($targetPath, $request->file('uploads')[$key]);
            } catch (Exception $e) {
                return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to upload file.', 503);
            }

            try {
                $brand->uploads()->create([
                    'uploadable_id' => $brand_id,
                    'uploadable_type' => Brand::class,
                    'path' => $filePath,
                    'file' => $fileName,
                    'uploader_id' => $request->user()->id,
                    'size' => $request->file('uploads')[$key]->getSize(),
                ]);
            } catch (Exception $e) {
                return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to upload.', $e->getCode());
            }
        }

        $brand->refresh();

        return JsonResponse::make($brand, JsonResponse::SUCCESS);
    }

    private function fileValidation($target)
    {
        if($target === 'brand_logo') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:ai,eps,psd,svg,png,gif,jpeg|max:10240',
            ];
        }

        if($target === 'brand_fonts') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:woff,woff2,ttf,otf|max:10240',
            ];
        }

        if($target === 'brand_icon') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:svg,png,ico|max:5120',
            ];
        }

        if($target === 'brand_images') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:svg,png,jpeg,ico,gif|max:10240',
            ];
        }

        if($target === 'graphic_elements') {
            return [
                'uploads' => 'required',
                'uploads.*' => 'required|mimes:ai,eps,psd,svg,png,gif,jpeg|max:10240',
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
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function socialAccounts(Request $request, Organization $organization, Brand $brand) {
        $rules = [
            'social_accounts' => 'array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brand->social_accounts = $request->input('social_accounts');

        if ($brand->update()) {
            return JsonResponse::make($brand, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function destroy(Organization $organization, Brand $brand)
    {
        $current_user = auth()->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($brand->delete()) {
            return JsonResponse::make([], JsonResponse::SUCCESS, 'Brand Deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }
}
