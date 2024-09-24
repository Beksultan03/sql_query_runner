<?php

namespace App\Http\API\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class BaseController
{
    protected function getApiVersion()
    {
        return config('api.api_version') ?? '1';
    }

    /**
     * @param array $errors
     * @param int $errorCode
     * @return JsonResponse
     */
    public function responseError(array $errors, int $errorCode): JsonResponse
    {
        return response()->json(
            [
                'error' => $errors,
                'meta' => 'api version ' . $this->getApiVersion()
            ],
        )->setStatusCode($errorCode);
    }

    /**
     * @return JsonResponse
     */
    protected function responseNotFound(): JsonResponse
    {
        return response()->json(
            [
                'error' => 'Not Found',
                'meta' => 'api version ' . $this->getApiVersion()
            ]
        )->setStatusCode(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return JsonResponse
     */
    protected function responseBadRequest(): JsonResponse
    {
        return response()->json(
            [
                'error' => 'Bad operation',
                'meta' => 'api version ' . $this->getApiVersion()
            ]
        )->setStatusCode(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param JsonResource|array $data
     * @return JsonResponse
     */
    protected function responseOk(JsonResource|array $data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => 'api version ' . $this->getApiVersion(),
        ])->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param JsonResource|array $data
     * @return JsonResponse
     */
    protected function responseCreated(JsonResource|array $data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => 'api version ' . $this->getApiVersion(),
        ])->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function responseDeleted(string $message): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => $message,
            'meta' => 'api version ' . $this->getApiVersion()
        ])->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * @param string $message
     * @param array $errors
     * @return JsonResponse
     */
    protected function responseInvalidData(string $message, array $errors): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => $errors,
                'meta' => 'api version ' . $this->getApiVersion()
            ]
        )->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return JsonResponse
     */
    protected function responseMethodNotAllowed(): JsonResponse
    {
        return response()->json([
            'error' => 'Has Not Implemented',
            'meta' => 'api version ' . $this->getApiVersion()
        ])->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * @return JsonResponse
     */
    protected function responseForbidden(): JsonResponse
    {
        return response()->json(
            [
                'status' => 'error',
                'error' => 'Access denied!',
                'meta' => 'api version ' . $this->getApiVersion()
            ]
        )->setStatusCode(Response::HTTP_FORBIDDEN);
    }

    protected function responseConflict(string $message): JsonResponse
    {
        return response()->json(
            [
                'error' => $message,
                'meta' => 'api version ' . $this->getApiVersion()
            ]
        )->setStatusCode(Response::HTTP_CONFLICT);
    }

    protected function responseOkWithMessage(string $message): JsonResponse
    {
        return response()->json(['message' => $message])->setStatusCode(Response::HTTP_OK);
    }
}
