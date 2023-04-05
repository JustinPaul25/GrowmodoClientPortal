<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class OrganizationController extends Controller
{
    /**
     * Display the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organization = Organization::all();

        $response = $organization->toArray();

        return JsonResponse::make($response, JsonResponse::SUCCESS);
    }

    public function show($id)
    {
        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $organization = Organization::find($id);

        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $response = $organization->toArray();

        return JsonResponse::make($response, JsonResponse::SUCCESS);
    }

    /**
     * Create the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string',
            'address_line_1' => 'string',
            'address_line_2' => 'string',
            'state' => 'string',
            'city' => 'string',
            'zipcode' => 'string',
            'country' => 'string',
            'about_us' => 'string',
            'website' => 'string',
            'company_type_id' => 'required|exists:company_types,id',
        ];
        if (!empty($request->social_accounts)) {
            $rules['social_accounts'] = 'array';
            $rules['social_accounts.*.*'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $organization = new Organization;
        foreach ($request->all() as $key => $value) {
            $organization->{$key} = $value;
        }

        if (!empty($request->owner_id)) {
            $owner_id = $request->owner_id;
        } else {
            $owner_id = $request->user()->id;
        }
        $organization->owner_id = $owner_id;


        if ($organization->save()) {
            $organization_refresh = Organization::find($organization->id);

            $response = $organization_refresh->toArray();

            return JsonResponse::make($response, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    /**
     * Update the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request_data = json_decode($request->getContent(), true);
        $current_user = $request->user();
        if (empty($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $organization = Organization::find($id);

        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $rules = [
            'title' => 'string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'country' => 'string',
            'about_us' => 'nullable|string',
            'website' => 'nullable|string',
            'company_type_id' => 'exists:company_types,id',
            'employee_count_id' => 'required|integer|exists:employee_counts,id',
        ];
        if (!empty($request->social_accounts)) {
            $rules['social_accounts'] = 'array';
            $rules['social_accounts.*.*'] = 'string';
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if (!empty($request_data['title']))
            $organization->title = $request_data['title'];
        if (!empty($request_data['address_line_1']))
            $organization->address_line_1 = $request_data['address_line_1'];
        if (!empty($request_data['address_line_2']))
            $organization->address_line_2 = $request_data['address_line_2'];
        if (!empty($request_data['state']))
            $organization->state = $request_data['state'];
        if (!empty($request_data['city']))
            $organization->city = $request_data['city'];
        if (!empty($request_data['zipcode']))
            $organization->zipcode = $request_data['zipcode'];
        if (!empty($request_data['country']))
            $organization->country = $request_data['country'];
        if (!empty($request_data['about_us']))
            $organization->about_us = $request_data['about_us'];
        if (!empty($request_data['website']))
            $organization->website = $request_data['website'];
        if (!empty($request_data['company_type_id']))
            $organization->company_type_id = $request_data['company_type_id'];
        if (!empty($request_data['employee_count_id']))
            $organization->employee_count_id = $request_data['employee_count_id'];
        if (!empty($request_data['social_accounts'])) {
            $organization->social_accounts = json_encode($request_data['social_accounts']);
        }

        if (!empty(xz_if_user_owner_or_admin_on_org($current_user->id, $id))) {
            if ($organization->save()) {
                $organization->refresh();
                $response = $organization->toArray();

                return JsonResponse::make($response, JsonResponse::SUCCESS);
            } else {
                return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
            }
        } else {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');
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
        $current_user = $request->user();
        if (empty($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $organization = Organization::find($id);

        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        if (!empty(xz_if_user_owner_or_admin_on_org($current_user->id, $id))) {
            $s = $organization->delete();

            // $s = $organization->save();
            return JsonResponse::make([], JsonResponse::SUCCESS, 'Organization has been deleted.');
            // return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'Organization has been deleted.' : 'Unable to delete organization');
        } else {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');
        }
    }


    /**
     * Create the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeAdmin(Request $request)
    {
        $request_data = json_decode($request->getContent(), true);

        $validator = Validator::make($request_data, [
            'title' => 'required|string',
            'address_line_1' => 'string',
            'address_line_2' => 'string',
            'state' => 'string',
            'city' => 'string',
            'zipcode' => 'string',
            'country' => 'string',
            'about_us' => 'string',
            'website' => 'string',
            'company_type_id' => 'required|exists:company_types,id',
            'social_accounts' => 'array',
            'social_accounts.*.*' => 'required|string',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $organization = new Organization();

        if (!empty($request_data['title']))
            $organization->title = $request_data['title'];
        if (!empty($request_data['address_line_1']))
            $organization->address_line_1 = $request_data['address_line_1'];
        if (!empty($request_data['address_line_2']))
            $organization->address_line_2 = $request_data['address_line_2'];
        if (!empty($request_data['state']))
            $organization->state = $request_data['state'];
        if (!empty($request_data['city']))
            $organization->city = $request_data['city'];
        if (!empty($request_data['zipcode']))
            $organization->zipcode = $request_data['zipcode'];
        if (!empty($request_data['country']))
            $organization->country = $request_data['country'];
        if (!empty($request_data['about_us']))
            $organization->about_us = $request_data['about_us'];
        if (!empty($request_data['website']))
            $organization->website = $request_data['website'];
        if (!empty($request_data['company_type_id']))
            $organization->company_type_id = $request_data['company_type_id'];
        if (!empty($request_data['social_accounts'])) {
            $organization->social_accounts = json_encode($request_data['social_accounts']);
        }

        if (!empty($request->owner_id)) {
            $owner_id = $request->owner_id;
        } else {
            $owner_id = $request->user()->id;
        }
        $organization->owner_id = $owner_id;

        if ($organization->save()) {
            $organization_refresh = Organization::find($organization->id);

            $response = $organization_refresh->toArray();

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    /**
     * Update the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAdmin(Request $request, $id)
    {
        $request_data = json_decode($request->getContent(), true);

        $validator = Validator::make($request_data, [
            'title' => 'required|string',
            'address_line_1' => 'string',
            'address_line_2' => 'string',
            'state' => 'string',
            'city' => 'string',
            'zipcode' => 'string',
            'country' => 'string',
            'about_us' => 'string',
            'website' => 'string',
            'company_type_id' => 'required|exists:company_types,id',
            'social_accounts' => 'array',
            'social_accounts.*.*' => 'required|string',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $organization = Organization::find($id);

        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        if (!empty($request_data['title']))
            $organization->title = $request_data['title'];
        if (!empty($request_data['address_line_1']))
            $organization->address_line_1 = $request_data['address_line_1'];
        if (!empty($request_data['address_line_2']))
            $organization->address_line_2 = $request_data['address_line_2'];
        if (!empty($request_data['state']))
            $organization->state = $request_data['state'];
        if (!empty($request_data['city']))
            $organization->city = $request_data['city'];
        if (!empty($request_data['zipcode']))
            $organization->zipcode = $request_data['zipcode'];
        if (!empty($request_data['country']))
            $organization->country = $request_data['country'];
        if (!empty($request_data['about_us']))
            $organization->about_us = $request_data['about_us'];
        if (!empty($request_data['website']))
            $organization->website = $request_data['website'];
        if (!empty($request_data['company_type_id']))
            $organization->company_type_id = $request_data['company_type_id'];
        if (!empty($request_data['social_accounts'])) {
            $organization->social_accounts = json_encode($request_data['social_accounts']);
        }

        if ($organization->save()) {
            $organization->refresh();
            $response = $organization->toArray();

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error ');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteAdmin(Request $request, $id)
    {
        if (empty($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $organization = Organization::find($id);
        if (empty($organization->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization not found.');

        $s = $organization->delete();

        return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'Organization has been deleted.' : 'Unable to delete user');
    }
}
