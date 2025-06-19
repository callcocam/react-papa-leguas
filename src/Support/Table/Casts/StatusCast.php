<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts;

use BackedEnum;
use UnitEnum;

class StatusCast extends Cast
{
    /**
     * Prioridade média para valores de status
     */
    protected int $priority = 70;

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'status';
    }

    /**
     * Configurações padrão do cast
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'format_type' => 'badge', // badge, text, icon
            'variants' => [],
            'labels' => [],
            'icons' => [],
            'colors' => [],
            'default_variant' => 'secondary',
            'default_label' => null,
            'default_icon' => null,
            'case_sensitive' => false,
            'enum_method' => null, // método do enum para obter label/variant
        ]);
    }

    /**
     * Configuração rápida para badges de status padrão
     */
    public static function statusBadge(array $config = []): static
    {
        return static::make(array_merge([
            'format_type' => 'badge',
            'variants' => [
                'active' => 'success',
                'published' => 'success',
                'approved' => 'success',
                'completed' => 'success',
                'inactive' => 'secondary',
                'draft' => 'secondary',
                'pending' => 'warning',
                'review' => 'warning',
                'processing' => 'warning',
                'rejected' => 'destructive',
                'cancelled' => 'destructive',
                'failed' => 'destructive',
                'archived' => 'outline',
                'deleted' => 'outline',
            ],
            'labels' => [
                'active' => 'Ativo',
                'inactive' => 'Inativo',
                'published' => 'Publicado',
                'draft' => 'Rascunho',
                'pending' => 'Pendente',
                'approved' => 'Aprovado',
                'rejected' => 'Rejeitado',
                'archived' => 'Arquivado',
            ],
        ], $config));
    }

    /**
     * Configuração rápida para badges booleanos
     */
    public static function booleanBadge(array $config = []): static
    {
        return static::make(array_merge([
            'format_type' => 'badge',
            'variants' => [
                true => 'success',
                1 => 'success',
                '1' => 'success',
                'true' => 'success',
                'yes' => 'success',
                'sim' => 'success',
                false => 'secondary',
                0 => 'secondary',
                '0' => 'secondary',
                'false' => 'secondary',
                'no' => 'secondary',
                'não' => 'secondary',
            ],
            'labels' => [
                true => 'Sim',
                1 => 'Sim',
                '1' => 'Sim',
                'true' => 'Sim',
                'yes' => 'Sim',
                'sim' => 'Sim',
                false => 'Não',
                0 => 'Não',
                '0' => 'Não',
                'false' => 'Não',
                'no' => 'Não',
                'não' => 'Não',
            ],
        ], $config));
    }

    /**
     * Configuração rápida para ícones
     */
    public static function iconStatus(array $config = []): static
    {
        return static::make(array_merge([
            'format_type' => 'icon',
            'icons' => [
                'active' => 'check-circle',
                'published' => 'check-circle',
                'approved' => 'check-circle',
                'inactive' => 'x-circle',
                'draft' => 'edit',
                'pending' => 'clock',
                'rejected' => 'x-circle',
                'archived' => 'archive',
            ],
            'colors' => [
                'active' => 'text-green-600',
                'published' => 'text-green-600',
                'approved' => 'text-green-600',
                'inactive' => 'text-gray-400',
                'draft' => 'text-blue-600',
                'pending' => 'text-yellow-600',
                'rejected' => 'text-red-600',
                'archived' => 'text-gray-600',
            ],
        ], $config));
    }

    /**
     * Define variantes para os valores
     */
    public function variants(array $variants): static
    {
        return $this->config('variants', array_merge($this->getConfig('variants', []), $variants));
    }

    /**
     * Define labels para os valores
     */
    public function labels(array $labels): static
    {
        return $this->config('labels', array_merge($this->getConfig('labels', []), $labels));
    }

    /**
     * Define ícones para os valores
     */
    public function icons(array $icons): static
    {
        return $this->config('icons', array_merge($this->getConfig('icons', []), $icons));
    }

    /**
     * Define cores para os valores
     */
    public function colors(array $colors): static
    {
        return $this->config('colors', array_merge($this->getConfig('colors', []), $colors));
    }

    /**
     * Define o tipo de formatação
     */
    public function formatType(string $type): static
    {
        return $this->config('format_type', $type);
    }

    /**
     * Define método do enum para obter informações
     */
    public function enumMethod(string $method): static
    {
        return $this->config('enum_method', $method);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCast(mixed $value, array $context): mixed
    {
        // Normalizar valor
        $normalizedValue = $this->normalizeValue($value);
        
        // Obter informações do status
        $statusInfo = $this->getStatusInfo($value, $normalizedValue);
        
        // Aplicar formatação baseada no tipo
        $formatType = $this->getConfig('format_type');
        
        return match ($formatType) {
            'badge' => $this->formatAsBadge($statusInfo),
            'icon' => $this->formatAsIcon($statusInfo),
            'text' => $this->formatAsText($statusInfo),
            default => $this->formatAsBadge($statusInfo),
        };
    }

    /**
     * Normaliza o valor para comparação
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if ($this->getConfig('case_sensitive')) {
            return $value;
        }
        
        if (is_string($value)) {
            return strtolower($value);
        }
        
        return $value;
    }

    /**
     * Obtém informações do status
     */
    protected function getStatusInfo(mixed $originalValue, mixed $normalizedValue): array
    {
        $info = [
            'original_value' => $originalValue,
            'normalized_value' => $normalizedValue,
            'variant' => $this->getConfig('default_variant'),
            'label' => $this->getConfig('default_label') ?? (string) $originalValue,
            'icon' => $this->getConfig('default_icon'),
            'color' => null,
        ];

        // Se é um enum, tentar extrair informações
        if ($originalValue instanceof UnitEnum) {
            $info = array_merge($info, $this->getEnumInfo($originalValue));
        }

        // Aplicar configurações manuais (sobrescreve enum)
        $variants = $this->getConfig('variants', []);
        $labels = $this->getConfig('labels', []);
        $icons = $this->getConfig('icons', []);
        $colors = $this->getConfig('colors', []);

        // Buscar por valor normalizado primeiro, depois original
        foreach ([$normalizedValue, $originalValue] as $searchValue) {
            if (isset($variants[$searchValue])) {
                $info['variant'] = $variants[$searchValue];
            }
            if (isset($labels[$searchValue])) {
                $info['label'] = $labels[$searchValue];
            }
            if (isset($icons[$searchValue])) {
                $info['icon'] = $icons[$searchValue];
            }
            if (isset($colors[$searchValue])) {
                $info['color'] = $colors[$searchValue];
            }
        }

        return $info;
    }

    /**
     * Obtém informações de um enum
     */
    protected function getEnumInfo(UnitEnum $enum): array
    {
        $info = [];
        
        // Obter valor se for BackedEnum
        if ($enum instanceof BackedEnum) {
            $info['enum_value'] = $enum->value;
        }
        
        // Obter nome do case
        $info['enum_name'] = $enum->name;
        
        // Tentar métodos comuns do enum
        $enumMethod = $this->getConfig('enum_method');
        if ($enumMethod && method_exists($enum, $enumMethod)) {
            $result = $enum->{$enumMethod}();
            if (is_array($result)) {
                $info = array_merge($info, $result);
            }
        }
        
        // Tentar métodos padrão
        $commonMethods = ['getLabel', 'getVariant', 'getColor', 'getBadge'];
        foreach ($commonMethods as $method) {
            if (method_exists($enum, $method)) {
                $result = $enum->{$method}();
                $key = strtolower(str_replace('get', '', $method));
                $info[$key] = $result;
            }
        }
        
        return $info;
    }

    /**
     * Formata como badge
     */
    protected function formatAsBadge(array $info): array
    {
        return [
            'type' => 'badge',
            'variant' => $info['variant'],
            'label' => $info['label'],
            'value' => $info['original_value'],
            'enum_info' => $this->getEnumInfoForOutput($info),
        ];
    }

    /**
     * Formata como ícone
     */
    protected function formatAsIcon(array $info): array
    {
        return [
            'type' => 'icon',
            'icon' => $info['icon'],
            'color' => $info['color'],
            'label' => $info['label'],
            'value' => $info['original_value'],
            'enum_info' => $this->getEnumInfoForOutput($info),
        ];
    }

    /**
     * Formata como texto
     */
    protected function formatAsText(array $info): array
    {
        return [
            'type' => 'text',
            'label' => $info['label'],
            'color' => $info['color'],
            'value' => $info['original_value'],
            'enum_info' => $this->getEnumInfoForOutput($info),
        ];
    }

    /**
     * Obtém informações do enum para saída
     */
    protected function getEnumInfoForOutput(array $info): ?array
    {
        if (!isset($info['enum_name'])) {
            return null;
        }
        
        return [
            'name' => $info['enum_name'],
            'value' => $info['enum_value'] ?? null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkCanCast(mixed $value, ?string $type = null): bool
    {
        // Verificar tipo explícito
        if ($type && $type !== 'status') {
            return false;
        }

        // Verificar se é um enum
        if ($value instanceof UnitEnum) {
            return true;
        }

        // Verificar se está nas configurações manuais
        $variants = $this->getConfig('variants', []);
        $labels = $this->getConfig('labels', []);
        
        $normalizedValue = $this->normalizeValue($value);
        
        // Verificar valor original e normalizado
        foreach ([$value, $normalizedValue] as $searchValue) {
            if (isset($variants[$searchValue]) || isset($labels[$searchValue])) {
                return true;
            }
        }

        // Verificar valores comuns de status
        $commonStatuses = [
            'active', 'inactive', 'published', 'draft', 'pending', 
            'approved', 'rejected', 'archived', 'deleted', 'completed',
            'processing', 'failed', 'cancelled', 'review'
        ];
        
        if (is_string($value)) {
            $lowerValue = strtolower($value);
            if (in_array($lowerValue, $commonStatuses)) {
                return true;
            }
        }

        return false;
    }
} 