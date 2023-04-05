<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Choice;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    //

    public function index(Request $request)
    {
        //

        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $group = $request->group;

        $choices = Choice::whereRaw('1=1');

        if (!empty($search))
            $choices->where('title', 'LIKE', '%' . $search . '%');

        if (!empty($group))
            $choices->where('group', 'LIKE', '%' . $group . '%');

        return JsonResponse::make($choices->get());
    }

    public function roles()
    {
        $roles = array();
        foreach (title_list() as $role) {
            $roles[] = array(
                "label" => $role,
                "value" => $role,
            );
        }
        return JsonResponse::make($roles);
    }

    public function rolesInviteUser()
    {
        $roles = array();
        $roles[] = array(
            "label" => "Admin",
            "value" => "organization_admin",
        );

        $roles[] = array(
            "label" => "Team Member",
            "value" => "organization_member",
        );

        return JsonResponse::make($roles);
    }
}
