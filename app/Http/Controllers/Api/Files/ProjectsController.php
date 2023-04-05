<?php

namespace App\Http\Controllers\Api\Files;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectsController extends Controller
{
    //
    public function index(Request $request, Organization $organization, Project $project)
    {
        //
        $path = 'organizations/' . $organization->id . '/projects/' . $project->id;

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
