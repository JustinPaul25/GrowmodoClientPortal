<?php

namespace App\Http\Controllers\Api\Files;

use App\Models\Brand;
use App\Models\Upload;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\OrganizationUsers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BrandsController extends Controller
{
    //
    public function index(Request $request, Organization $organization, Brand $brand)
    {
        //
        $path = env('APP_ENV') . '/organizations/' . $organization->id . '/brands/' . $brand->id;

        try {
            $files = Storage::directories($path);
            $directories = Storage::files($path);
        } catch (Exception $e) {
            return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to get files.', 503);
        }

        return JsonResponse::make([
            'files' => $files,
            'directories' => $directories
        ], JsonResponse::SUCCESS);
    }
}
