<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Closure;

/**
 * Trait para sistema de validação e sanitização de dados da tabela
 */
trait HasValidation
{
    /**
     * Configurações de validação
     */
    protected array $validationConfig = [
        'enabled' => true,
        'column_rules' => [],
        'column_sanitizers' => [],
        'global_sanitizers' => [],
        'custom_validators' => [],
        'error_handling' => 'strict', // 'strict', 'lenient', 'skip'
    ];

    /**
     * Erros de validação
     */
    protected array $validationErrors = [];

    /**
     * Habilitar validação
     */
    public function validation(bool $enabled = true): static
    {
        $this->validationConfig['enabled'] = $enabled;
        
        return $this;
    }

    /**
     * Definir regras de validação para coluna
     */
    public function columnRules(string $column, array $rules): static
    {
        $this->validationConfig['column_rules'][$column] = $rules;
        
        return $this;
    }

    /**
     * Definir sanitizador para coluna
     */
    public function columnSanitizer(string $column, Closure $sanitizer): static
    {
        if (!isset($this->validationConfig['column_sanitizers'][$column])) {
            $this->validationConfig['column_sanitizers'][$column] = [];
        }
        
        $this->validationConfig['column_sanitizers'][$column][] = $sanitizer;
        
        return $this;
    }

    /**
     * Adicionar sanitizador global
     */
    public function globalSanitizer(Closure $sanitizer): static
    {
        $this->validationConfig['global_sanitizers'][] = $sanitizer;
        
        return $this;
    }

    /**
     * Adicionar validador customizado
     */
    public function customValidator(string $name, Closure $validator): static
    {
        $this->validationConfig['custom_validators'][$name] = $validator;
        
        return $this;
    }

    /**
     * Definir modo de tratamento de erros
     */
    public function errorHandling(string $mode): static
    {
        $this->validationConfig['error_handling'] = $mode;
        
        return $this;
    }

    /**
     * Validar dados antes da transformação
     */
    public function validateData(array $data, Request $request): array
    {
        if (!$this->validationConfig['enabled']) {
            return $data;
        }

        $this->validationErrors = [];
        $validatedData = [];

        foreach ($data as $index => $row) {
            $validatedRow = $this->validateRow($row, $index, $request);
            
            if ($validatedRow !== null) {
                $validatedData[] = $validatedRow;
            }
        }

        return $validatedData;
    }

    /**
     * Validar linha individual
     */
    protected function validateRow(array $row, int $index, Request $request): ?array
    {
        // Sanitizar dados primeiro
        $sanitizedRow = $this->sanitizeRow($row, $request);

        // Aplicar regras de validação
        $rules = $this->buildValidationRules($sanitizedRow);
        
        if (empty($rules)) {
            return $sanitizedRow;
        }

        $validator = Validator::make($sanitizedRow, $rules);

        // Adicionar validadores customizados
        foreach ($this->validationConfig['custom_validators'] as $name => $validatorClosure) {
            $validator->addExtension($name, $validatorClosure);
        }

        if ($validator->fails()) {
            return $this->handleValidationError($validator->errors()->toArray(), $index, $sanitizedRow);
        }

        return $sanitizedRow;
    }

    /**
     * Sanitizar linha
     */
    protected function sanitizeRow(array $row, Request $request): array
    {
        // Aplicar sanitizadores globais
        foreach ($this->validationConfig['global_sanitizers'] as $sanitizer) {
            $row = $sanitizer($row, $request, $this);
        }

        // Aplicar sanitizadores de coluna
        foreach ($this->validationConfig['column_sanitizers'] as $column => $sanitizers) {
            if (isset($row[$column])) {
                foreach ($sanitizers as $sanitizer) {
                    $row[$column] = $sanitizer($row[$column], $row, $request, $this);
                }
            }
        }

        // Aplicar sanitizadores automáticos
        return $this->applyAutoSanitizers($row);
    }

    /**
     * Aplicar sanitizadores automáticos
     */
    protected function applyAutoSanitizers(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                // Sanitização básica de strings
                $row[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                // Sanitização recursiva para arrays
                $row[$key] = $this->applyAutoSanitizers($value);
            }
        }

