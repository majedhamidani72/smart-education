<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * پاسخ موفق
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {

        return response()->json([

            'success' => true,

            'message' => $message,

            'data' => $data,

        ], $status);
    }

    /**
     * پاسخ خطا
     */
    public static function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {

        return response()->json([

            'success' => false,

            'message' => $message,

            'errors' => $errors,

        ], $status);
    }

    /**
     * خطای اعتبارسنجی
     */
    public static function validation(
        mixed $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {

        return self::error(
            $message,
            $errors,
            422
        );
    }

    /**
     * پیدا نشد
     */
    public static function notFound(
        string $message = 'Resource not found.'
    ): JsonResponse {

        return self::error(
            $message,
            null,
            404
        );
    }

    /**
     * عدم احراز هویت
     */
    public static function unauthorized(
        string $message = 'Unauthorized.'
    ): JsonResponse {

        return self::error(
            $message,
            null,
            401
        );
    }

    /**
     * عدم دسترسی
     */
    public static function forbidden(
        string $message = 'Forbidden.'
    ): JsonResponse {

        return self::error(
            $message,
            null,
            403
        );
    }

    /**
     * پاسخ صفحه‌بندی
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        mixed $data,
        string $message = 'Success'
    ): JsonResponse {

        return response()->json([

            'success' => true,

            'message' => $message,

            'data' => $data,

            'meta' => [

                'current_page' => $paginator->currentPage(),

                'last_page' => $paginator->lastPage(),

                'per_page' => $paginator->perPage(),

                'total' => $paginator->total(),

            ],

        ]);
    }
}
