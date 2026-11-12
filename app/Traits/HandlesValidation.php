<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Trait for handling validation operations
 */
trait HandlesValidation
{
    /**
     * Validate a request with the given rules.
     *
     * @param  Request  $request  The HTTP request
     * @param  array  $rules  Validation rules
     * @param  array  $messages  Custom error messages
     * @param  array  $attributes  Custom attribute names
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateRequest(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        return $request->validate($rules, $messages, $attributes);
    }

    /**
     * Validate a request with the given rules and return a validator instance.
     *
     * @param  Request  $request  The HTTP request
     * @param  array  $rules  Validation rules
     * @param  array  $messages  Custom error messages
     * @param  array  $attributes  Custom attribute names
     * @return \Illuminate\Validation\Validator The validator instance
     */
    protected function getValidator(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): \Illuminate\Validation\Validator {
        return Validator::make($request->all(), $rules, $messages, $attributes);
    }

    /**
     * Validate data with the given rules.
     *
     * @param  array  $data  The data to validate
     * @param  array  $rules  Validation rules
     * @param  array  $messages  Custom error messages
     * @param  array  $attributes  Custom attribute names
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateData(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get validation rules for creating a model.
     *
     * @return array The validation rules
     */
    protected function getStoreValidationRules(): array
    {
        return [];
    }

    /**
     * Get validation rules for updating a model.
     *
     * @param  int  $id  The model ID
     * @return array The validation rules
     */
    protected function getUpdateValidationRules(int $id): array
    {
        return [];
    }

    /**
     * Get custom validation messages.
     *
     * @return array The custom validation messages
     */
    protected function getValidationMessages(): array
    {
        return [];
    }

    /**
     * Get custom validation attribute names.
     *
     * @return array The custom validation attribute names
     */
    protected function getValidationAttributes(): array
    {
        return [];
    }

    /**
     * Get validation rules for a specific action.
     *
     * @param  string  $action  The action name
     * @param  array  $params  Additional parameters
     * @return array The validation rules
     */
    protected function getValidationRulesForAction(string $action, array $params = []): array
    {
        $methodName = 'get'.ucfirst($action).'ValidationRules';

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}(...$params);
        }

        return [];
    }

    /**
     * Validate a request for a specific action.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $action  The action name
     * @param  array  $params  Additional parameters
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateRequestForAction(
        Request $request,
        string $action,
        array $params = []
    ): array {
        $rules = $this->getValidationRulesForAction($action, $params);
        $messages = $this->getValidationMessages();
        $attributes = $this->getValidationAttributes();

        return $this->validateRequest($request, $rules, $messages, $attributes);
    }

    /**
     * Get common validation rules for a model.
     *
     * @return array The common validation rules
     */
    protected function getCommonValidationRules(): array
    {
        return [];
    }

    /**
     * Merge common validation rules with specific rules.
     *
     * @param  array  $specificRules  The specific validation rules
     * @return array The merged validation rules
     */
    protected function mergeWithCommonRules(array $specificRules): array
    {
        $commonRules = $this->getCommonValidationRules();

        return array_merge($commonRules, $specificRules);
    }

    /**
     * Get validation rules for a specific field.
     *
     * @param  string  $field  The field name
     * @param  string  $action  The action name (create, update)
     * @return string|array The validation rules for the field
     */
    protected function getFieldValidationRules(string $field, string $action = 'create')
    {
        $rules = $action === 'create'
            ? $this->getStoreValidationRules()
            : $this->getUpdateValidationRules(0);

        return $rules[$field] ?? '';
    }

    /**
     * Check if a field is required for validation.
     *
     * @param  string  $field  The field name
     * @param  string  $action  The action name (create, update)
     * @return bool Whether the field is required
     */
    protected function isFieldRequired(string $field, string $action = 'create'): bool
    {
        $rules = $this->getFieldValidationRules($field, $action);

        if (is_string($rules)) {
            return str_contains($rules, 'required');
        }

        if (is_array($rules)) {
            return in_array('required', $rules);
        }

        return false;
    }

    /**
     * Get validation error messages for a specific field.
     *
     * @param  string  $field  The field name
     * @return array The validation error messages for the field
     */
    protected function getFieldValidationMessages(string $field): array
    {
        $messages = $this->getValidationMessages();
        $fieldMessages = [];

        foreach ($messages as $key => $message) {
            if (str_starts_with($key, $field.'.')) {
                $fieldMessages[$key] = $message;
            }
        }

        return $fieldMessages;
    }
}