        return $row;
    }

    /**
     * Sanitizar string
     */
    protected function sanitizeString(string $value): string
    {
        // Remover tags HTML perigosas
        $value = strip_tags($value, '<p><br><strong><em><u><a>');
        
        // Trim espaços
        $value = trim($value);
        
        // Escapar caracteres especiais
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }

    /**
     * Construir regras de validação
     */
    protected function buildValidationRules(array $row): array
    {
        $rules = [];

        foreach ($this->validationConfig['column_rules'] as $column => $columnRules) {
            if (isset($row[$column])) {
                $rules[$column] = $columnRules;
            }
        }

        return $rules;
    }

    /**
     * Lidar com erro de validação
     */
    protected function handleValidationError(array $errors, int $index, array $row): ?array
    {
        $this->validationErrors[] = [
            'row_index' => $index,
            'errors' => $errors,
            'data' => $row,
        ];

        switch ($this->validationConfig['error_handling']) {
            case 'strict':
                // Em modo strict, para a execução
                throw new \InvalidArgumentException("Erro de validação na linha {$index}: " . json_encode($errors));
                
            case 'lenient':
                // Em modo lenient, corrige os dados e continua
                return $this->fixValidationErrors($row, $errors);
                
            case 'skip':
                // Em modo skip, ignora a linha com erro
                return null;
                
            default:
                return $row;
        }
    }

    /**
     * Tentar corrigir erros de validação
     */
    protected function fixValidationErrors(array $row, array $errors): array
    {
        foreach ($errors as $field => $fieldErrors) {
            // Estratégias de correção baseadas no tipo de erro
            foreach ($fieldErrors as $error) {
                if (str_contains($error, 'required')) {
                    $row[$field] = $this->getDefaultValue($field);
                } elseif (str_contains($error, 'email')) {
                    $row[$field] = $this->sanitizeEmail($row[$field] ?? '');
                } elseif (str_contains($error, 'numeric')) {
                    $row[$field] = $this->sanitizeNumeric($row[$field] ?? '');
                } elseif (str_contains($error, 'date')) {
                    $row[$field] = $this->sanitizeDate($row[$field] ?? '');
                }
            }
        }

        return $row;
    }

    /**
     * Obter valor padrão para campo
     */
    protected function getDefaultValue(string $field): mixed
    {
        $defaults = [
            'email' => '',
            'name' => 'N/A',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return $defaults[$field] ?? null;
    }

    /**
     * Sanitizar email
     */
    protected function sanitizeEmail(string $email): string
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    /**
     * Sanitizar valor numérico
     */
    protected function sanitizeNumeric(string $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitizar data
     */
    protected function sanitizeDate(string $date): ?string
    {
        try {
            return \Carbon\Carbon::parse($date)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obter erros de validação
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Verificar se há erros de validação
     */
    public function hasValidationErrors(): bool
    {
        return !empty($this->validationErrors);
    }

    /**
     * Sanitizadores pré-definidos de conveniência
     */
    public function sanitizeHtml(string $column): static
    {
        return $this->columnSanitizer($column, function ($value) {
            return strip_tags($value);
        });
    }

    public function sanitizePhone(string $column): static
    {
        return $this->columnSanitizer($column, function ($value) {
            return preg_replace('/[^0-9]/', '', $value);
        });
    }

    public function sanitizeCpf(string $column): static
    {
        return $this->columnSanitizer($column, function ($value) {
            return preg_replace('/[^0-9]/', '', $value);
        });
    }

    public function sanitizeSlug(string $column): static
    {
        return $this->columnSanitizer($column, function ($value) {
            return \Illuminate\Support\Str::slug($value);
        });
    }

    /**
     * Validadores pré-definidos de conveniência
     */
    public function validateEmail(string $column): static
    {
        return $this->columnRules($column, ['email']);
    }

    public function validateRequired(string $column): static
    {
        return $this->columnRules($column, ['required']);
    }

    public function validateNumeric(string $column): static
    {
        return $this->columnRules($column, ['numeric']);
    }

    public function validateDate(string $column): static
    {
        return $this->columnRules($column, ['date']);
    }

    public function validateUnique(string $column, string $table): static
    {
        return $this->columnRules($column, ["unique:{$table},{$column}"]);
    }

    /**
     * Configurações rápidas de validação
     */
    public function strictValidation(): static
    {
        return $this->validation(true)->errorHandling('strict');
    }

    public function lenientValidation(): static
    {
        return $this->validation(true)->errorHandling('lenient');
    }

    public function skipInvalidRows(): static
    {
        return $this->validation(true)->errorHandling('skip');
    }
} 