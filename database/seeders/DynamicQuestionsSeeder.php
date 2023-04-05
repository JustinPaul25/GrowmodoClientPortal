<?php

namespace Database\Seeders;

use App\Models\DynamicQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DynamicQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DynamicQuestion::truncate();

        $json = File::get("database/data/dynamic_questions.json");
        $questions = json_decode($json);

        foreach ($questions as $key => $value) {
            DynamicQuestion::create([
                "title" => $value->title,
                "question" => $value->question,
                "placeholder" => $value->placeholder,
                "type" => $value->type,
                "required" => $value->required,
                "options" => $value->options,
                "alternative_if" => $value->alternative_if,
                "alternative_question" => $value->alternative_question,
                "alternative_placeholder" => $value->alternative_placeholder,
                "alternative_type" => $value->alternative_type,
                "alternative_options" => $value->alternative_options,
                "show_in_overview" => $value->show_in_overview,
            ]);
        }
    }
}
