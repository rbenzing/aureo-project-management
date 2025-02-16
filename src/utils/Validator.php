<?php
namespace App\Utils;

use App\Core\Database;

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    private $db; // Database connection

    /**
     * Constructor to initialize the data, validation rules, and database connection.
     *
     * @param array $data The input data to validate (e.g., $_POST or $_GET).
     * @param array $rules The validation rules for each field.
     */
    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
        $this->db = Database::getInstance(); // Consistent with models
    }

    /**
     * Validate the input data against the defined rules.
     *
     * @return bool Returns true if validation passes, false otherwise.
     */
    public function fails() {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            foreach ($rules as $rule) {
                $this->validateRule($field, $rule);
            }
        }
        return !empty($this->errors);
    }

    /**
     * Get the validation errors.
     *
     * @return array An array of error messages.
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Validate a single rule for a field.
     *
     * @param string $field The field name.
     * @param string $rule The validation rule (e.g., "required", "string|max:255").
     */
    private function validateRule($field, $rule) {
        // Extract parameters from the rule (e.g., "max:255" -> ["max", "255"])
        $params = explode(':', $rule);
        $ruleName = $params[0];
    
        // Retrieve & Sanitize the input value
        $value = $this->data[$field] ?? null;
    
        if (is_string($value)) {
            $value = trim($value); // Remove unnecessary whitespace
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); // Prevent XSS
        }
    
        // Apply different sanitization methods based on data type
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "$field is required.");
                }
                break;
    
            case 'string':
                if (!is_string($value)) {
                    $this->addError($field, "$field must be a string.");
                } elseif (isset($params[1])) { // Check max length
                    $maxLength = intval($params[1]);
                    if (strlen($value) > $maxLength) {
                        $this->addError($field, "$field must not exceed $maxLength characters.");
                    }
                }
                break;
    
            case 'email':
                $value = filter_var($value, FILTER_SANITIZE_EMAIL); // Sanitize email
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$field must be a valid email address.");
                }
                break;
    
            case 'unique':
                list($table, $column) = explode(',', $params[1]);
                $excludeId = isset($this->data['id']) ? intval($this->data['id']) : null;
                if (!$this->isUnique($table, $column, $value, $excludeId)) {
                    $this->addError($field, "$field must be unique.");
                }
                break;
    
            case 'max':
                if (isset($params[1]) && strlen($value) > intval($params[1])) {
                    $this->addError($field, "$field must not exceed {$params[1]} characters.");
                }
                break;
    
            case 'min':
                if (isset($params[1]) && strlen($value) < intval($params[1])) {
                    $this->addError($field, "$field must be at least {$params[1]} characters.");
                }
                break;
    
            case 'integer':
                $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT); // Sanitize integer input
                if (!is_numeric($value) || intval($value) != $value) {
                    $this->addError($field, "$field must be an integer.");
                } elseif (isset($params[1])) { // Check min/max values
                    $minMax = explode(',', $params[1]);
                    if (isset($minMax[0]) && $value < intval($minMax[0])) {
                        $this->addError($field, "$field must be at least {$minMax[0]}.");
                    }
                    if (isset($minMax[1]) && $value > intval($minMax[1])) {
                        $this->addError($field, "$field must not exceed {$minMax[1]}.");
                    }
                }
                break;
    
            case 'boolean':
                if (!in_array($value, [0, 1, '0', '1', true, false], true)) {
                    $this->addError($field, "$field must be a boolean value.");
                }
                break;
    
            case 'in':
                $allowedValues = explode(',', $params[1]);
                if (!in_array($value, $allowedValues)) {
                    $this->addError($field, "$field must be one of: " . implode(', ', $allowedValues));
                }
                break;
    
            case 'date':
                if (!strtotime($value)) {
                    $this->addError($field, "$field must be a valid date.");
                }
                break;
    
            case 'nullable':
                // No action needed; nullable fields are ignored if empty.
                break;
    
            case 'same':
                if (trim($value) !== trim($this->data[$params[1]])) {
                    $this->addError($field, "$params[1] fields must match.");
                }
                break;
    
            default:
                $this->addError($field, "Unknown validation rule: $ruleName.");
                break;
        }
    
        // Store the sanitized value back to the original data array
        $this->data[$field] = $value;
    }    

    /**
     * Add an error message for a specific field.
     *
     * @param string $field The field name.
     * @param string $message The error message.
     */
    private function addError($field, $message) {
        $this->errors[] = $message;
    }

    /**
     * Check if a value is unique in the database.
     *
     * @param string $table The table name.
     * @param string $column The column name.
     * @param mixed $value The value to check.
     * @param int|null $excludeId The ID to exclude (for updates).
     * @return bool True if the value is unique, false otherwise.
     */
    private function isUnique($table, $column, $value, $excludeId = null) {
        if (empty($value)) {
            return true; // Skip validation if the value is empty
        }
    
        $query = "SELECT COUNT(*) FROM $table WHERE $column = :value";
        $params = [':value' => $value];
    
        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
    
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() === 0;
    }
    
}