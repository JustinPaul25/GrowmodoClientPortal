<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function destroy(User $user)
    {
        $current_user = auth()->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($user->delete()) {
            return JsonResponse::make([], JsonResponse::SUCCESS,  'User Deleted');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'User Deletetion Failed!');
        }
    }
}
