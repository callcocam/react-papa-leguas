<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToId;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToIcon;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToVariant;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToHidden;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToOrder;
use Closure;

class Tab
{
    use EvaluatesClosures,
        BelongsToId,
        BelongsToLabel,
        BelongsToIcon,
        BelongsToVariant,
        BelongsToHidden,
        BelongsToOrder;

    protected mixed $badge = null;
    protected ?Closure $badgeCallback = null;
    protected string $color = 'default';
    protected array $content = [];
    protected array $tableConfig = [];
    protected ?Closure $dataCallback = null;
    protected array $config = [];

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
        $this->variant = 'default';
        $this->color = 'default';
    }

    /**
     * Criar instância da tab
     */
    public static function make(string $id, string $label): static
    {
        return new static($id, $label);
    }

    /**
     * Define o badge/contador da tab
     */
    public function badge(mixed $badge): self
    {
        $this->badge = $badge;
        return $this;
    }

    /**
     * Define badge usando callback
     */
    public function badgeUsing(Closure $callback): self
    {
        $this->badgeCallback = $callback;
        return $this;
    }

    /**
     * Define a cor da tab
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Define o conteúdo da tab
     */
    public function content(array $content): self
    {
        $this->content = array_merge($this->content, $content);
        return $this;
    }

    /**
     * Define configurações da tabela
     */
    public function tableConfig(array $config): self
    {
        $this->tableConfig = array_merge($this->tableConfig, $config);
        return $this;
    }

    /**
     * Define callback para obter dados
     */
    public function dataUsing(Closure $callback): self
    {
        $this->dataCallback = $callback;
        return $this;
    }

    /**
     * Define configuração personalizada
     */
    public function config(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Atalhos para cores comuns
     */
    public function primary(): self
    {
        return $this->color('primary');
    }

    public function success(): self
    {
        return $this->color('success');
    }

    public function warning(): self
    {
        return $this->color('warning');
    }

    public function danger(): self
    {
        return $this->color('destructive');
    }

    public function secondary(): self
    {
        return $this->color('secondary');
    }

    /**
     * Atalhos para configurações de tabela
     */
    public function searchable(bool $searchable = true): self
    {
        return $this->tableConfig(['searchable' => $searchable]);
    }

    public function sortable(bool $sortable = true): self
    {
        return $this->tableConfig(['sortable' => $sortable]);
    }

    public function filterable(bool $filterable = true): self
    {
        return $this->tableConfig(['filterable' => $filterable]);
    }

    public function paginated(bool $paginated = true): self
    {
        return $this->tableConfig(['paginated' => $paginated]);
    }

    public function selectable(bool $selectable = true): self
    {
        return $this->tableConfig(['selectable' => $selectable]);
    }

    /**
     * Obtém o badge avaliado
     */
    public function getBadge(): mixed
    {
        if ($this->badgeCallback) {
            return $this->evaluate($this->badgeCallback, $this->context ?? []);
        }

        return $this->badge;
    }

    /**
     * Obtém a cor da tab
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Obtém o conteúdo da tab
     */
    public function getContent(): array
    {
        $content = $this->content;

        // Se tem callback de dados, adiciona aos dados do conteúdo
        if ($this->dataCallback) {
            $data = $this->evaluate($this->dataCallback, $this->context ?? []);
            $content['data'] = $data;
        }

        return $content;
    }

    /**
     * Obtém configurações da tabela
     */
    public function getTableConfig(): array
    {
        return $this->tableConfig;
    }

    /**
     * Obtém configuração personalizada
     */
    public function getConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Verifica se tem badge
     */
    public function hasBadge(): bool
    {
        return $this->badge !== null || $this->badgeCallback !== null;
    }

    /**
     * Serializa para array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'badge' => $this->getBadge(),
            'color' => $this->getColor(),
            'variant' => $this->getVariant(),
            'hidden' => $this->isHidden(),
            'order' => $this->getOrder(),
            'content' => $this->getContent(),
            'tableConfig' => $this->getTableConfig(),
            'config' => $this->getConfig(),
        ];
    }
} 