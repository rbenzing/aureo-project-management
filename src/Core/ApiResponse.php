<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Standardized API Response Format
 *
 * Provides consistent JSON response structure for all API endpoints
 */
class ApiResponse
{
    /**
     * Send a successful response
     *
     * @param mixed $data Response data
     * @param array $meta Additional metadata
     * @param int $statusCode HTTP status code (default 200)
     */
    public static function success(mixed $data = null, array $meta = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => true,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code (default 400)
     * @param array $details Additional error details
     */
    public static function error(string $message, int $code = 400, array $details = []): void
    {
        http_response_code($code);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a paginated response
     *
     * @param array $items Items for current page
     * @param int $total Total number of items
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param array $meta Additional metadata
     */
    public static function paginated(
        array $items,
        int $total,
        int $page,
        int $perPage,
        array $meta = []
    ): void {
        $totalPages = (int) ceil($total / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        $pagination = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $total,
            'total_pages' => $totalPages,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage,
        ];

        if ($hasNextPage) {
            $pagination['next_page'] = $page + 1;
        }

        if ($hasPrevPage) {
            $pagination['prev_page'] = $page - 1;
        }

        $response = [
            'success' => true,
            'data' => $items,
            'pagination' => $pagination,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a created response (201)
     *
     * @param mixed $data Created resource data
     * @param string|null $location Optional Location header value
     */
    public static function created(mixed $data = null, ?string $location = null): void
    {
        if ($location !== null) {
            header("Location: {$location}");
        }

        self::success($data, [], 201);
    }

    /**
     * Send a no content response (204)
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Send a not found error (404)
     *
     * @param string $message
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send a validation error (422)
     *
     * @param array $errors Validation errors
     * @param string $message
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::error($message, 422, ['validation_errors' => $errors]);
    }

    /**
     * Send an unauthorized error (401)
     *
     * @param string $message
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send a forbidden error (403)
     *
     * @param string $message
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send an internal server error (500)
     *
     * @param string $message
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }
}
