<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a business rule is violated
 * Used for domain logic constraints and invariants
 */
class BusinessRuleException extends RuntimeException
{
    protected $code = 400;

    /**
     * Create a new BusinessRuleException
     *
     * @param string $message Custom error message
     * @param int $code Error code (defaults to 400)
     */
    public function __construct(string $message = "Business rule violation", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    /**
     * Create exception for invalid status transition
     *
     * @param string $from Current status
     * @param string $to Attempted new status
     * @return self
     */
    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self("Cannot transition from '{$from}' to '{$to}'");
    }

    /**
     * Create exception for duplicate resource
     *
     * @param string $resourceType Type of resource
     * @param string $identifier Identifying information
     * @return self
     */
    public static function duplicateResource(string $resourceType, string $identifier): self
    {
        return new self("{$resourceType} '{$identifier}' already exists");
    }

    /**
     * Create exception for circular reference
     *
     * @param string $resourceType Type of resource
     * @return self
     */
    public static function circularReference(string $resourceType): self
    {
        return new self("Circular reference detected in {$resourceType} hierarchy");
    }
}
