<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\AsanaApi;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\GoogleClient;
use App\Helpers\JsonResponse;
use App\Models\GoogleDriveFolder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AuthController extends Controller
{
    //
    var $asanaApi = null;

    public function __construct()
    {
        $this->asanaApi = new AsanaApi();
    }

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|max:50',
            'password' => 'required|string|max:50',
        ];

        $attempt_data = [];

        if (filter_var($request['username'], FILTER_VALIDATE_EMAIL)) {
            $rules['username'] .= '|email';
            $attempt_data['email'] = $request['username'];
        } else {
            // $rules['username'] .= '|exists:users,username';
            $attempt_data['username'] = $request['username'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return JsonResponse::make($validator->errors(), JsonResponse::INVALID_PARAMS);
        }

        /* if (! $this->verifyReCaptcha($request['gcaptcha'])) {
            return JsonResponse::make([], JsonResponse::INVALID_GCAPTCHA, 'Invalid reCaptcha response. Please try again.');
        } */

        $attempt_data['password'] = $request['password'];


        // if ($this->hasTooManyLoginAttempts($request)) {
        //     $this->fireLockoutEvent($request);
        //     return $this->sendLockoutResponse($request);
        // }

        if (\Auth::attempt($attempt_data)) {
            // $this->clearLoginAttempts($request);
            $user = \Auth::user();

            $token = $user->createToken('Password Grant Client')->accessToken;
            $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);

            $userd = $user->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                // 'role' => collect($user->getRoleNames())->first(),
                'organization' => xz_org_response($user->id),
                'token' => $token
            ];

            activity()
                ->causedBy($user)
                ->event('login')
                ->log('User logged in.');

            return JsonResponse::make($response);
        } else {
            // $this->incrementLoginAttempts($request);
            return JsonResponse::make([], JsonResponse::WRONG_CREDENTIALS);
        }
    }

    public function loginViaToken(Request $request, GoogleClient $googleClient)
    {
        $rules = [
            'payment_token' => 'required|exists:users,payment_token',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = User::where('payment_token', $request->payment_token)->first();

        Auth::login($user);

        $token = $user->createToken('Password Grant Client')->accessToken;
        $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);

        $userd = $user->toArray();

        unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

        $userd['role'] = collect($user->getRoleNames())->first();

        //$organization->asana_gid = $team_id->option_value;

        $organization = Organization::where('owner_id', $user->id)->first();

        $googleClient->initDriveService();
        $parentFolder = $googleClient->createFolder(
            $organization->title . ' - Projects',
            $organization,
            1,
        );

        $resourcesFolder = $googleClient->createFolder(
            'Assets and Resources (from ' . $organization->title . ')',
            $organization,
            1,
            $parentFolder->id,
        );

        $outputsFolder = $googleClient->createFolder(
            'Outputs and Deliverables (from Growmodo)',
            $organization,
            1,
            $parentFolder->id,
        );

        $brandsFolder = $googleClient->createFolder(
            'Brands',
            $organization,
            1,
            $outputsFolder->id,
        );

        $googleClient->shareFolder($parentFolder->folder_id, $user->email);

        try {
            $asanaproject = $this->asanaApi->post('projects', [
                "data" => [
                    "name" => $organization->title,
                    "notes" => "This project is generated by Growmodo Client Portal",
                    "team" => env('ASANA_API_DEFAULT_TEAM_ID'),
                ]
            ], []);

            $sections = array('Task Queue', 'Working On It', 'Need Approval', 'Stuck', 'On Hold', 'Complete Task');

            foreach ($sections as $section) {
                $this->asanaApi->post('projects/' . $asanaproject['data']['gid'] . '/sections', [
                    "data" => [
                        "name" => $section,
                    ]
                ]);
            }

            $this->asanaApi->deleteFirstSection($asanaproject['data']['gid'], []);

            $organization->update([
                'asana_gid' => $asanaproject['data']['gid'],
                'folder_id' => $parentFolder->id,
                'resources_folder_id' => $resourcesFolder->id,
                'outputs_folder_id' => $outputsFolder->id,
                'brands_folder_id' => $brandsFolder->id,

            ]);

            activity()
                ->causedBy($user)
                ->event('token-login')
                ->log('User logged in via token.');
        } catch (Exception $e) {
            return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to create project.', 503);
        }

        $user->payment_token = null;
        $user->save();

        $response = [
            'user' => $userd,
            'organization' => xz_org_response($user->id),
            'token' => $token
        ];

        return JsonResponse::make($response);
    }

    public function me()
    {
        if (Auth::check()) {
            $user = Auth::user();

            // $token = $user->createToken('Password Grant Client')->accessToken;
            // if ($user->hasRole('store_staff')) {
            // }

            $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);

            $userd = $user->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);
            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd, //UserResource::make($user),
                // 'role' => collect($user->getRoleNames())->first(),
                'organization' => xz_org_response($user->id),
            ];


            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        }
    }


    public function updateMyPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|max:50',
            //'new_password' => 'required|min:8',
            'new_password' => [
                'required',
                'string',
                'max:50',
                RulesPassword::min(8), //->mixedCase()->numbers()->symbols()
            ]
        ]);

        if ($validator->fails()) {
            // if ($validator->errors()->first('password'))
            // {
            //     $password = ['password'=>['Must atleast minimum of 8 characters, contain atleast one lowercase letter,one uppercase letter,number and special character.']];
            //     return JsonResponse::make($password, JsonResponse::INVALID_PARAMS);
            // }

            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());
        }

        $user = auth()->user();

        if (!Hash::check($request['old_password'], $user->password)) {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Wrong Old Password');
        }

        $user->password = Hash::make($request['new_password']);

        if ($user->save()) {
            activity()
                ->causedBy(auth()->user())
                ->event('updated')
                ->log('Updated Password.');

            return JsonResponse::make($user->with('roles,permissions,group,store'), JsonResponse::SUCCESS, 'Successfully updated.');
        } else {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Unable to update.');
        }
    }

    public function logout(Request $request)
    {
        activity()
            ->causedBy(auth()->user())
            ->event('logout')
            ->log('User logged out.');

        return JsonResponse::make($request->user()->token()->revoke());
    }
}
