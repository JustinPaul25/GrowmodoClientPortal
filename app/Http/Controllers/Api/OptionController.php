<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index(Request $request) {
        $options = Option::where('public', true)->get();

        $__options = [];

        foreach($options as $o => $option) {
            $__options[$option->option_name] = $option->option_value;
        }

        return JsonResponse::make($__options);
    }
}
