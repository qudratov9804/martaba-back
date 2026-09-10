<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        array $meta = [],
        int $status = 200,
    ): JsonResponse {
        if ($data instanceof AnonymousResourceCollection || $data instanceof JsonResource) {
            $response = $data->response()->getData(true);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $response['data'] ?? $data,
                'meta' => array_merge($response['meta'] ?? [], $meta),
                ...(isset($response['links']) ? ['links' => $response['links']] : []),
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function error(
        string $message,
        array $errors = [],
        int $status = 422,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
