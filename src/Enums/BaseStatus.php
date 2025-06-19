<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Enums;

/**
 * Enum para status base do sistema
 */
enum BaseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
    case Deleted = 'deleted';

    /**
     * Obter label em português
     */
    public function label(): string
    {
        return match($this) {
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
            self::Archived => 'Arquivado',
            self::Deleted => 'Excluído',
        };
    }

    /**
     * Obter variante de badge
     */
    public function badgeVariant(): string
    {
        return match($this) {
            self::Active, self::Published => 'success',
            self::Inactive, self::Draft => 'secondary',
            self::Archived => 'warning',
            self::Deleted => 'destructive',
        };
    }

    /**
     * Verificar se é ativo
     */
    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Published]);
    }

    /**
     * Obter todos os valores
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obter opções para select
     */
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label()
        ], self::cases());
    }
}
