<?php
declare(strict_types=1);

namespace App\Core;

abstract class BaseValidator
{
    protected array $errors = [];
    abstract protected function rules(): array;

    public function validate(array $data, bool $isPatch = false): array
    {
        $this->errors = [];

        foreach ($this->rules() as $field => $rules) {
            
       
            if ($isPatch && !array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field] ?? null;

            foreach ($rules as $key => $ruleItem) {
               
                if (is_int($key)) {
                    $ruleName = $ruleItem;
                    $ruleValue = true; 
                } else {
                    $ruleName = $key;
                    $ruleValue = $ruleItem;
                }

                $this->applyRule($field, $value, $ruleName, $ruleValue, $data);
            }
        }

        return $this->errors;
    }
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field]) || !in_array($message, $this->errors[$field])) {
            $this->errors[$field][] = $message;
        }
    }

    protected function applyRule(
        string $field, 
        mixed $value, 
        string $rule, 
        mixed $ruleValue, 
        array $data 
    ): void {

        if ($rule === 'required') {
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                $this->addError($field, "Field '$field' is required");
            }
            return; 
        }
        if ($value === null || $value === '') {
            return;
        }
        switch ($rule) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Invalid email format");
                }
                break;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "Field must be an integer");
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, "Field must be numeric");
                }
                break;

            case 'boolean':
                if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
                    $this->addError($field, "Field must be boolean");
                }
                break;

            case 'minLength':
                if (mb_strlen((string)$value) < $ruleValue) {
                    $this->addError($field, "Minimum length is $ruleValue characters");
                }
                break;

            case 'maxLength':
                if (mb_strlen((string)$value) > $ruleValue) {
                    $this->addError($field, "Maximum length is $ruleValue characters");
                }
                break;

            case 'min':
                if ($value < $ruleValue) {
                    $this->addError($field, "Minimum value is $ruleValue");
                }
                break;

            case 'max':
                if ($value > $ruleValue) {
                    $this->addError($field, "Maximum value is $ruleValue");
                }
                break;

            case 'inArray':
                if (!in_array($value, $ruleValue)) {
                    $this->addError($field, "Invalid selection");
                }
                break;

            case 'confirm':
                if ($value !== ($data[$ruleValue] ?? null)) {
                    $this->addError($field, "Confirmation does not match");
                }
                break;

            case 'noTags':
                if (is_string($value) && $value !== strip_tags($value)) {
                    $this->addError($field, "HTML tags are not allowed");
                }
                break;
        }
    }
}