<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a requested resource is not found
 * Corresponds to HTTP 404 errors
 */
class NotFoundException extends RuntimeException
{
    protected $code = 404;

    /**
     * Create a new NotFoundException
     *
     * @param string $message Custom error message
     * @param int $code Error code (defaults to 404)
     */
    public function __construct(string $message = "Resource not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }

    /**
     * Create exception for a specific model
     *
     * @param string $modelName Name of the model
     * @param int|string $id ID of the resource
     * @return self
     */
    public static function forModel(string $modelName, int|string $id): self
    {
        return new self("{$modelName} with ID {$id} not found");
    }
}
