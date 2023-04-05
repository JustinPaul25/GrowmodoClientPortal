<?php

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

if (!function_exists('convert_file_size')) {
    function convert_file_size($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if (!function_exists('get_product_id')) {
    function get_product_id($talent, $plan)
    {
        $product_id = [
            '1' => [
                '1' => 'price_1MRszWBGxhpCJs2lSVIgN1sA',
                '2' => 'price_1MRt2LBGxhpCJs2lMs191OAq',
                '3' => 'price_1MRt39BGxhpCJs2lgg8N7Lr7',
                '4' => 'price_1MRt3qBGxhpCJs2l4DLJsl9S',
            ],
            '2' => [
                '1' => 'price_1MRt71BGxhpCJs2l6yHnuVUN',
                '2' => 'price_1MRt71BGxhpCJs2lbTSBmLvA',
                '3' => 'price_1MRt71BGxhpCJs2lAY2SAdrJ',
                '4' => 'price_1MRt71BGxhpCJs2l2RnlatGS',
            ],
            '3' => [
                '1' => 'price_1MRt9ZBGxhpCJs2llwBeyLBA',
                '2' => 'price_1MRt9ZBGxhpCJs2lrdfpuNEk',
                '3' => 'price_1MRt9ZBGxhpCJs2lZi1pzmuu',
                '4' => 'price_1MRt9ZBGxhpCJs2lHDP9wMfN',
            ],
            '4' => [
                '1' => 'price_1MRtC8BGxhpCJs2lANmT6Z4v',
                '2' => 'price_1MRtC8BGxhpCJs2lntyiqp8v',
                '3' => 'price_1MRtC8BGxhpCJs2l7LUp70l7',
                '4' => 'price_1MRtC8BGxhpCJs2l12NqwWPd',
            ],
            '5' => [
                '1' => 'price_1MRtEVBGxhpCJs2lEDI6OYbK',
                '2' => 'price_1MRtEVBGxhpCJs2lGFNd8Em5',
                '3' => 'price_1MRtEVBGxhpCJs2la3fEob2q',
                '4' => 'price_1MRtEVBGxhpCJs2lkuPAAuUn',
            ]
        ];

        return $product_id[$talent][$plan];
    }
}

if (!function_exists('define_role')) {
    function define_role($role)
    {
        $admin = array(
            'Founder/Owner',
            'C-Level Executive',
            'Marketing/Growth Manager',
            'Product Manager',
            'Operations Manager',
            'Project Manager',
        );

        $member = array(
            'Developer/Engineer',
            'UX/UI Designer',
            'HR Manager',
            'Freelancer/Consultant',
            'Others',
        );

        if (in_array($role, $admin)) {
            return 'organization_admin';
        }

        if (in_array($role, $member)) {
            return 'organization_member';
        }

        return 'organization_billing';
    }
}

if (!function_exists('title_list')) {
    function title_list()
    {
        return array(
            'Founder/Owner',
            'C-Level Executive',
            'Marketing/Growth Manager',
            'Product Manager',
            'Operations Manager',
            'Project Manager',
            'Sales Manager',
            'Developer/Engineer',
            'UX/UI Designer',
            'HR Manager',
            'Freelancer/Consultant',
            'Others',
        );
    }
}

if (!function_exists('invite_user_role_list')) {
    function invite_user_role_list()
    {
        return array(
            'organization_admin',
            'organization_member',
        );
    }
}

if (!function_exists('define_invite_user_role')) {
    function define_invite_user_role($role)
    {
        $roles = array(
            "organization_admin" => "Admin",
            "organization_member" => "Member",
        );

        return $roles[$role];
    }
}

if (!function_exists('make_random_token_key')) {
    function make_random_token_key()
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle($permitted_chars), 0, 64);
    }
}

if (
    !function_exists('user_org')
) {
    function user_org($user_id)
    {
        // Custom Organization Data
        $user_orgs = DB::table('users')->join('organization_users', 'users.id', '=', 'organization_users.user_id')
            ->select('users.id', 'organization_users.organization_id');
        $user_orgs->where('user_id', 'LIKE', '' . $user_id . '');
        $user_orgs->orderBy('organization_id', 'ASC');

        $user_orgs_to_array = $user_orgs->get()->toArray();
        $custom_org_response = array();

        foreach ($user_orgs_to_array as $user_org) {
            $org_id = $user_org->organization_id;
            $organization = Organization::find($org_id);
            if (empty($organization->id))
                continue;
            $custom_org_response[] = $organization->toArray();
        }
        if (!empty($custom_org_response)) {
            return $custom_org_response;
        } else {
            return $custom_org_response;
        }
    }
}
