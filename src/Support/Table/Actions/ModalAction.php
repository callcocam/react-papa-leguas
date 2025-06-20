<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Closure;

/**
 * Ação que abre um modal ou slide-over no frontend.
 */
class ModalAction extends CallbackAction
{
    /**
     * O modo de exibição do componente ('modal' ou 'slideover').
     */
    protected string $mode = 'modal';

    /**
     * Título a ser exibido no header do modal/slide-over.
     */
    protected ?string $modalTitle = null;

    /**
     * A largura do modal/slide-over (classe CSS).
     * Ex: 'max-w-md', 'max-w-xl', 'w-1/3'
     */
    protected ?string $width = null;

    /**
     * Define o modo de exibição.
     */
    public function mode(string $mode): static
    {
        if (!in_array($mode, ['modal', 'slideover'])) {
            throw new \InvalidArgumentException("Modo inválido. Use 'modal' ou 'slideover'.");
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * Define o título do modal.
     */
    public function modalTitle(string $title): static
    {
        $this->modalTitle = $title;
        return $this;
    }

    /**
     * Define a largura do modal (classe CSS).
     */
    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Obtém o tipo da ação.
     */
    public function getType(): string
    {
        return 'modal';
    }

    /**
     * Serializa para array incluindo dados específicos do modal.
     */
    public function toArray($item = null, array $context = []): array
    {
        $array = parent::toArray($item, $context);

        if (empty($array)) {
            return $array;
        }

        $array['mode'] = $this->mode;
        $array['modal_title'] = $this->modalTitle;
        $array['width'] = $this->width;
        
        return $array;
    }
} 