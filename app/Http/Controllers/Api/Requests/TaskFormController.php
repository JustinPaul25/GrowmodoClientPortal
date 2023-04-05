<?php

namespace App\Http\Controllers\Api\Requests;

use App\Models\Task;
use App\Models\Brand;
use App\Models\TaskType;
use App\Models\TempFile;
use App\Helpers\AsanaApi;
use Illuminate\Support\Str;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\DynamicQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskFormController extends Controller
{
    //
    var $asanaApi = null;

    public function __construct()
    {
        $this->asanaApi = new AsanaApi();
    }

    public function index(Request $request, Organization $organization)
    {
        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;
        $brand_id = $request->brand_id;
        $validSort = [
            'title',
        ];
        if (in_array(strtolower($request->input('sortBy')), $validSort)) {
            $sort = strtolower($request->input('sortBy'));
        } else {
            $sort = 'id';
        }

        if (strtolower($request->input('sortType')) === 'desc') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        $tasks = $organization->tasks()->whereRaw('1=1');

        if (!empty($search))
            $tasks->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $tasks->where('status', 'LIKE', '%' . $status . '%');
        if (!empty($request->brand_id))
            $tasks->where('brand_id', $request->brand_id);

        return JsonResponse::make($tasks->orderBy($sort, $order)->paginate($perPage));
    }

    public function store(Request $request, Organization $organization)
    {
        $rules = [
            'title' => 'required|string',
            'dynamic_questions' => 'required|array',
            'task_type_id' => 'required|integer|exists:task_types,id',
            'dynamic_questions.*id' => 'required|integer|exists:dynamic_questions,id',
            'dynamic_questions.*.answer' => 'required|array',
        ];

        $taskType = TaskType::find($request->task_type_id);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $task = new Task;

        $brand_id = $this->findBrandId($request->dynamic_questions);

        $brand_id = $brand_id['value'];

        $brand = Brand::find($brand_id);

        if ($brand->organization_id !== $organization->id)
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Brand not belong to organization.');

        $task->title = $request->title;
        $task->brand_id = $brand_id;
        $task->task_type_id = $request->task_type_id;
        $task->requested_by_id = auth()->id();
        $task->organization_id = $organization->id;

        if ($task->save()) {
            $update_questions = $this->convertFileAnswers($request->dynamic_questions, $task, $organization->id);

            $html_notes = $this->htmlNotes($request->dynamic_questions);

            $task->dynamic_questions = $update_questions;
            $task->save();

            $asanaTask = $this->asanaApi->createTask($organization->asana_gid, [
                "data" => [
                    "completed" => false,
                    "due_on" => "2023-09-30",
                    "name" => $task->title,
                    "html_notes" => $html_notes,
                    "projects" => $organization->asana_gid,
                    "workspace" => '',
                    "assignee_section" => ''
                ]
            ]);

            // return [
            //     gettype($asanaTask['data']),
            //     $asanaTask['data']['gid'],
            // ];

            if (!empty($asanaTask['data']['gid'])) {
                $task->asana_gid = $asanaTask['data']['gid'];
                $task->save();
            }

            $task->refresh();

            // $response = Http::post('https://app.asana.com/api/1.0/tasks', [
            //     'name' => 'Steve',
            //     'role' => 'Network Administrator',
            // ]);

            return JsonResponse::make(Task::find($task->id), JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }
    }

    public function htmlNotes($answers)
    {
        $html_note = "<body>";
        foreach ($answers as $answer) {
            $current_question = DynamicQuestion::find($answer['id']);

            $html_note = $html_note . "<strong>" . $current_question->title . "</strong>\n";

            $value = $answer['answer'][0];

            if ($current_question->type === 'select_brand') {
                $brand = Brand::find($value['value']);
                $html_note = $html_note . $brand->brand_name . "\n\n";
            } else {
                if (is_array($value['value']) || is_object($value['value'])) {
                    $html_note = $html_note . $value['value']['value'] . "\n\n";
                } else {
                    $html_note = $html_note . $value['value'] . "\n\n";
                }
            }
        }
        $html_note = $html_note . "</body>";

        return $html_note;
    }

    public function findBrandId($questions)
    {
        foreach ($questions as $question) {
            $current_question = DynamicQuestion::find($question['id']);

            if ($current_question->type === 'select_brand') {
                return $question['answer'][0];
            };
        }
    }

    public function convertFileAnswers($questions, $task, $org_id)
    {
        foreach ($questions as $key => $question) {
            $current_question = DynamicQuestion::find($question['id']);

            if ($current_question->type === 'upload_single' || $current_question->type === 'upload_multiple') {
                $answers = $question['answer'];
                $new_answers = [];
                foreach ($answers as $key => $answer) {
                    $file = TempFile::find($answer);

                    $path = env('APP_ENV') . '/organizations/' . $org_id . '/brands/' . $task->brand_id . '/tasks/' . $task->id . '/';

                    Storage::put($path . $file->hash_name, Storage::disk('local')->get($file->path));

                    $transfered_file = $task->uploads()->create([
                        'uploadable_id' => $task->id,
                        'uploadable_type' => Task::class,
                        'path' => $path . $file->hash_name,
                        'file' => $file->file,
                        'uploader_id' => auth()->id(),
                        'size' => $file->size,
                    ]);

                    array_push($new_answers, (string)$transfered_file->id);

                    Storage::disk('local')->delete($file->path);
                    $file->delete();
                }
                $questions[$key]['answer'] = $new_answers;
            };
        }

        return $questions;
    }

    public function show(Request $request, Organization $organization, Task $type)
    {
    }

    public function update(Request $request, Organization $organization, Task $type)
    {
    }

    public function destroy(Request $request, Organization $organization, Task $type)
    {
    }
}
