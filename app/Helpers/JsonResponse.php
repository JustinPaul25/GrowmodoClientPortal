<?php

namespace App\Helpers;

class JsonResponse
{

    const
        SUCCESS = 0,

        EXCEPTION = 100,
        QUERY_ERR = 99,

        INVALID_PARAMS = 101,
        ATTEMPT_FAILED = 102,

        NOT_FOUND = 200,
        INVALID_METHOD = 201,
        INVALID_GCAPTCHA = 203,

        UNAUTHORIZED = 300,
        PERMISSION_NOT_EXISTS = 301,
        WRONG_CREDENTIALS = 302,
        INVALID_FB_TOKEN = 303,
        EMAIL_NOT_VERIFIED = 304,
        ACCOUNT_INACTIVE = 305,
        ACCOUNT_LOCKED = 306,

        TOO_MANY_ATTEMPTS = 400,
        FORBIDDEN = 403,

        INTERNAL_SERVER_ERROR = 500,
        SERVICE_UNAVAILABLE = 503;

    public static function make($data, $status_code = 0, $message = '', $http_code = 200)
    {
        $data = [
            'success' => true,
            'code' => $status_code, // => our own custom status codes
            'data' => $data, // => the data to be return,
            'message' => $message // => optional message,
        ];

        if ($status_code != 0)
            $data['success'] = false;

        return response()->json(
            $data,
            $http_code,
        );
    }
}
