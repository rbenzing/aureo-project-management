<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when validation fails
 * Used for form validation and data integrity checks
 */
class ValidationException extends RuntimeException
{
    protected $code = 422;

    /**
     * Validation errors
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Create a new ValidationException
     *
     * @param string $message Error message
     * @param array $errors Array of validation errors
     * @param int $code Error code (defaults to 422)
     */
    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if a specific field has errors
     *
     * @param string $field
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get errors for a specific field
     *
     * @param string $field
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Create exception from validator errors
     *
     * @param array $errors Validation errors
     * @return self
     */
    public static function withErrors(array $errors): self
    {
        return new self("Validation failed", $errors);
    }
}
