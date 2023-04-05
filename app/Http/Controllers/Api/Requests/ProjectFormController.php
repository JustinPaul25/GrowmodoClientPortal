<?php

namespace App\Http\Controllers\Api\Requests;

use App\Models\Project;
use App\Models\TempFile;
use App\Helpers\AsanaApi;
use App\Models\ProjectType;
use Illuminate\Support\Str;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\DynamicQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectFormController extends Controller
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

        $projects = $organization->projects()->whereRaw('1=1');

        if (!empty($search))
            $projects->where('title', 'LIKE', '%' . $search . '%');
        if (!empty($status))
            $projects->where('status', 'LIKE', '%' . $status . '%');
        if (!empty($request->brand_id))
            $projects->where('brand_id', $request->brand_id);

        return JsonResponse::make($projects->orderBy($sort, $order)->paginate($perPage));
    }

    public function store(Request $request, Organization $organization)
    {
        $rules = [
            'title' => 'required|string',
            'dynamic_questions' => 'required|array',
            'project_type_id' => 'required|integer|exists:project_types,id',
            'dynamic_questions.*id' => 'required|integer|exists:dynamic_questions,id',
            'dynamic_questions.*.answer' => 'required|array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $project = new project;

        $brand_id = $this->findBrandId($request->dynamic_questions);

        $project->title = $request->title;
        $project->brand_id = $brand_id['value'];
        $project->project_type_id = $request->project_type_id;
        $project->requested_by_id = auth()->id();
        $project->organization_id = $organization->id;

        if ($project->save()) {
            $update_questions = $this->convertFileAnswers($request->dynamic_questions, $project, $organization->id);

            $project->dynamic_questions = $update_questions;
            $project->save();

            $asanaproject = $this->asanaApi->post('projects', [
                "data" => [
                    "name" => "Bug Project",
                    "notes" => "",
                    // "workspace" => 1331,
                    "team" => env('ASANA_API_DEFAULT_TEAM_ID'),
                    // "workspace" => env('ASANA_API_DEFAULT_TEAM_ID'),
                ]
            ]);

            // return [
            //     gettype($asanaproject['data']),
            //     $asanaproject['data']['gid'],
            // ];

            if (!empty($asanaproject['data']['gid'])) {
                $project->asana_gid = $asanaproject['data']['gid'];
                $project->save();
            }

            $project->refresh();

            // $response = Http::post('https://app.asana.com/api/1.0/projects', [
            //     'name' => 'Steve',
            //     'role' => 'Network Administrator',
            // ]);

            return JsonResponse::make(Project::find($project->id), JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }
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

    public function convertFileAnswers($questions, $project, $org_id)
    {
        foreach ($questions as $key => $question) {
            $current_question = DynamicQuestion::find($question['id']);

            if ($current_question->type === 'upload_single' || $current_question->type === 'upload_multiple') {
                $answers = $question['answer'];
                $new_answers = [];
                foreach ($answers as $key => $answer) {
                    $file = TempFile::find($answer);

                    $path = env('APP_ENV') . '/organizations/' . $org_id . '/brands/' . $project->brand_id . '/projects/' . $project->id . '/';

                    Storage::put($path . $file->hash_name, Storage::disk('local')->get($file->path));

                    $transfered_file = $project->uploads()->create([
                        'uploadable_id' => $project->id,
                        'uploadable_type' => Project::class,
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

    public function show(Request $request, Organization $organization, project $type)
    {
    }

    public function update(Request $request, Organization $organization, project $type)
    {
    }

    public function destroy(Request $request, Organization $organization, project $type)
    {
    }
}
