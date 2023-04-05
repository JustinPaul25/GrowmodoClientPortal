<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    //

    public function editAccount(Request $request)
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id, //(),
            'title' => 'required|string|max:255',
            'phone_number' => 'nullable|max:20',
            'phone_country_code' => 'nullable|max:4',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = $request->user();

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->title = $request->title;
        $user->phone_number = $request->phone_number;
        $user->phone_country_code = $request->phone_country_code;

        if ($user->save()) {
            return JsonResponse::make($user, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    // public function

}
