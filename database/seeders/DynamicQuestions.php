<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Brand;
use App\Models\Project;
use App\Models\TaskType;
use App\Models\ProjectType;
use App\Models\DynamicQuestion;
use Illuminate\Database\Seeder;
use App\Models\DynamicQuestionable;

class DynamicQuestions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DynamicQuestion::truncate();
        DynamicQuestionable::truncate();

        $qTypes = [
            "checkbox" => [
                "title" => "Q1",
                "question" => "How many tails do you have?",
                "options" => [
                    [
                        "label" => "One",
                        "value" => 1
                    ],
                    [
                        "label" => "Two",
                        "value" => 2
                    ],
                    [
                        "label" => "Three",
                        "value" => 3
                    ]
                ]
            ],
            "radio" => [
                "title" => "Q2",
                "question" => "How many tails do you have?",
                "options" => [
                    [
                        "label" => "One",
                        "value" => 1
                    ],
                    [
                        "label" => "Two",
                        "value" => 2
                    ],
                    [
                        "label" => "Three",
                        "value" => 3
                    ]
                ]
            ],
            "select" => [
                "title" => "Q3",
                "question" => "How many tails do you have?",
                "options" => [
                    [
                        "label" => "One",
                        "value" => 1
                    ],
                    [
                        "label" => "Two",
                        "value" => 2
                    ],
                    [
                        "label" => "Three",
                        "value" => 3
                    ]
                ]
            ],
            "select_brand" => [
                "title" => "Select your brand sis",
                "question" => "Select your brand sis",
                "options" => [],
            ],
            "select_platform" => [
                "title" => "Platforms?",
                "question" => "IN which chuchu platforms",
                "options" => [],
            ],
            "textfield" => [
                "title" => "History",
                "question" => "History",
                "options" => [],
            ],
            "url" => [
                "title" => "URL",
                "question" => "Website URL",
                "options" => [],
            ],
            "textarea1" => [
                "title" => "Textarea",
                "question" => "Text area",
                "options" => [],
            ],
            "textarea2" => [
                "title" => "Details",
                "question" => "More details?",
                "options" => [],
            ],
            "upload_single" => [
                "title" => "Need file",
                "question" => "Upload 1 file?",
                "options" => [],
            ],
            "upload_multiple" => [
                "title" => "Need more files",
                "question" => "Upload more files?",
                "options" => [],
            ],
        ];

        $projectType = ProjectType::first();
        $taskType = TaskType::first();

        $projectType->questions()->delete();
        $taskType->questions()->delete();

        foreach ($qTypes as $key => $value) {
            $projectType->questions()->create([
                'title' => $value['title'],
                'question' => $value['question'],
                'type' => $key,
                'options' => ($value['options']),
            ]);
            $taskType->questions()->create([
                'title' => $value['title'],
                'question' => $value['question'],
                'type' => $key,
                'options' => ($value['options']),
            ]);
        }

        $array = array(1, 2, 3);
        $brand = Brand::find(12);

        foreach ($array as $value) {
            Task::create([
                'organization_id' => $brand->organization->id,
                'brand_id' => $brand->id,
                'title' => 'task title ' . $value,
                'description' => 'task description ' . $value,
                'video_walkthrough' => 'task video_walkthroug ' . $value,
                'resources' => 'task resources' . $value,
                'priority' => 'low',
                'status' => 'active',
                'task_type_id' => 1,
                'warnings' => '123',
                'dynamic_questions' => [
                    [
                        'question_id' => 2,
                        'answers' => ['test'],
                    ],
                    [
                        'question_id' => 8,
                        'answers' => ['test'],
                    ],
                    [
                        'question_id' => 10,
                        'answers' => ['test'],
                    ],
                ],
            ]);
        }

        foreach ($array as $value) {
            Project::create([
                'organization_id' => $brand->organization->id,
                'brand_id' => $brand->id,
                'brand_name' => 'task title ' . $value,
                'business_description' => 'task description ' . $value,
                'web_design_inspiration' => 'task resources' . $value,
                'disliked_designs' => 'low',
                'website_objective' => 'low',
                'graphic_files' => 'active',
                'sitemap' => 'active',
                'wireframes' => 'active',
                'content_management_system' => 'active',
                'third_party_tools' => 'active',
                'dynamic_questions' => [
                    [
                        'question_id' => 1,
                        'answers' => ['test'],
                    ],
                    [
                        'question_id' => 7,
                        'answers' => ['test'],
                    ],
                    [
                        'question_id' => 9,
                        'answers' => ['test'],
                    ],
                ],
            ]);
        }
    }
}
