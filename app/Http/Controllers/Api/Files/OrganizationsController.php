<?php

namespace App\Http\Controllers\Api\Files;

use LDAP\Result;
use App\Models\Task;
use App\Models\Brand;
use App\Models\Upload;
use App\Models\Project;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\OrganizationUsers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function App\Http\Controllers\Api\xz_if_user_owner_or_admin_on_org;

class OrganizationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Organization $organization)
    {
        $user = auth()->user();
        $user_org = user_org($user->id)[0];

        if ($organization->id !== $user_org['id'])
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Organization not found or You are not belong to this organization.');

        $rules = [
            'path' => 'nullable|string',
            'viewall' => 'nullable|string',
            'sortBy' => 'nullable|string',
            'sort' => 'nullable|string'
        ];

        if (!empty($request->input('sortBy'))) {
            $order = $request->input('sortBy');
        } else {
            $order = 'updated_at';
        }

        $sort = strtolower($request->input('sortBy') || 'desc');
        if ($sort !== 'desc' || $sort !== 'asc') $sort = 'desc';

        $validator = Validator::make($request->all(), $rules);
        $viewall = ($request->input('viewall') == 1 || strtolower($request->input('viewall')) === 'true') ? true : false;

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $path_init = env('APP_ENV') . '/organizations/' . $organization->id . '/brands';
        $path = $path_init;
        $query_path = $request->path;

        if (!str_starts_with($query_path, '/')) $query_path = '/' . $query_path;
        if (!str_ends_with($query_path, '/')) $query_path .= '/';
        if (!empty($query_path)) $path .= $query_path;

        $files = Upload::query()->where('path', 'LIKE', $path . '%')->orderBy($order, $sort)->get();
        $directories = array(); // Storage::directories($path);

        $path_files = array();
        $dir_pattern = '^' . env('APP_ENV') . '\/organization(s?)\/' . $organization->id . '\/brand(s?)' . str_replace('/', '\/', $query_path);
        $file_pattern = $dir_pattern . '([a-zA-Z0-9_ -]*)\.(.{2,})';

        foreach ($files as $file)
            if (preg_match('/' . $file_pattern . '/', $file->path)) {
                $path_files[] = $file;
            } else {
                $path_match = array();
                if (preg_match('/' . $dir_pattern . '/', $file->path, $path_match)) {
                    $l_path = $file->path;
                    $path_extra = explode('/', str_replace($path_match[0], '', $l_path));
                    $next_path_query = str_replace($path_init, '', $path_match[0]) . $path_extra[0];
                    $created_at = $file->created_at;
                    $updated_at = $file->updated_at;
                    $path_queries = array_column($directories, 'path_query');
                    $found_key = array_search($next_path_query, $path_queries);
                    if (empty($found_key) && $found_key !== 0 && !empty($path_extra[0])) {
                        $directories[] = [
                            'id' => 'dir' . count($directories),
                            'path' => $path_match[0] . $path_extra[0],
                            'name' => $path_extra[0],
                            'path_query' => $next_path_query,
                            'created_at' => $created_at,
                            'updated_at' => $updated_at,
                            'size' => $file->size,
                        ];
                    } elseif ($found_key >= 0) {
                        $directories[$found_key]['size'] += $file->size;
                        $directories[$found_key]['created_at'] = $created_at;
                        $directories[$found_key]['updated_at'] = $updated_at;
                    }
                }
            }

        if (!$viewall) $files = $path_files;

        return JsonResponse::make([
            'path' => $path,
            'viewall' => $viewall,
            'files' => $files,
            'directories' => $directories,
            'order' => $order,
            'sort' => $sort,
            'url' => [
                'origin' => env('SPACES_ENDPOINT'),
                'edge' => env('SPACES_URL'),
                'cdn' => env('SPACES_CDN_ENDPOINT'),
            ]
        ], JsonResponse::SUCCESS);
    }

    public function isUserInvolvedInOrganization(Request $request, Organization $organization)
    {
        return $organization->employees()->where('user_id', $request->user()->id)->count() > 0;
    }

    public function deleteFile(Upload $upload)
    {
        try {
            $result = Storage::delete($upload->path);
        } catch (Exception $e) {
            return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to delete file.');
        }

        if ($result) {
            if ($upload->delete()) {
                return JsonResponse::make([], JsonResponse::SUCCESS);
            } else {
                return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
            }
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }
}
