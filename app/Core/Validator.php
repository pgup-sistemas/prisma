<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    private function __construct(private array $data, private array $rules)
    {
        $this->run();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function validated(): array
    {
        $out = [];
        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $out[$field] = $this->data[$field];
            }
        }

        return $out;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $param);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $name, ?string $param): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($name) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "{$label} é obrigatório.");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$label} deve ser um e-mail válido.");
                }
                break;

            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "{$label} deve ser uma URL válida.");
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, "{$label} deve ser numérico.");
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "{$label} deve ser um número inteiro.");
                }
                break;

            case 'min':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) < (int) $param) {
                    $this->addError($field, "{$label} deve ter no mínimo {$param} caracteres.");
                }
                break;

            case 'max':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) > (int) $param) {
                    $this->addError($field, "{$label} deve ter no máximo {$param} caracteres.");
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $param);
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->addError($field, "{$label} inválido.");
                }
                break;

            case 'confirmed':
                $confirmation = $this->data[$field . '_confirmation'] ?? null;
                if ($value !== $confirmation) {
                    $this->addError($field, 'A confirmação de ' . lcfirst($label) . ' não confere.');
                }
                break;

            case 'unique':
                [$table, $column] = array_pad(explode(',', (string) $param), 2, 'id');
                $column = $column === 'id' && !str_contains((string) $param, ',') ? $field : $column;
                if ($value !== null && $value !== '' && $this->existsInTable($table, $column, $value)) {
                    $this->addError($field, "{$label} já está em uso.");
                }
                break;
        }
    }

    private function existsInTable(string $table, string $column, mixed $value): bool
    {
        $stmt = Database::get()->prepare(
            "SELECT COUNT(*) AS total FROM `{$table}` WHERE `{$column}` = ?"
        );
        $stmt->execute([$value]);
        $row = $stmt->fetch();

        return ((int) ($row['total'] ?? 0)) > 0;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
