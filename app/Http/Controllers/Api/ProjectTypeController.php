<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\ProjectType;
use App\Models\ProjectTypeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectTypeController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;

        $projectTypes = ProjectType::whereRaw('1=1');

        if (!empty($search))
            $projectTypes->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $projectTypes->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($projectTypes->paginate($perPage));
    }


    public function categories(Request $request)
    {
        //
        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;

        $projectTypeCategories = ProjectTypeCategory::whereRaw('1=1');

        if (!empty($search))
            $projectTypeCategories->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $projectTypeCategories->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($projectTypeCategories->paginate($perPage));
    }


    public function categoriesAdmin(Request $request, $id = null)
    {
        $categories = DB::table('pt_cats')->join('project_type_categories', 'pt_cats.category_id', '=', 'project_type_categories.id')
            ->select('pt_cats.category_id as id', 'project_type_categories.title', 'pt_cats.project_type_id');

        $categories->where('pt_cats.project_type_id', 'LIKE', '' . $id . '');

        $response = $categories->get()->toArray();

        $ids = array();

        if (!empty($response)) {
            foreach ($response as $id) {
                array_push($ids, $id->id);
            }
        }

        return JsonResponse::make($ids, JsonResponse::SUCCESS);
    }

    public function platformsAdmin(Request $request, $id = null)
    {
        $platforms = DB::table('platforms')->join('platform_project_types', 'platforms.id', '=', 'platform_project_types.platform_id')
            ->select('platform_project_types.platform_id as id', 'platforms.title', 'platform_project_types.project_type_id');

        $platforms->where('platform_project_types.project_type_id', 'LIKE', '' . $id . '');

        $response = $platforms->get()->toArray();

        $ids = array();

        if (!empty($response)) {
            foreach ($response as $id) {
                array_push($ids, $id->id);
            }
        }

        return JsonResponse::make($ids, JsonResponse::SUCCESS);
    }

    public function store(Request $request)
    {
        // return json_decode($request->getContent(), true);
        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'title' => 'required|string|max:255',
            'turn_around_days_from' => 'required|integer|lt:turn_around_days_to',
            'turn_around_days_to' => 'required|integer|gt:turn_around_days_from',
            'description' => 'string',
            'categories' => 'array',
            'categories.*' => 'exists:project_type_categories,id',
            'platforms' => 'array',
            'platforms.*' => 'exists:platforms,id',
            'questions' => 'array',
            'questions.*' => 'exists:dynamic_questions,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $projectType = new ProjectType();
        $projectType->title = $request_data['title']; //$request->title;
        $projectType->project_type = $request_data['project_type'];
        $projectType->turn_around_days_from = $request_data['turn_around_days_from']; //$request->turn_around_days;
        $projectType->turn_around_days_to = $request_data['turn_around_days_to']; //$request->turn_around_days;
        $projectType->description = $request_data['description']; //$request->description;
        $projectType->tags = $request_data['tags'];
        $projectType->icon = $request_data['icon'];

        if ($projectType->save()) {
            // foreach ($request->categories as $c => $cat) {
            $projectType->categories()->sync($request->categories);
            $projectType->platforms()->sync($request->platforms);
            // }

            $response = $projectType->toArray();
            $response['platforms'] = $projectType->platforms()->pluck('title');
            $response['tags'] = $projectType->categories()->pluck('project_type_categories.id');
            $projectType->questions()->attach($request_data['questions']);

            $projectType->save();

            return JsonResponse::make($projectType);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function questions(Request $request, ProjectType $projectType)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;
        $status = $request->status;

        $resource = $projectType->questions()->whereRaw('1 = 1');

        if (!empty($search))
            $resource->where('title', 'LIKE', '%' . $search . '%');

        return JsonResponse::make($resource->paginate($perPage));
    }

    public function storeAdmin(Request $request, $id = null)
    {
        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'title' => 'required|string|max:255',
            'description' => 'string',
            'status' => 'string',
            'filter' => 'string',
            'icon' => 'string',
            'project_type' => 'string',
            'turn_around_days_from' => 'required|integer|lt:turn_around_days_to',
            'turn_around_days_to' => 'required|integer|gt:turn_around_days_from',
            'turn_around_days' => 'string',
            'categories' => 'array',
            'categories.*' => 'exists:project_type_categories,id',
            'platforms' => 'array',
            'platforms.*' => 'exists:platforms,id',
            'questions' => 'array',
            'questions.*' => 'exists:dynamic_questions,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $projectType = new ProjectType();

        if (!empty($request_data['title']))
            $projectType->title = $request_data['title'];
        if (!empty($request_data['description']))
            $projectType->description = $request_data['description'];
        if (!empty($request_data['icon']))
            $projectType->icon = $request_data['icon'];
        if (!empty($request_data['project_type']))
            $projectType->project_type = $request_data['project_type'];
        if (!empty($request_data['turn_around_days_from']))
            $projectType->turn_around_days_from = $request_data['turn_around_days_from'];
        if (!empty($request_data['turn_around_days_to']))
            $projectType->turn_around_days_to = $request_data['turn_around_days_to'];
        if (!empty($request_data['turn_around_days']))
            $projectType->turn_around_days = $request_data['turn_around_days'];

        if ($projectType->save()) {
            if (!empty($request_data['categories']))
                $projectType->categories()->sync($request_data['categories']);
            if (!empty($request_data['platforms']))
                $projectType->platforms()->sync($request_data['platforms']);

            $response = $projectType->toArray();
            $response['platforms'] = $projectType->platforms()->pluck('title');
            $response['tags'] = $projectType->categories()->pluck('project_type_categories.id');
            $projectType->questions()->attach($request->questions);

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function updateAdmin(Request $request, $id = null)
    {
        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'title' => 'required|string|max:255',
            'description' => 'string',
            'status' => 'string',
            'filter' => 'string',
            'icon' => 'string',
            'project_type' => 'string',
            'turn_around_days_from' => 'required|integer|lt:turn_around_days_to',
            'turn_around_days_to' => 'required|integer|gt:turn_around_days_from',
            'turn_around_days' => 'integer',
            'categories' => 'array',
            'categories.*' => 'exists:project_type_categories,id',
            'platforms' => 'array',
            'platforms.*' => 'exists:platforms,id',
            'questions' => 'array',
            'questions.*' => 'exists:dynamic_questions,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $projectType = ProjectType::find($id);
        if (empty($projectType->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Project Dir not found.');

        $projectType->title = $request_data['title']; //$request->title;
        $projectType->project_type = $request_data['project_type'];
        $projectType->turn_around_days_from = $request_data['turn_around_days_from']; //$request->turn_around_days;
        $projectType->turn_around_days_to = $request_data['turn_around_days_to']; //$request->turn_around_days;
        $projectType->description = $request_data['description']; //$request->description;
        $projectType->tags = $request_data['tags'];
        $projectType->icon = $request_data['icon'];

        if ($projectType->save()) {
            $projectType->refresh();
            if (!empty($request_data['categories']))
                $projectType->categories()->sync($request_data['categories']);
            if (!empty($request_data['platforms']))
                $projectType->platforms()->sync($request_data['platforms']);

            $response = $projectType->toArray();
            $response['platforms'] = $projectType->platforms()->pluck('title');
            $response['tags'] = $projectType->categories()->pluck('project_type_categories.id');

            $projectType->questions()->sync($request_data['questions']);

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function deleteAdmin(Request $request, $id)
    {
        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Project Dir not found.');

        $projectType = ProjectType::find($id);
        if (empty($projectType->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Project Dir not found.');

        $s = $projectType->delete();

        return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'Project Dir has been deleted.' : 'Unable to delete user');
    }
}
