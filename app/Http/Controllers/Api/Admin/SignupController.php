<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Option;
use App\Models\Project;
use App\Helpers\AsanaApi;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\OrganizationUsers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SignupController extends Controller
{
    var $asanaApi = null;


    public function __construct()
    {
        $this->asanaApi = new AsanaApi();
    }
    public function confirmation(User $user)
    {
        // $auth_roles = array('organization_owner', 'organization_admin', 'organization_billing');
        // // if ( ! ( in_array($user->roles->first()->name, $auth_roles) ) )
        if ( ! $user->hasRole('organization_admin') )
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $user->status = 'active';
        $user->client_signup_confirmed = true;
        $user->save();

        $organization = $user->getOrganizations()->first();

        if( empty($organization->id) )
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

        $subscription = $organization->subscriptions()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        if($subscription->status == 'active')
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization has an active subscription already.');

        $team_id = Option::where('option_name', 'asana_api_default_team_id')->first();

        $organization->status = 'active';

        if(! $organization->save())
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown error! organization status failed to update');

        $subscription->status = 'active';

        if($subscription->save()) {
            $subscription->refresh();
            $user->refresh();

            try{
                $asanaproject = $this->asanaApi->post('projects', [
                    "data" => [
                        "approval_status" => "pending",
                        "completed" => false,
                        "followers" => [
                            "1200308152118324", "1202188382832946", "1202181145649995", "1203592325821504", "1200322128282281", "204399971618995", "204399971618985"
                        ],
                        "html_notes" => "<body>" . $organization->title . "</body>",
                        "liked" => false,
                        "name" => $organization->title,
                        "team" => $team_id->option_value,
                        "resource_subtype" => "default_project",
                    ]
                ]);
            } catch (Exception $e) {
                return JsonResponse::make([], JsonResponse::SERVICE_UNAVAILABLE, 'Failed to create project.', 503);
            }

            $organization->asana_gid = $team_id->option_value;

            return [$asanaproject, gettype($asanaproject)];
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown error! subscription status failed to update');
        }
    }
}
