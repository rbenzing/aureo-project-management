<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when user lacks permission to perform an action
 * Corresponds to HTTP 403 Forbidden errors
 */
class AuthorizationException extends RuntimeException
{
    protected $code = 403;

    /**
     * Create a new AuthorizationException
     *
     * @param string $message Custom error message
     * @param int $code Error code (defaults to 403)
     */
    public function __construct(string $message = "You do not have permission to perform this action", int $code = 403)
    {
        parent::__construct($message, $code);
    }

    /**
     * Create exception for a specific permission
     *
     * @param string $permission Name of the required permission
     * @return self
     */
    public static function forPermission(string $permission): self
    {
        return new self("You need the '{$permission}' permission to perform this action");
    }

    /**
     * Create exception for a specific action
     *
     * @param string $action Action being attempted
     * @param string $resource Resource being accessed
     * @return self
     */
    public static function forAction(string $action, string $resource): self
    {
        return new self("You are not authorized to {$action} this {$resource}");
    }
}
