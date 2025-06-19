<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\FactoryPattern;
use Closure;

/**
 * Classe base para ações de tabela
 * 
 * Representa uma ação que pode ser executada em uma linha da tabela
 * ou em múltiplas linhas (bulk actions)
 */
abstract class Action
{
    use EvaluatesClosures, FactoryPattern;

    /**
     * Identificador único da ação
     */
    protected string $key;

    /**
     * Label exibido para a ação
     */
    protected string $label;

    /**
     * Ícone da ação (opcional)
     */
    protected ?string $icon = null;

    /**
     * Cor/variante da ação
     */
    protected string $variant = 'default';

    /**
     * Tamanho da ação
     */
    protected string $size = 'sm';

    /**
     * Se a ação está oculta
     */
    protected bool $hidden = false;

    /**
     * Se a ação está desabilitada
     */
    protected bool $disabled = false;

    /**
     * Posição da ação (start, end)
     */
    protected string $position = 'end';

    /**
     * Grupo da ação (para organização)
     */
    protected ?string $group = null;

    /**
     * Ordem de exibição
     */
    protected int $order = 0;

    /**
     * Tooltip da ação
     */
    protected ?string $tooltip = null;

    /**
     * Mensagem de confirmação
     */
    protected ?string $confirmationMessage = null;

    /**
     * Título da confirmação
     */
    protected ?string $confirmationTitle = null;

    /**
     * Closure para determinar visibilidade
     */
    protected ?Closure $visibleUsing = null;

    /**
     * Closure para determinar se está habilitada
     */
    protected ?Closure $enabledUsing = null;

    /**
     * Closure para customizar label
     */
    protected ?Closure $labelUsing = null;

    /**
     * Closure para customizar ícone
     */
    protected ?Closure $iconUsing = null;

    /**
     * Closure para customizar variante
     */
    protected ?Closure $variantUsing = null;

    /**
     * Closure para customizar URL
     */
    protected ?Closure $urlUsing = null;

    /**
     * Atributos HTML personalizados
     */
    protected array $attributes = [];

    /**
     * Construtor da ação
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = ucfirst(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Define o label da ação
     */
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Define o ícone da ação
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Define a variante/cor da ação
     */
    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * Define o tamanho da ação
     */
    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Oculta a ação
     */
    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * Desabilita a ação
     */
    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Define a posição da ação
     */
    public function position(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Define o grupo da ação
     */
    public function group(string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Define a ordem de exibição
     */
    public function order(int $order): static
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Define o tooltip da ação
     */
    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * Define mensagem de confirmação
     */
    public function requiresConfirmation(string $message, ?string $title = null): static
    {
        $this->confirmationMessage = $message;
        $this->confirmationTitle = $title;
        return $this;
    }

    /**
     * Define closure para visibilidade
     */
    public function visible(Closure $callback): static
    {
        $this->visibleUsing = $callback;
        return $this;
    }

    /**
     * Define closure para habilitação
     */
    public function enabled(Closure $callback): static
    {
        $this->enabledUsing = $callback;
        return $this;
    }

    /**
     * Define closure para label dinâmico
     */
    public function labelUsing(Closure $callback): static
    {
        $this->labelUsing = $callback;
        return $this;
    }

    /**
     * Define closure para ícone dinâmico
     */
    public function iconUsing(Closure $callback): static
    {
        $this->iconUsing = $callback;
        return $this;
    }

    /**
     * Define closure para variante dinâmica
     */
    public function variantUsing(Closure $callback): static
    {
        $this->variantUsing = $callback;
        return $this;
    }

    /**
     * Define closure para URL dinâmica
     */
    public function urlUsing(Closure $callback): static
    {
        $this->urlUsing = $callback;
        return $this;
    }

    /**
     * Define atributos HTML personalizados
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Configurações rápidas para ações comuns
     */
    public function edit(): static
    {
        return $this->icon('edit')
            ->variant('outline')
            ->tooltip('Editar');
    }

    public function delete(): static
    {
        return $this->icon('trash')
            ->variant('destructive')
            ->tooltip('Excluir')
            ->requiresConfirmation('Tem certeza que deseja excluir este item?', 'Confirmar Exclusão');
    }

    public function view(): static
    {
        return $this->icon('eye')
            ->variant('ghost')
            ->tooltip('Visualizar');
    }

    public function duplicate(): static
    {
        return $this->icon('copy')
            ->variant('outline')
            ->tooltip('Duplicar');
    }

    /**
     * Verifica se a ação está visível para um item
     */
    public function isVisible($item = null, array $context = []): bool
    {
        if ($this->hidden) {
            return false;
        }

        if ($this->visibleUsing) {
            $result = $this->evaluate($this->visibleUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]);
            return (bool) $result;
        }

        return true;
    }

    /**
     * Verifica se a ação está habilitada para um item
     */
    public function isEnabled($item = null, array $context = []): bool
    {
        if ($this->disabled) {
            return false;
        }

        if ($this->enabledUsing) {
            $result = $this->evaluate($this->enabledUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]);
            return (bool) $result;
        }

        return true;
    }

    /**
     * Obtém o label da ação para um item
     */
    public function getLabel($item = null, array $context = []): string
    {
        if ($this->labelUsing) {
            return $this->evaluate($this->labelUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]) ?? $this->label;
        }

        return $this->label;
    }

    /**
     * Obtém o ícone da ação para um item
     */
    public function getIcon($item = null, array $context = []): ?string
    {
        if ($this->iconUsing) {
            return $this->evaluate($this->iconUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]) ?? $this->icon;
        }

        return $this->icon;
    }

    /**
     * Obtém a variante da ação para um item
     */
    public function getVariant($item = null, array $context = []): string
    {
        if ($this->variantUsing) {
            return $this->evaluate($this->variantUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]) ?? $this->variant;
        }

        return $this->variant;
    }

    /**
     * Obtém a URL da ação para um item
     */
    abstract public function getUrl($item = null, array $context = []): ?string;

    /**
     * Obtém o método HTTP da ação
     */
    abstract public function getMethod(): string;

    /**
     * Obtém o tipo da ação
     */
    abstract public function getType(): string;

    /**
     * Serializa a ação para array
     */
    public function toArray($item = null, array $context = []): array
    {
        if (!$this->isVisible($item, $context)) {
            return [];
        }

        return [
            'key' => $this->key,
            'type' => $this->getType(),
            'label' => $this->getLabel($item, $context),
            'icon' => $this->getIcon($item, $context),
            'variant' => $this->getVariant($item, $context),
            'size' => $this->size,
            'position' => $this->position,
            'group' => $this->group,
            'order' => $this->order,
            'tooltip' => $this->tooltip,
            'url' => $this->getUrl($item, $context),
            'method' => $this->getMethod(),
            'enabled' => $this->isEnabled($item, $context),
            'confirmation' => $this->confirmationMessage ? [
                'message' => $this->confirmationMessage,
                'title' => $this->confirmationTitle,
            ] : null,
            'attributes' => $this->attributes,
        ];
    }

    /**
     * Getters para propriedades
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getPosition(): string
    {
        return $this->position;
    }
} 