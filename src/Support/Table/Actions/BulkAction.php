<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Closure;
use Illuminate\Database\Eloquent\Collection;

/**
 * Ação que é executada em múltiplos itens selecionados (em lote).
 */
class BulkAction extends Action
{
    /**
     * O callback a ser executado para esta ação.
     * Espera receber uma coleção de modelos.
     *
     * @var Closure|null
     */
    protected ?Closure $callback = null;

    /**
     * Define o callback a ser executado.
     * O closure receberá uma Collection de modelos como argumento.
     */
    public function callback(Closure $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Obtém o callback da ação.
     */
    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    /**
     * Obtém o tipo da ação.
     */
    public function getType(): string
    {
        return 'bulk';
    }

    /**
     * Bulk actions não têm uma URL direta por linha.
     */
    public function getUrl($item = null, array $context = []): ?string
    {
        return null;
    }

    /**
     * Bulk actions usam o método POST.
     */
    public function getMethod(): string
    {
        return 'POST';
    }
} 