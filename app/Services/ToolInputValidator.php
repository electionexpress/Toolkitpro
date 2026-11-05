<?php
declare(strict_types=1);
namespace App\Services;

use InvalidArgumentException;

final class ToolInputValidator
{
    public function validate(array $input, array $schema): void
    {
        if (($schema['type'] ?? 'object') !== 'object') throw new InvalidArgumentException('Tool input schema root must be an object.');
        foreach (($schema['required'] ?? []) as $field) {
            if (!array_key_exists((string)$field, $input)) throw new InvalidArgumentException('Missing required input: ' . $field);
        }
        foreach (($schema['properties'] ?? []) as $field => $rules) {
            if (!array_key_exists($field, $input) || !is_array($rules)) continue;
            $value = $input[$field];
            $type = $rules['type'] ?? null;
            $valid = match ($type) {
                'string' => is_string($value),
                'integer' => is_int($value),
                'number' => is_int($value) || is_float($value),
                'boolean' => is_bool($value),
                'array' => is_array($value) && array_is_list($value),
                'object' => is_array($value) && !array_is_list($value),
                null => true,
                default => false,
            };
            if (!$valid) throw new InvalidArgumentException('Invalid input type for: ' . $field);
            if (isset($rules['enum']) && !in_array($value, $rules['enum'], true)) throw new InvalidArgumentException('Unsupported value for: ' . $field);
            if (is_string($value) && isset($rules['maxLength']) && mb_strlen($value) > (int)$rules['maxLength']) throw new InvalidArgumentException('Input is too long: ' . $field);
            if ((is_int($value) || is_float($value)) && isset($rules['minimum']) && $value < $rules['minimum']) throw new InvalidArgumentException('Input is below minimum: ' . $field);
            if ((is_int($value) || is_float($value)) && isset($rules['maximum']) && $value > $rules['maximum']) throw new InvalidArgumentException('Input exceeds maximum: ' . $field);
        }
    }
}
