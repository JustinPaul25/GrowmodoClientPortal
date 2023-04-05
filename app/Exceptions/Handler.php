<?php

namespace App\Exceptions;

use App\Helpers\JsonResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (Throwable $e) {
        //     //
        // });

        $include_data_message = ! (config('app.env') == 'production' || config('app.env') == 'staging');


        $this->renderable(function (\League\OAuth2\Server\Exception\OAuthServerException $e, $request) {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        });

        $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        });
        $this->renderable(function (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e, $request) {
            return JsonResponse::make([], JsonResponse::PERMISSION_NOT_EXISTS);
        });
        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        });


        $this->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            return JsonResponse::make([], JsonResponse::NOT_FOUND);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return JsonResponse::make([], JsonResponse::NOT_FOUND);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return JsonResponse::make([], JsonResponse::NOT_FOUND);
        });

        $this->renderable(function (\Illuminate\Foundation\Exceptions\MethodNotAllowedHttpException $e, $request) {
            return JsonResponse::make([], JsonResponse::INVALID_METHOD);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            return JsonResponse::make([], JsonResponse::INVALID_METHOD);
        });


        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) use ($include_data_message) {
            return ! $include_data_message ? JsonResponse::make([], JsonResponse::QUERY_ERR) : JsonResponse::make([], JsonResponse::QUERY_ERR, $e->getMessage());
        });

        $this->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) use ($include_data_message) {
            return JsonResponse::make([], JsonResponse::TOO_MANY_ATTEMPTS, 'Too many attempts');
        });


        $this->renderable(function (\ErrorException $e, $request) use ($include_data_message) {
            Log::error('Unknown Error Exception: ```' . json_encode([
                get_class($e),
                $e->getLine(),
                $e->getFile(),
            ]) . '```');

            return ! $include_data_message ?  JsonResponse::make([], JsonResponse::EXCEPTION) : JsonResponse::make([$e->getLine(), $e->getFile()], JsonResponse::EXCEPTION, $e->getMessage());
        });

        $this->renderable(function (\Throwable $e, $request) use ($include_data_message) {
            Log::error('Unknown Exception: ```' . json_encode([
                get_class($e),
                $e->getLine(),
                $e->getFile(),
            ]) . '```');

            return ! $include_data_message ? JsonResponse::make([], JsonResponse::EXCEPTION) : JsonResponse::make([
                get_class($e),
                $e->getLine(),
                $e->getFile(),
            ], JsonResponse::EXCEPTION, $e->getMessage());
        });

        $this->reportable(function (Throwable $e) {
            //
        });

    }
}
