<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email|max:255',
            'username' => 'required|unique:users,email|max:1000',
            'password' => 'required|string|max:255',
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'organization_name' => 'required|string',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $client = new User();
        $client->email = $request->email;
        $client->username = $request->username;
        $client->password = Hash::make($request->password);
        $client->firstname = $request->firstname;
        $client->lastname = $request->lastname;

        if ($client->save()) {
            $client->assignRole('organization_admin');
            $organization = new Organization();
            $organization->owner_id = $client->id;
            $organization->title = $request->organization_name;
            $organization->save();

            $client = User::with('organization')->find($client->id);

            $response = $client->toArray();
            $response['organization_id'] = $organization->id;

            return JsonResponse::make($response, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }

    }



    public function registerUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email|max:255',
            'username' => 'required|unique:users,email|max:1000',
            'password' => 'required|string|max:255',
            'role' => 'required|exists:roles,name',
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'organization_id' => 'exists:organizations,id',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = new User();
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->title = $request->role;

        if ($user->save()) {
            $user->assignRole(define_role($request->role));

            if (intval($request->organization_id) > 0) {
                $organization = Organization::find($request->organization_id);
                $organization->employees()->attach($user);
            }

            return JsonResponse::make($user, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }

    }


    function updateUser(Request $request, $id) {

        $user = User::find($id);

        if (empty ($user->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND);

        $request_data = $this->getTrueRequestData($request);

        // return $request_data;
        $rules = [
            'username' => 'required|unique:users,username,' . $id,
            'password' => 'string|min:8',
            'firstname' => 'max:255',
            'lastname' => 'max:255',
            // 'role' => 'exists:roles,name',
            'email' => 'required|email|unique:users,email,' . $id,
        ];

        if (! empty($request->role)) {
            $rules['role'] = 'exists:roles,name';
        }

        $validator = Validator::make($request_data, $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if (! empty($request_data['firstname']))
            $user->firstname = $request_data['firstname'];

        if (! empty($request_data['lastname']))
            $user->lastname = $request_data['lastname'];

        $user->username = $request_data['username'];

        if (! empty($request_data['password']))
            $user->password = Hash::make($request_data['password']);

        if (! empty($request_data['role']))
            $user->title = $request_data['role'];

        $user->email = $request_data['email'];

        if ($user->save()) {

            if (! empty($request_data['role'])) {
                $user->roles()->detach();
                $user->assignRole(define_role($request_data['role']));
            }

            $userd = $user->toArray();
            // $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);
            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);
            $response = [
                'user' => $userd,
                'role' => collect($user->getRoleNames())->first(),
                // 'organization' => $organization->with('social_accounts')->get(),
                // 'token' => $token
            ];
            return JsonResponse::make($response, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function status(User $user)
    {
        $org = $user->organization->first();
        if($org) {
            $subscription_status = $org->subscriptions ? $org->subscriptions->status : 'no subscription';
            $org_status = $org->status;
        } else {
            $subscription_status = 'no subscription';
            $org_status = 'no organization';
        }

        $response = [
            'user' => $user->status,
            'organization' => $org_status,
            'subscription' => $subscription_status,
        ];

        return JsonResponse::make($response, JsonResponse::SUCCESS);
    }

}
