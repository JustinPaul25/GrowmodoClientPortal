<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
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

        // $tasks = Task::whereRaw('1=1');
        if (!auth()->user()->hasRole('superadmin'))
            $tasks = auth()->user()->whereRaw('1=1');
        else
            $tasks = Task::whereRaw('1=1');

        if (!empty($search))
            $tasks->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $tasks->where('status', 'LIKE', '%' . $status . '%');

        // Get current user organization id
        if (!empty($request->user()->organization)) {
            $tasks->where('organization_id', $request->user()->organization()->first()->id);
        } elseif (!empty($request->user()->employer)) {
            $tasks->where('organization_id', $request->user()->employer()->first()->organization_id);
        } elseif (!auth()->user()->hasRole('superadmin')) {
        } else {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        }

        // return $tasks->get();

        return JsonResponse::make($tasks->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            // 'project_id' => 'required',

            // 'project_id' => 'integer|exists:projects,id',
            // 'organization_id' => 'integer|exists:organizations,id',

            'task_type_id' => 'exists:task_types,id',
            // 'title' => 'required|max:255',
            'description' => 'required|max:1000',
            'target_audience' => 'string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',

            // 'platforms' => 'nullable|exists:platforms.id'

            'video_walkthrough' => 'string|max:255',
            'warnings' => 'string|max:255',
            'brand_id' => 'exists:brands,id',

            'resources' => 'string|max:255',
            // 'status' => 'required|in:open,backlog,working_on_it,qc_design,qc_tech,waiting_for_feedback',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $task = new Task;

        foreach ($request->all() as $k => $v) {
            $task->{$k} = $v;
        }

        if ($task->save()) {

            // $response = Http::post('https://app.asana.com/api/1.0/tasks', [
            //     'name' => 'Steve',
            //     'role' => 'Network Administrator',
            // ]);

            return JsonResponse::make($task, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        //
        $this->validate($request, [
            // 'project_id' => 'required',

            'title' => 'required|max:255',
            'description' => 'required|max:1000',
            'video_walkthrough' => 'string|max:255',
            'resources' => 'string|max:255',
            'target_audience' => 'string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,backlog,working_on_it,qc_design,qc_tech,waiting_for_feedback',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        //
    }
}
