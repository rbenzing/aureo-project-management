<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Validator utility class
 *
 * Tests validation logic for user input including:
 * - Required field validation
 * - Email format validation
 * - String length validation
 * - Numeric value validation
 * - Custom validation rules
 */
final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    /**
     * Test required field validation
     */
    public function testRequiredFieldValidation(): void
    {
        // Test with empty string
        $this->validator->validate(['name' => ''], ['name' => 'required']);
        $this->assertTrue($this->validator->hasErrors());
        $this->assertArrayHasKey('name', $this->validator->getErrors());

        // Test with null value
        $validator2 = new Validator();
        $validator2->validate(['name' => null], ['name' => 'required']);
        $this->assertTrue($validator2->hasErrors());

        // Test with valid value
        $validator3 = new Validator();
        $validator3->validate(['name' => 'John Doe'], ['name' => 'required']);
        $this->assertFalse($validator3->hasErrors());
    }

    /**
     * Test email validation
     */
    public function testEmailValidation(): void
    {
        // Test with invalid email
        $this->validator->validate(
            ['email' => 'invalid-email'],
            ['email' => 'email']
        );
        $this->assertTrue($this->validator->hasErrors());

        // Test with valid email
        $validator2 = new Validator();
        $validator2->validate(
            ['email' => 'user@example.com'],
            ['email' => 'email']
        );
        $this->assertFalse($validator2->hasErrors());

        // Test with complex valid email
        $validator3 = new Validator();
        $validator3->validate(
            ['email' => 'user.name+tag@sub.example.com'],
            ['email' => 'email']
        );
        $this->assertFalse($validator3->hasErrors());
    }

    /**
     * Test minimum length validation
     */
    public function testMinLengthValidation(): void
    {
        // Test with string shorter than minimum
        $this->validator->validate(
            ['password' => '123'],
            ['password' => 'min:6']
        );
        $this->assertTrue($this->validator->hasErrors());

        // Test with string equal to minimum
        $validator2 = new Validator();
        $validator2->validate(
            ['password' => '123456'],
            ['password' => 'min:6']
        );
        $this->assertFalse($validator2->hasErrors());

        // Test with string longer than minimum
        $validator3 = new Validator();
        $validator3->validate(
            ['password' => '12345678'],
            ['password' => 'min:6']
        );
        $this->assertFalse($validator3->hasErrors());
    }

    /**
     * Test maximum length validation
     */
    public function testMaxLengthValidation(): void
    {
        // Test with string longer than maximum
        $this->validator->validate(
            ['username' => 'thisusernameiswaytoolong'],
            ['username' => 'max:10']
        );
        $this->assertTrue($this->validator->hasErrors());

        // Test with string equal to maximum
        $validator2 = new Validator();
        $validator2->validate(
            ['username' => '1234567890'],
            ['username' => 'max:10']
        );
        $this->assertFalse($validator2->hasErrors());

        // Test with string shorter than maximum
        $validator3 = new Validator();
        $validator3->validate(
            ['username' => 'john'],
            ['username' => 'max:10']
        );
        $this->assertFalse($validator3->hasErrors());
    }

    /**
     * Test numeric validation
     */
    public function testNumericValidation(): void
    {
        // Test with non-numeric string
        $this->validator->validate(
            ['age' => 'abc'],
            ['age' => 'numeric']
        );
        $this->assertTrue($this->validator->hasErrors());

        // Test with numeric string
        $validator2 = new Validator();
        $validator2->validate(
            ['age' => '25'],
            ['age' => 'numeric']
        );
        $this->assertFalse($validator2->hasErrors());

        // Test with actual number
        $validator3 = new Validator();
        $validator3->validate(
            ['age' => 25],
            ['age' => 'numeric']
        );
        $this->assertFalse($validator3->hasErrors());
    }

    /**
     * Test multiple validation rules
     */
    public function testMultipleValidationRules(): void
    {
        // Test with multiple rules - all pass
        $this->validator->validate(
            [
                'email' => 'user@example.com',
                'password' => 'SecurePass123',
                'age' => '25'
            ],
            [
                'email' => 'required|email',
                'password' => 'required|min:8|max:50',
                'age' => 'required|numeric'
            ]
        );
        $this->assertFalse($this->validator->hasErrors());

        // Test with multiple rules - some fail
        $validator2 = new Validator();
        $validator2->validate(
            [
                'email' => 'invalid-email',
                'password' => 'short',
                'age' => 'not-a-number'
            ],
            [
                'email' => 'required|email',
                'password' => 'required|min:8|max:50',
                'age' => 'required|numeric'
            ]
        );
        $this->assertTrue($validator2->hasErrors());
        $errors = $validator2->getErrors();
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertArrayHasKey('age', $errors);
    }

    /**
     * Test validation with all valid inputs
     */
    public function testValidationPassesWithValidInputs(): void
    {
        $result = $this->validator->validate(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'password' => 'SecurePassword123'
            ],
            [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]
        );

        $this->assertTrue($result);
        $this->assertFalse($this->validator->hasErrors());
        $this->assertEmpty($this->validator->getErrors());
    }

    /**
     * Test getting first error message
     */
    public function testGetFirstError(): void
    {
        $this->validator->validate(
            [
                'email' => 'invalid',
                'password' => '123'
            ],
            [
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]
        );

        $this->assertTrue($this->validator->hasErrors());
        $firstError = $this->validator->getFirstError();
        $this->assertNotEmpty($firstError);
        $this->assertIsString($firstError);
    }

    /**
     * Test sanitization of inputs
     */
    public function testInputSanitization(): void
    {
        $validator = new Validator();
        $result = $validator->validate(
            ['name' => '  John Doe  '],
            ['name' => 'required']
        );

        // Validator should trim whitespace
        $this->assertTrue($result);
    }

    /**
     * Test error messages are human-readable
     */
    public function testErrorMessagesAreReadable(): void
    {
        $this->validator->validate(
            ['email' => ''],
            ['email' => 'required']
        );

        $errors = $this->validator->getErrors();
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertIsString($errors['email']);
        $this->assertNotEmpty($errors['email']);
    }
}
