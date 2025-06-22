<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

class PriorityTab extends Tab
{
    protected array $priorities = [];
    protected string $priorityField = 'priority';

    public function __construct(string $id, string $label, array $priorities = [])
    {
        parent::__construct($id, $label);
        $this->priorities = $priorities;
        $this->content(['type' => 'table'])
             ->tableConfig([
                 'searchable' => true,
                 'sortable' => true,
                 'paginated' => true,
             ]);
    }

    /**
     * Define as prioridades para filtrar
     */
    public function priorities(array $priorities): self
    {
        $this->priorities = $priorities;
        return $this;
    }

    /**
     * Define o campo de prioridade para filtrar
     */
    public function priorityField(string $field): self
    {
        $this->priorityField = $field;
        return $this;
    }

    /**
     * Atalhos para tabs de prioridade comuns
     */
    public static function highPriority(string $label = 'Alta Prioridade'): self
    {
        return (new self('alta_prioridade', $label, ['high', 'urgent']))
            ->icon('alert-triangle')
            ->warning()
            ->order(10);
    }

    public static function urgent(string $label = 'Urgente'): self
    {
        return (new self('urgente', $label, ['urgent']))
            ->icon('alert-circle')
            ->danger()
            ->order(5);
    }

    public static function mediumPriority(string $label = 'Média Prioridade'): self
    {
        return (new self('media_prioridade', $label, ['medium']))
            ->icon('minus-circle')
            ->secondary()
            ->order(15);
    }

    public static function lowPriority(string $label = 'Baixa Prioridade'): self
    {
        return (new self('baixa_prioridade', $label, ['low']))
            ->icon('circle')
            ->secondary()
            ->order(20);
    }

    /**
     * Obtém as prioridades
     */
    public function getPriorities(): array
    {
        return $this->priorities;
    }

    /**
     * Obtém o campo de prioridade
     */
    public function getPriorityField(): string
    {
        return $this->priorityField;
    }

    /**
     * Serializa para array incluindo configurações de prioridade
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'priorities' => $this->getPriorities(),
            'priorityField' => $this->getPriorityField(),
        ]);
    }
} 