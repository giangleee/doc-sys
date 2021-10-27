<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
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
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        $request->headers->set('Accept', 'application/json');
        if ($request->wantsJson()) {
            return $this->handleApiException($request, $exception);
        } else {
            return parent::render($request, $exception);
        }
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $message = !empty($exception->getMessage()) ? $exception->getMessage() : __('message.unauthenticated');
        return response()->json(['message' => $message], 401);
    }

    protected function unauthorized($request, AccessDeniedHttpException $exception)
    {
        $message = !empty($exception->getMessage()) ? $exception->getMessage() : __('message.unauthorized');
        return response()->json(['message' => $message], 403);
    }

    protected function notfound($request, NotFoundHttpException $exception)
    {
        $message = empty($exception->getMessage()) ? __('message.not_found') : $exception->getMessage();
        return response()->json(['message' => $message], 404);
    }

    protected function modelNotFound(ModelNotFoundException $exception)
    {
        return response()->json(['message' => $exception->getMessage()], 404);
    }

    protected function badRequest(BadRequestException $exception)
    {
        return response()->json(['message' => $exception->getMessage()], 400);
    }

    protected function internalError(InternalErrorException $exception)
    {
        return response()->json(['message' => $exception->getMessage()], 500);
    }

    protected function handleApiException($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof ModelNotFoundException) {
            return $this->modelNotFound($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof BadRequestException) {
            return $this->badRequest($exception);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return $this->unauthorized($request, $exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->notfound($request, $exception);
        }

        if ($exception instanceof InternalErrorException) {
            return $this->internalError($exception);
        }

        return parent::render($request, $exception);
    }
}
