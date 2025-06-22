<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\FactoryPattern;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToIcon;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToVariant;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToSize;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToHidden;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToDisabled;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToOrder;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToGroup;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToTooltip;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToAttributes;
use Closure;

/**
 * Classe base para ações de tabela
 * 
 * Representa uma ação que pode ser executada em uma linha da tabela
 * ou em múltiplas linhas (bulk actions)
 */
abstract class Action
{
    use EvaluatesClosures, 
        FactoryPattern,
        BelongsToLabel,
        BelongsToIcon,
        BelongsToVariant,
        BelongsToSize,
        BelongsToHidden,
        BelongsToDisabled,
        BelongsToOrder,
        BelongsToGroup,
        BelongsToTooltip,
        BelongsToAttributes;

    /**
     * Identificador único da ação
     */
    protected string $key;

    /**
     * Posição da ação (start, end)
     */
    protected string $position = 'end';

    /**
     * Se o label deve ser exibido junto com o ícone.
     * O padrão é false (apenas ícone).
     */
    protected bool $showLabel = false;

    /**
     * Mensagem de confirmação
     */
    protected ?string $confirmationMessage = null;

    /**
     * Título da confirmação
     */
    protected ?string $confirmationTitle = null;

    /**
     * Texto do botão de confirmação
     */
    protected ?string $confirmationConfirmText = null;

    /**
     * Texto do botão de cancelamento
     */
    protected ?string $confirmationCancelText = null;

    /**
     * Variante do botão de confirmação
     */
    protected ?string $confirmationConfirmVariant = null;

    /**
     * Closure para customizar URL
     */
    protected ?Closure $urlUsing = null;

    /**
     * Contexto da ação (tabela, etc.)
     */
    protected array $context = [];

    /**
     * Construtor da ação
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = ucfirst(str_replace(['_', '-'], ' ', $key));
        $this->variant = 'default';
        $this->size = 'sm';
    }

    /**
     * Compatibilidade: Alias para visibleUsing (usa trait BelongsToHidden)
     */
    public function visible(Closure $callback): static
    {
        return $this->visibleUsing($callback);
    }

    /**
     * Compatibilidade: Alias para enabledUsing (usa trait BelongsToDisabled)
     */
    public function enabled(Closure $callback): static
    {
        return $this->enabledUsing($callback);
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
     * Define o tooltip da ação
     */
    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * Configura a ação para exibir o label ao lado do ícone.
     */
    public function showLabel(bool $show = true): static
    {
        $this->showLabel = $show;
        return $this;
    }

    /**
     * Define mensagem de confirmação
     */
    public function requiresConfirmation(
        string $message,
        ?string $title = null,
        ?string $confirmText = null,
        ?string $cancelText = null,
        ?string $confirmVariant = null
    ): static {
        $this->confirmationMessage = $message;
        $this->confirmationTitle = $title;
        $this->confirmationConfirmText = $confirmText;
        $this->confirmationCancelText = $cancelText;
        $this->confirmationConfirmVariant = $confirmVariant;
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
     * Define o contexto da ação
     */
    public function setContext(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Obtém o contexto da ação
     */
    public function getContext(): array
    {
        return $this->context;
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
     * Verifica se a ação está visível para um item (sobrescreve trait BelongsToHidden)
     */
    public function isVisible($item = null, array $context = []): bool
    {
        // Usa a lógica do trait primeiro
        if ($this->isHidden()) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se a ação está habilitada para um item (sobrescreve trait BelongsToDisabled)
     */
    public function isEnabled($item = null, array $context = []): bool
    {
        // Usa a lógica do trait primeiro
        if ($this->isDisabled()) {
            return false;
        }

        return true;
    }

    /**
     * Obtém o label da ação para um item (usa trait BelongsToLabel com contexto)
     */
    public function getLabel($item = null, array $context = []): string
    {
        // Seta contexto temporário para callbacks
        $originalContext = $this->context;
        $this->context = array_merge($this->context, [
            'item' => $item,
            'context' => $context,
            'action' => $this,
        ]);

        // Usa método do trait BelongsToLabel
        $result = $this->label !== null 
            ? $this->evaluate($this->label, $this->context) 
            : ucfirst(str_replace(['_', '-'], ' ', $this->key));

        // Restaura contexto
        $this->context = $originalContext;
        
        return $result ?? '';
    }

    /**
     * Obtém o ícone da ação para um item (usa trait BelongsToIcon com contexto)
     */
    public function getIcon($item = null, array $context = []): ?string
    {
        // Seta contexto temporário para callbacks
        $originalContext = $this->context;
        $this->context = array_merge($this->context, [
            'item' => $item,
            'context' => $context,
            'action' => $this,
        ]);

        // Usa método do trait BelongsToIcon
        $result = $this->icon !== null 
            ? $this->evaluate($this->icon, $this->context) 
            : null;

        // Restaura contexto
        $this->context = $originalContext;
        
        return $result;
    }

    /**
     * Obtém a variante da ação para um item (usa trait BelongsToVariant com contexto)
     */
    public function getVariant($item = null, array $context = []): string
    {
        // Seta contexto temporário para callbacks
        $originalContext = $this->context;
        $this->context = array_merge($this->context, [
            'item' => $item,
            'context' => $context,
            'action' => $this,
        ]);

        // Usa método do trait BelongsToVariant
        $result = $this->evaluate($this->variant, $this->context);

        // Restaura contexto
        $this->context = $originalContext;
        
        return $result ?? 'default';
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
     * Serializa a ação para um array
     */
    public function toArray($item = null, array $context = []): array
    {
        if (!$this->isVisible($item, $context)) {
            return [];
        }

        $array = [
            'key' => $this->key,
            'item_id' => $item->id ?? null,
            'type' => $this->getType(),
            'label' => $this->getLabel($item, $context),
            'icon' => $this->getIcon($item, $context),
            'variant' => $this->getVariant($item, $context),
            'size' => $this->getSize(),
            'position' => $this->getPosition(),
            'group' => $this->getGroup(),
            'order' => $this->getOrder(),
            'tooltip' => $this->getTooltip(),
            'showLabel' => $this->showLabel,
            'enabled' => $this->isEnabled($item, $context),
            'attributes' => $this->getAttributes(),
            'confirmation' => null,
        ];

        if ($this->confirmationMessage) {
            $array['confirmation'] = [
                'message' => $this->confirmationMessage,
                'title' => $this->confirmationTitle,
                'confirm_text' => $this->confirmationConfirmText,
                'cancel_text' => $this->confirmationCancelText,
                'confirm_variant' => $this->confirmationConfirmVariant,
            ];
        }

        return $array;
    }

    /**
     * Getter para posição (específico da Action)
     */
    public function getPosition(): string
    {
        return $this->position;
    }
} 