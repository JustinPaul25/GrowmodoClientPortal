<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\TaskType;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\DynamicQuestion;
use App\Models\DynamicQuestionable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DynamicQuestionController extends Controller
{

    var $allowedTypes = [
        'project-dirs' => ProjectType::class,
        'task-dirs' => TaskType::class
    ];

    private function getEloquent($type, $id)
    {
        if (!in_array($type, array_keys($this->allowedTypes))) {
            return abort(404);
        }

        if (empty($id)) {
            return abort(404);
            // return new $this->allowedTypes[$type];
        } else {
            return ($this->allowedTypes[$type])::find($id);
        }

        return (empty($eloquent)) ? null : $eloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $type, $id)
    {
        $model = $this->getEloquent($type, $id);

        $perPage = $request->per_page ?? 10;
        $search = $request->search;
        $status = $request->status;

        $resource = $model->questions()->whereRaw('1 = 1');

        if (!empty($search))
            $resource->where('title', 'LIKE', '%' . $search . '%');

        return JsonResponse::make($resource->paginate($perPage));
    }

    public function list()
    {
        return JsonResponse::make(DynamicQuestion::all());
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
            'title' => 'required|string|max:255',
            'question' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', config('project.questionable_types')),
            'options' => 'nullable|array',
            'options.*.label' => 'required|max:50',
            'options.*.value' => 'required|max:50',
            'required' => 'required|boolean',
            'show_in_overview' => 'required|boolean',
            'alternative_if' => 'nullable|string',
            'alternative_question' => 'nullable|string',
            'alternative_placeholder' => 'nullable|string',
            'alternative_type' => 'nullable|in:' . implode(',', config('project.questionable_types')),
            'alternative_options' => 'nullable|array',
            // 'websites' => 'url',
        ];

        if (in_array($request->type, [
            "checkbox",
            "radio",
            "select",
        ])) {
            $rules['options'] .= '|required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $toInsert = [
            'title' => $request->title,
            'question' => $request->title,
            'placeholder' => $request->placeholder,
            'type' => $request->type,
            'required' => $request->required,
            'show_in_overview' => $request->required,
            'alternative_if' => $request->alternative_if,
            'alternative_question' => $request->alternative_question,
            'alternative_placeholder' => $request->alternative_placeholder,
            'alternative_type' => $request->alternative_type,
            'alternative_options' => $request->alternative_options,
        ];

        if (!empty($request->options))
            $toInsert['options'] = ($request->options);

        $dynamicQuestion = DynamicQuestion::create($toInsert);


        if ($dynamicQuestion->id) {
            return JsonResponse::make($dynamicQuestion, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DynamicQuestion $dynamicQuestion)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'question' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', config('project.questionable_types')),
            'options' => 'nullable|array',
            'options.*.label' => 'required|max:50',
            'options.*.value' => 'required|max:50',
            'required' => 'required|boolean',
            'show_in_overview' => 'required|boolean',
            'alternative_if' => 'nullable|string',
            'alternative_question' => 'nullable|string',
            'alternative_placeholder' => 'nullable|string',
            'alternative_type' => 'nullable|in:' . implode(',', config('project.questionable_types')),
            'alternative_options' => 'nullable|array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $toInsert = [
            'title' => $request->title,
            'question' => $request->title,
            'placeholder' => $request->placeholder,
            'type' => $request->type,
            'required' => $request->required,
            'show_in_overview' => $request->required,
            'alternative_if' => $request->alternative_if,
            'alternative_question' => $request->alternative_question,
            'alternative_placeholder' => $request->alternative_placeholder,
            'alternative_type' => $request->alternative_type,
            'alternative_options' => $request->alternative_options,
        ];

        if (!empty($request->options) || is_array($request->options))
            $toInsert['options'] = ($request->options);

        $isTrue = $dynamicQuestion->update($toInsert);

        if ($isTrue) {
            return JsonResponse::make($dynamicQuestion, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DynamicQuestion $dynamicQuestion)
    {
        $question_id = $dynamicQuestion->id;
        if ($dynamicQuestion->delete()) {
            DynamicQuestionable::where('dynamic_question_id', $question_id)->delete();

            return JsonResponse::make('Question deleted', JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function show(DynamicQuestion $dynamicQuestion)
    {
        return $dynamicQuestion;
    }
}
