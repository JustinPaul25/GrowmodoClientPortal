<?php

namespace App\Http\Controllers\Api;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Events\Registered;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\UserOnboarding;
use App\Models\UserOffboarding;
use Illuminate\Validation\Rule;
use App\Models\OrganizationUsers;
use App\Events\OrganizationNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\InviteTokenVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class OrganizationUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $page = $request->page ?? 0;
        $skip = 0;

        if ($skip >= 1) {
            $skip = 0;
        } else {
            $skip = ($page - 1) * $per_page;
        }

        $search = $request->search;
        $status = $request->status;

        $current_user = $request->user();

        $validSortBy = ['users_firstname', 'users_lastname', 'users_username', 'users_email', 'organizations_id', 'organizations_title'];

        if (in_array(strtolower($request->input('sortBy')), $validSortBy)) {
            $sort = str_replace("_", ".", strtolower($request->input('sortBy')));
        } else {
            $sort = 'organization_id';
        }

        if (strtolower($request->input('sortType')) === 'desc') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if (empty($current_user->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND);

        $users = DB::table('users')->join('organization_users', 'users.id', '=', 'organization_users.user_id')->join('organizations', 'organizations.id', '=', 'organization_users.organization_id')
            ->select('users.id', 'users.organization_role', 'users.firstname', 'users.lastname', 'users.username', 'users.email', 'users.email_verified_at', 'users.current_team_id', 'users.profile_photo_path', 'users.status', 'users.created_at', 'users.updated_at', 'users.phone_country_code', 'users.phone_number', 'users.deleted_at', 'users.signup_token', 'organization_users.organization_id', 'organization_users.user_id', 'organizations.owner_id');

        $users->where('organizations.owner_id', 'LIKE', '' . $current_user->id . '');

        if (!empty($status)) {
            $users->where('users.status', 'LIKE', '' . $status . '');
        }
        if (!empty($search)) {
            $users->where('username', 'LIKE', '%' . $search . '%')
                ->orWhere('firstname', 'LIKE', '%' . $search . '%')
                ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                ->orWhere('email', 'LIKE', '%' . $search . '%');
        }

        $users->orderBy($sort, $order)->skip($skip)->take($per_page);

        $response = $users->get()->toArray();

        $custom_response = array();

        foreach ($response as $user) {
            $org_id = $user->organization_id;

            $user_f = User::find($user->id);
            if (!empty($user_f->id))
                $user->role = collect($user_f->getRoleNames())->first();

            $user_wout_org_id = $user;
            unset($user_wout_org_id->organization_id);

            $custom_response[$org_id][] = $user_wout_org_id;
        }

        return JsonResponse::make($custom_response, JsonResponse::SUCCESS);
    }

    public function orgusers(Request $request, $id = null)
    {
        $validSortBy = ['firstname', 'lastname', 'username', 'email'];

        if ($request->input('sortBy')) {
            $sort = strtolower($request->input('sortBy'));
            if (in_array($sort, $validSortBy)) {
                $sort = 'users.' . $sort;
            } else {
                $sort = 'users.id';
            }
        } else {
            $sort = 'users.id';
        }

        if (strtolower($request->input('sortType')) === 'desc') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        $current_user = $request->user();
        if (empty($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $org = xz_org_response($current_user->id);

        if ($org[0]['id'] != $id)
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization Users not found.');

        $users = User::select(
            'users.id',
            'users.firstname',
            'users.lastname',
            'users.username',
            'users.email',
            'users.email_verified_at',
            'users.current_team_id',
            'users.profile_photo_path',
            'users.status',
            'users.created_at',
            'users.updated_at',
            'users.phone_country_code',
            'users.phone_number',
            'users.title',
            'users.deleted_at',
            'users.signup_token',
            'organization_users.organization_id',
        )->join('organization_users', 'users.id', '=', 'organization_users.user_id')->orderBy($sort, $order);

        $organization = Organization::find($id);

        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        if (!empty($id)) {
            $users->where('organization_id', 'LIKE', '' . $id . '');
        }
        $response = $users->get();


        $custom_response = array();

        foreach ($response as $user) {
            $user_f = User::find($user->id);
            if (!empty($user_f->id))
                $user->role = collect($user_f->getRoleNames())->first();

            $custom_response[] = $user;
        }

        if (empty($users))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Users not found.');

        return JsonResponse::make($custom_response, JsonResponse::SUCCESS);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'password' => 'required|string|min:8',
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'role' => [
                'required',
                Rule::in(invite_user_role_list()),
            ],
            'email' => 'required|email:rfc,dns|indisposable|unique:users,email',
        ]);

        if (!empty(xz_if_exist_username($request->username)))
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, ['username' => array('The username has already been taken.')]);

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL))
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, ['email' => array('The email is invalid.')]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = auth()->user();

        $role = $user->getRoleNames()[0];

        if ($role !== 'organization_admin')
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $organization = $user->getOrganizations()[0];

        $user = new User;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $user->invite_token = base64_encode(Hash::make('invite_token|' . $request->email . '|' . $request->firstname . '|' . Carbon::now()));
        $user->signup_step = 'verify-email';
        $user->title = define_invite_user_role($request->role);
        $user->organization_role = $request->role;

        if ($user->save()) {
            $organization->employees()->attach($user);

            $user->assignRole($request->role);

            $userd = User::find($user->id)->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                'organization' => xz_org_response($user->id),
            ];

            event(new Registered($user, $request->headers->get('origin')));
            // $user->notify(new InviteTokenVerifyEmail($request->headers->get('origin')));

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $request->all()])
                ->event('invite user')
                ->log('Add/Invite user in organization');

            return JsonResponse::make($response, JsonResponse::SUCCESS, 'User added to organization.');
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function resendInvitation(Request $request)
    {
        $rules = [
            'email' => 'required|string|max:255|exists:users,email',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        // $inviteeUser = User::where('email', $request->email)->first();

        // if ($inviteeUser)

        $user = User::where('email', $request->email)->first();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $user->invite_token = base64_encode(Hash::make('invite_token|' . $request->email . '|' . $request->firstname . '|' . Carbon::now()));
            $user->save();
            $user->refresh();

            $user->notify(new InviteTokenVerifyEmail($request->headers->get('origin')));

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->event('resend invitation')
                ->log('Resend Invitation to User');

            return JsonResponse::make([], JsonResponse::SUCCESS, "Email Invitation link has been sent!");
        } else {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, "Email address provided already verified");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        $user = User::find($id);

        if (empty($user->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization User not found.');

        $userd = $user->toArray();

        unset($userd['roles'], $userd['employer'], $userd['invite_token'], $userd['organization']);

        $userd['role'] = collect($user->getRoleNames())->first();

        $response = [
            'user' => $userd,
            'organization' => xz_org_response($user->id),
        ];

        return JsonResponse::make($response, JsonResponse::SUCCESS);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = null)
    {
        $admin = auth()->user();
        $admin_role = auth()->user()->getRoleNames()[0];

        if ($admin_role !== 'organization_admin')
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $admin_org = $admin->getOrganizations()[0];

        $user = User::find($id);
        $user_orgs = OrganizationUsers::where('user_id', 'LIKE', '' . $user->id . '')->get()->toArray();

        if ($admin_org->id !== $user_orgs[0]['organization_id'])
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, "Unauthorized to update user.");

        $validator = Validator::make($request->all(), [
            'username' => 'unique:users,username,' . $id,
            'password' => 'string|min:8',
            'firstname' => 'max:255',
            'lastname' => 'max:255',
            'email' => 'email:rfc,dns|indisposable|unique:users,email,' . $id,
        ]);

        if (!empty($request->username)) {
            if (strtolower($user->username) != strtolower($request->username)) {
                if (!empty(xz_if_exist_username($request->username))) {
                    return JsonResponse::make([], JsonResponse::INVALID_PARAMS, ['username' => array('The username has already been taken.')]);
                }
            }
        }

        if (!empty($request->email)) {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL))
                return JsonResponse::make([], JsonResponse::INVALID_PARAMS, ['email' => array('The email is invalid.')]);
        }

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $organization = $admin_org;

        // Get rent user organization id
        if (!empty($request->user()->organization)) {
            $organization = $request->user()->organization()->first();
        } elseif (!empty($request->user()->employer)) {
            $organization = $request->user()->employer()->first();
        } else {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');
        }

        if (!empty($request->firstname))
            $user->firstname = $request->firstname;
        if (!empty($request->lastname))
            $user->lastname = $request->lastname;
        if (!empty($request->username))
            $user->username = $request->username;
        if (!empty($request->email))
            $user->email = $request->email;
        if (!empty($request->role)) {
            $user->organization_role = $request->role;
            $user->title = define_invite_user_role($request->role);;
        }

        if (!empty($request->password))
            $user->password = Hash::make($request->password);

        if ($user->save()) {

            if (!empty($request->role)) {
                $user->roles()->detach();
                $user->assignRole($request->role);
            }

            $userd = $user->toArray();

            unset($userd['roles'], $userd['employer'], $userd['invite_token'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                'organization' => xz_org_response($user->id),
            ];

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(["attributes" => $request->all()])
                ->log('Update organization user.');

            return JsonResponse::make($response, JsonResponse::SUCCESS, "User Updated");
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
    public function delete(Request $request, $id)
    {
        //
        $organization = null;

        $user = User::find($id);
        if (empty($user->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization User not found.');

        $current_user = $request->user();
        if (empty($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $cur_user_orgs = array();
        $cur_user_orgs_if = array();
        $cur_user_orgs_if_ = '';

        $user_org = OrganizationUsers::where('user_id', 'LIKE', '' . $user->id . '')->get()->toArray();

        foreach ($user_org as $user_) {
            if ($current_user->roles->first()->name == 'organization_admin') {
                $org = Organization::find($user_['organization_id']);
                if ($org->owner_id != $user->id) {
                    array_push($cur_user_orgs_if, $user_['organization_id']);
                } else {
                    unset($cur_user_orgs_if);
                    $cur_user_orgs_if = array();
                    break;
                }
            } else {
                array_push($cur_user_orgs_if, $user_['organization_id']);
            }
        }

        if (empty($cur_user_orgs))
            $cur_user_orgs = $cur_user_orgs_if;

        foreach ($cur_user_orgs as $org_id) {
            if (!empty(xz_if_user_owner_or_admin_on_org($current_user->id, $org_id))) {
                $can_update_this = '1';
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->event('deleted')
            ->withProperties(["attributes" => ["user_email" => $user->email]])
            ->log('User named' . $user->firstname . ' ' . $user->lastname . 'deleted.');

        if (empty($can_update_this))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $s = $user->delete();

        return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'User has been deleted.' : 'Unable to delete user');
    }


    /**
     * Display onboarding users the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function onboardingShow(Request $request,  $id = null)
    {
        $per_page = $request->per_page ?? 999;
        $page = $request->page ?? 0;
        $skip = 0;

        if ($skip >= 1) {
            $skip = 0;
        } else {
            $skip = ($page - 1) * $per_page;
        }

        $users = DB::table('users')->join('organization_users', 'users.id', '=', 'organization_users.user_id')
            ->select('users.id', 'users.firstname', 'users.lastname', 'users.username', 'users.email', 'users.email_verified_at', 'users.current_team_id', 'users.profile_photo_path', 'users.status', 'users.created_at', 'users.updated_at', 'users.phone_country_code', 'users.phone_number', 'users.deleted_at', 'users.signup_token', 'users.onboarded', 'organization_users.organization_id', 'organization_users.user_id');
        $users->whereNull('onboarded');

        $users->distinct('user_id')->orderBy('user_id', 'ASC')->skip($skip)->take($per_page);

        $response = $users->get()->toArray();

        $custom_response = array();

        foreach ($response as $user) {
            $user_wout_org_id = $user;

            $user_d = User::find($user->id);
            if (empty($user_d->id))
                continue;

            $userd = $user_d->toArray();

            unset($userd['roles'], $userd['employer'], $userd['invite_token'], $userd['organization']);

            $user_role = collect($user_d->getRoleNames())->first();

            if (!empty($user_role)) {
                if ($user_role == 'organization_admin') {
                    $user_wout_org_id->role = $user_role;
                } else {
                    continue;
                }
            } else {
                continue;
            }

            $user_wout_org_id->organization = xz_org_response($user->id);

            unset($custom_org_response);
            unset($user_wout_org_id->organization_id);

            $custom_response['users'][] = $user_wout_org_id;
            $user_role = '';
        }

        return JsonResponse::make($custom_response, JsonResponse::SUCCESS);
    }

    public function onboardingStore(Request $request)
    {
        $current_user = $request->user();

        if ($current_user) {
            if (isset($request->questions)) {
                $current_user->onboardings()->delete();
            }
            if (!empty($request->questions)) {
                foreach ($request->questions as $key => $answer) {
                    $user_ob = new UserOnboarding();
                    $user_ob->user_id = $current_user->id;
                    $user_ob->question = $key;
                    if (is_array($answer)) {
                        $user_ob->answer = json_encode($answer);
                    } else {
                        $user_ob->answer = $answer;
                    }
                    $user_ob->save();
                }

                $current_user->onboarded = now();
            }
            if ($current_user->save()) {
                $userd = $current_user->toArray();

                unset($userd['roles'], $userd['employer'], $userd['invite_token'], $userd['organization']);

                $userd['role'] = collect($current_user->getRoleNames())->first();

                $response = [
                    'user' => $userd,
                    'organization' => xz_org_response($current_user->id),
                    'onboarding' => xz_qa_response($current_user->id, 'onboardings'),
                ];

                return JsonResponse::make($response, JsonResponse::SUCCESS);
            } else {
                return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
            }
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function offboardingStore(Request $request)
    {
        $current_user = $request->user();

        $orgs = Organization::where('owner_id', 'LIKE', '' . $current_user->id . '')->get();

        if (!empty($orgs)) {
            foreach ($orgs as $org) {
                $org->status = 'cancelled';
                $org->save();

                $subscription = Subscription::where('organization_id', 'LIKE', '' . $org->id . '')->get()->first();

                if (empty($subscription->id))
                    continue;

                $subscription->status = 'cancelled';
                $subscription->cancelled_at = now();
                $subscription->save();
            }
        }

        if ($current_user->id) {
            if (isset($request->answer)) {
                $current_user->offboardings()->delete();
            }
            if (!empty($request->answer)) {
                $user_ob = new UserOffboarding();
                $user_ob->user_id = $current_user->id;
                $user_ob->answer = $request->answer;
                $user_ob->save();
                $user_ob->refresh();
            }

            $userd = $current_user->toArray();

            unset($userd['roles'], $userd['employer'], $userd['invite_token'], $userd['organization']);
            unset($user_ob['id'], $user_ob['user_id']);

            $userd['role'] = collect($current_user->getRoleNames())->first();

            $response = [
                'offboarding' => $user_ob,
                'user' => $userd,
                'organization' => xz_org_response($current_user->id),
            ];

            return JsonResponse::make($response, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }
}
