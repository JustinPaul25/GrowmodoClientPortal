<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\TaskType;
use App\Models\TaskTypeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskTypeController extends Controller
{
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

        $taskTypes = TaskType::where('title', '<>', 'Quick Request')->whereRaw('1=1');

        if (!empty($search))
            $taskTypes->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $taskTypes->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($taskTypes->paginate($perPage));
    }


    public function categoriesAdmin(Request $request, $id = null)
    {
        $categories = DB::table('tt_cats')->join('task_type_categories', 'tt_cats.category_id', '=', 'task_type_categories.id')
            ->select('tt_cats.category_id as id', 'task_type_categories.title', 'tt_cats.task_type_id');

        $categories->where('tt_cats.task_type_id', 'LIKE', '' . $id . '');

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
        $platforms = DB::table('platforms')->join('platform_task_types', 'platforms.id', '=', 'platform_task_types.platform_id')
            ->select('platform_task_types.platform_id as id', 'platforms.title', 'platform_task_types.task_type_id');

        $platforms->where('platform_task_types.task_type_id', 'LIKE', '' . $id . '');

        $response = $platforms->get()->toArray();

        $ids = array();

        if (!empty($response)) {
            foreach ($response as $id) {
                array_push($ids, $id->id);
            }
        }

        return JsonResponse::make($ids, JsonResponse::SUCCESS);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories(Request $request)
    {
        //

        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;

        $taskTypeCategories = TaskTypeCategory::whereRaw('1=1');

        if (!empty($search))
            $taskTypeCategories->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $taskTypeCategories->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($taskTypeCategories->paginate($perPage));
    }

    public function storeAdmin(Request $request)
    {
        // return json_decode($request->getContent(), true);
        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'title' => 'required|string|max:255',
            'tags' => 'string',
            'icon' => 'string',
            'turn_around_days_from' => 'required|integer|lt:turn_around_days_to',
            'turn_around_days_to' => 'required|integer|gt:turn_around_days_from',
            'turn_around_days' => 'required',
            'description' => 'string',
            'categories' => 'array',
            'categories.*' => 'exists:task_type_categories,id',
            'platforms' => 'array',
            'platforms.*' => 'exists:platforms,id',
            'questions' => 'array',
            'questions.*' => 'exists:dynamic_questions,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $taskType = new TaskType();
        $taskType->title = $request_data['title'];
        $taskType->tags = $request_data['tags'];
        $taskType->icon = $request_data['icon'];
        $taskType->turn_around_days_from = $request_data['turn_around_days_from'];
        $taskType->turn_around_days_to = $request_data['turn_around_days_to'];
        $taskType->turn_around_days = $request_data['turn_around_days'];
        $taskType->description = $request_data['description'];

        if ($taskType->save()) {
            // foreach ($request->categories as $c => $cat) {
            $taskType->categories()->sync($request_data['categories']);
            $taskType->platforms()->sync($request_data['platforms']);
            // }

            $response = $taskType->toArray();
            $response['platforms'] = $taskType->platforms()->pluck('title');
            $response['tags'] = $taskType->categories()->pluck('task_type_categories.id');

            $taskType->questions()->attach($request->questions);

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
            'tags' => 'string',
            'icon' => 'string',
            'turn_around_days_from' => 'required|integer|lt:turn_around_days_to',
            'turn_around_days_to' => 'required|integer|gt:turn_around_days_from',
            'turn_around_days' => 'required',
            'description' => 'string',
            'categories' => 'array',
            'categories.*' => 'exists:task_type_categories,id',
            'platforms' => 'array',
            'platforms.*' => 'exists:platforms,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $taskType = TaskType::find($id);
        if (empty($taskType->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Task Type not found.');

        $taskType->title = $request_data['title'];
        $taskType->tags = $request_data['tags'];
        $taskType->icon = $request_data['icon'];
        $taskType->turn_around_days_from = $request_data['turn_around_days_from'];
        $taskType->turn_around_days_to = $request_data['turn_around_days_to'];
        $taskType->turn_around_days = $request_data['turn_around_days'];
        $taskType->description = $request_data['description'];

        if ($taskType->save()) {
            if (!empty($request_data['categories']))
                $taskType->categories()->sync($request_data['categories']);
            if (!empty($request_data['platforms']))
                $taskType->platforms()->sync($request_data['platforms']);

            $response = $taskType->toArray();
            $response['platforms'] = $taskType->platforms()->pluck('title');
            $response['tags'] = $taskType->categories()->pluck('task_type_categories.id');

            $taskType->questions()->sync($request_data['questions']);

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function deleteAdmin(Request $request, $id)
    {
        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Task Type not found.');

        $taskType = TaskType::find($id);
        if (empty($taskType->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Task Type not found.');

        $s = $taskType->delete();

        return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'Task Type has been deleted.' : 'Unable to delete user');
    }


    public function questions(Request $request, TaskType $taskType)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;
        $status = $request->status;

        $resource = $taskType->questions()->whereRaw('1 = 1');

        if (!empty($search))
            $resource->where('title', 'LIKE', '%' . $search . '%');

        return JsonResponse::make($resource->paginate($perPage));
    }

    public function quickRequest()
    {
        $form = TaskType::where('title', "Quick Request")->first();

        return JsonResponse::make($form);
    }
}
