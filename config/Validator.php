<?php
/**
 * Clase Validator - Validación de datos de entrada
 */
class Validator {
    
    private array $errors = [];
    
    /**
     * Valida que un campo sea requerido
     */
    public function required(string $field, $value, string $message = null): self {
        if (empty(trim($value))) {
            $this->errors[$field] = $message ?? "El campo {$field} es requerido";
        }
        return $this;
    }
    
    /**
     * Valida formato de email
     */
    public function email(string $field, $value, string $message = null): self {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "El campo {$field} debe ser un email válido";
        }
        return $this;
    }
    
    /**
     * Valida longitud mínima
     */
    public function minLength(string $field, $value, int $min, string $message = null): self {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = $message ?? "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }
    
    /**
     * Valida longitud máxima
     */
    public function maxLength(string $field, $value, int $max, string $message = null): self {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = $message ?? "El campo {$field} no puede tener más de {$max} caracteres";
        }
        return $this;
    }
    
    /**
     * Valida que sea un número
     */
    public function numeric(string $field, $value, string $message = null): self {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?? "El campo {$field} debe ser numérico";
        }
        return $this;
    }
    
    /**
     * Valida DNI peruano (8 dígitos)
     */
    public function dni(string $field, $value, string $message = null): self {
        if (!empty($value) && !preg_match('/^\d{8}$/', $value)) {
            $this->errors[$field] = $message ?? "El DNI debe tener 8 dígitos";
        }
        return $this;
    }
    
    /**
     * Valida RUC peruano (11 dígitos)
     */
    public function ruc(string $field, $value, string $message = null): self {
        if (!empty($value) && !preg_match('/^\d{11}$/', $value)) {
            $this->errors[$field] = $message ?? "El RUC debe tener 11 dígitos";
        }
        return $this;
    }
    
    /**
     * Valida teléfono peruano
     */
    public function phone(string $field, $value, string $message = null): self {
        if (!empty($value) && !preg_match('/^(\+51|51)?[9]\d{8}$/', $value)) {
            $this->errors[$field] = $message ?? "El teléfono debe ser un número válido peruano";
        }
        return $this;
    }
    
    /**
     * Verifica si hay errores
     */
    public function hasErrors(): bool {
        return !empty($this->errors);
    }
    
    /**
     * Obtiene todos los errores
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Limpia los errores
     */
    public function clearErrors(): self {
        $this->errors = [];
        return $this;
    }
    
    /**
     * Sanitiza una cadena
     */
    public static function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitiza un array de datos
     */
    public static function sanitizeArray(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}