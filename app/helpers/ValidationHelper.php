<?php
// FILE: /app/helpers/ValidationHelper.php

class ValidationHelper {
    private $errors = [];
    private $data = [];

    public function validate($data, $rules) {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $rule) {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $ruleValue = isset($parts[1]) ? $parts[1] : null;

        $value = isset($this->data[$field]) ? $this->data[$field] : null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . ' is required.');
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email address.');
                }
                break;

            case 'min':
                if ($value && strlen($value) < $ruleValue) {
                    $this->addError($field, ucfirst($field) . " must be at least $ruleValue characters.");
                }
                break;

            case 'max':
                if ($value && strlen($value) > $ruleValue) {
                    $this->addError($field, ucfirst($field) . " must not exceed $ruleValue characters.");
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be numeric.');
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, ucfirst($field) . ' must be an integer.');
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if ($value && !in_array($value, $allowedValues)) {
                    $this->addError($field, ucfirst($field) . ' must be one of: ' . implode(', ', $allowedValues));
                }
                break;

            case 'phone':
                if ($value && !preg_match('/^\+?[0-9\s\-\(\)]+$/', $value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid phone number.');
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid URL.');
                }
                break;

            case 'unique':
                // Format: unique:table,column
                $params = explode(',', $ruleValue);
                $table = $params[0];
                $column = isset($params[1]) ? $params[1] : $field;
                $exceptId = isset($params[2]) ? $params[2] : null;

                $db = Database::getInstance();
                $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = :value";
                $sqlParams = ['value' => $value];

                if ($exceptId) {
                    $sql .= " AND id != :except_id";
                    $sqlParams['except_id'] = $exceptId;
                }

                $result = $db->fetchOne($sql, $sqlParams);
                if ($result['count'] > 0) {
                    $this->addError($field, ucfirst($field) . ' already exists.');
                }
                break;

            case 'exists':
                // Format: exists:table,column
                $params = explode(',', $ruleValue);
                $table = $params[0];
                $column = isset($params[1]) ? $params[1] : 'id';

                if ($value) {
                    $db = Database::getInstance();
                    $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = :value";
                    $result = $db->fetchOne($sql, ['value' => $value]);
                    if ($result['count'] == 0) {
                        $this->addError($field, ucfirst($field) . ' does not exist.');
                    }
                }
                break;

            case 'match':
                // Format: match:other_field
                $otherField = $ruleValue;
                $otherValue = isset($this->data[$otherField]) ? $this->data[$otherField] : null;
                if ($value !== $otherValue) {
                    $this->addError($field, ucfirst($field) . ' must match ' . ucfirst($otherField) . '.');
                }
                break;

            case 'json':
                if ($value && json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->addError($field, ucfirst($field) . ' must be valid JSON.');
                }
                break;
        }
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError($field = null) {
        if ($field) {
            return isset($this->errors[$field]) ? $this->errors[$field][0] : null;
        }

        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }

        return null;
    }

    public function allErrors() {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            $all = array_merge($all, $fieldErrors);
        }
        return $all;
    }

    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
