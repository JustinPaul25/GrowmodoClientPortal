<?php
namespace App\Http\Controllers\Api;

use DB;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

if( !function_exists('xz_org_response') ){
    function xz_org_response( $user_id ){
        // Custom Organization Data
        $user_orgs = DB::table('users')->join('organization_users', 'users.id', '=', 'organization_users.user_id')
        ->select('users.id', 'organization_users.organization_id');
        $user_orgs->where( 'user_id', 'LIKE', '' . $user_id . '' );
        $user_orgs->orderBy('organization_id', 'ASC');

        $user_orgs_to_array = $user_orgs->get()->toArray();
        $custom_org_response = array();

        foreach($user_orgs_to_array as $user_org){
            $org_id = $user_org->organization_id;
            $organization = Organization::find($org_id);
            if ( empty ($organization->id))
                continue;
            $custom_org_response[] = $organization->toArray();
        }
        if( !empty($custom_org_response) ){
            return $custom_org_response;
        } else{
            return $custom_org_response;
        }

    }
}

if( !function_exists('xz_qa_response') ){
    function xz_qa_response( $user_id, $type = 'onboardings' ){
        // Custom Organization Data
        $user_qas = DB::table('users')->join('user_' . $type, 'users.id', '=', 'user_' . $type . '.user_id')
        ->select('user_' . $type . '.question', 'user_' . $type . '.answer');
        $user_qas->where( 'user_id', 'LIKE', '' . $user_id . '' );
        $user_qas->orderBy('user_' . $type . '.question', 'ASC');

        $user_qas_to_array = $user_qas->get()->toArray();
        $custom_qa_response = array();

        foreach($user_qas_to_array as $user_qa){
            // $custom_qa_response[] = $user_qa;
            if(json_decode($user_qa->answer)) {
                $custom_qa_response[$user_qa->question] = json_decode($user_qa->answer);
            } else {
                $custom_qa_response[$user_qa->question] = $user_qa->answer;
            }
        }
        if( !empty($custom_qa_response) ){
            return $custom_qa_response;
        } else{
            return $custom_qa_response;
        }

    }
}

if( !function_exists('xz_if_exist_username') ){
    function xz_if_exist_username( $username ){

        $str = strtolower($username);

        $users = User::select("id", "username", "email")
        ->where(DB::raw('lower(username)'), $str)
        ->get();

        if( !empty($users->first()->id) ){
            return $username;
        }
    }
}

if( !function_exists('xz_if_user_owner_or_admin_on_org') ){
    function xz_if_user_owner_or_admin_on_org( $user_id, $org_id ){
        $user = User::find($user_id);

        $auth_roles = array('organization_admin');

        if ( ! ( in_array($user->roles->first()->name, $auth_roles) ) )
            return '';

        if($user->roles->first()->name == 'organization_admin'){
            $user_org = DB::table('organization_users')->join('users', 'users.id', '=', 'organization_users.user_id')
            ->select('users.id', 'organization_users.user_id', 'organization_users.organization_id');
            $user_org->where( 'user_id', 'LIKE', '' . $user_id . '' );
            $user_org->where( 'organization_id', 'LIKE', '' . $org_id . '' );

            $user_orgs = $user_org->get();

            if( empty($user_orgs->first()->organization_id) )
                return '';

            return $user_orgs->first()->organization_id;
        }
        if($user->roles->first()->name == 'organization_admin'){
            $organization = Organization::find($org_id);

            if( empty($organization->id) )
            return '';

            if ( $organization->owner_id == $user_id ){
                return $organization->id;
            } else{
                $user_org = DB::table('organization_users')->join('users', 'users.id', '=', 'organization_users.user_id')
                ->select('users.id', 'organization_users.user_id', 'organization_users.organization_id');
                $user_org->where( 'user_id', 'LIKE', '' . $user_id . '' );
                $user_org->where( 'organization_id', 'LIKE', '' . $org_id . '' );

                $user_orgs = $user_org->get();

                if( empty($user_orgs->first()->organization_id) )
                    return '';

                return $user_orgs->first()->organization_id;
            }

        }

    }
}

if( !function_exists('xz_if_user_owner_on_org') ){
    function xz_if_user_owner_on_org( $user_id, $org_id ){
        $user = User::find($user_id);

        $auth_roles = array('organization_admin');

        if ( ! ( in_array($user->roles->first()->name, $auth_roles) ) )
            return '';

        if($user->roles->first()->name == 'organization_admin'){
            $organization = Organization::find($org_id);

            if( empty($organization->id) )
            return '';

            if ( $organization->owner_id == $user_id ){
                return $organization->id;
            } else{
                $user_org = DB::table('organization_users')->join('users', 'users.id', '=', 'organization_users.user_id')
                ->select('users.id', 'organization_users.user_id', 'organization_users.organization_id');
                $user_org->where( 'user_id', 'LIKE', '' . $user_id . '' );
                $user_org->where( 'organization_id', 'LIKE', '' . $org_id . '' );

                $user_orgs = $user_org->get();

                if( empty($user_orgs->first()->organization_id) )
                    return '';

                return $user_orgs->first()->organization_id;
            }

        }

    }
}
