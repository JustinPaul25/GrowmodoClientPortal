<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaPlatform;
use Illuminate\Http\Request;

class SocialMediaPlatformsController extends Controller
{
    //

    public function index() {
        $socials = SocialMediaPlatform::all();

        $formatted = [];

        foreach ($socials as $s => $social) {
            $formatted[$social->platform] = $social->toArray();
        }

        return JsonResponse::make($formatted);
    }

}
