<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
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
        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
    }

    public function handleException(Throwable $exception)
    {
        $request = request();
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            $errors = collect($errors)->mapWithKeys(function ($error, $key) {
                return [$key => $error[0]];
            });
            return $this->errorResponse($errors, 422);
        }
        if ($exception instanceof ModelNotFoundException) // model error(if search like user model but does not exist any result)
        {
            $model_name = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not exist {$model_name} with the specified identificator", 404);
        }
        if ($exception instanceof AuthenticationException) // who does not register user in our system
        {
            return $this->unauthenticated($request, $exception);
        }
        if ($exception instanceof AuthorizationException) // who is register user but does not permission some system
        {
            return $this->errorResponse($exception->getMessage(), 403);
        }
        if ($exception instanceof MethodNotAllowedHttpException) // if method not found
        {
            return $this->errorResponse('The specified method for the request is invalid', 405);
        }
        if ($exception instanceof NotFoundHttpException) { // url wrong
            return $this->errorResponse('The specified url can not be found', 404);
        }
        if ($exception instanceof HttpException) // any type of incoming http exception which we not know what is this.(any other kind of http exception)
        {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof TokenMismatchException) { // if you  use frontend web service then need csrf security for form submit then it will work
            return redirect()->back()->withInput($request->input());
        }

        // database related
        if ($exception instanceof QueryException) {
            $error_code = $exception->errorInfo[1];
            if ($error_code === 1451) {
                return $this->errorResponse('Cannot remove this resource permanently.It is related with any other resource', 409);
            }
        }

        if ($exception instanceof TokenInvalidException) {
            return $this->errorResponse('Token is Invalid', 400);
        } elseif ($exception instanceof TokenExpiredException) {
            return $this->errorResponse('Token is Expired', 400);
        } elseif ($exception instanceof JWTException) {
            return $this->errorResponse('There is problem with your token', 400);
        }

    }
}
