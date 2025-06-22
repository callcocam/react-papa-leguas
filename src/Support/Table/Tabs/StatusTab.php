<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

class StatusTab extends Tab
{
    protected array $statuses = [];
    protected string $statusField = 'status';

    public function __construct(string $id, string $label, array $statuses = [])
    {
        parent::__construct($id, $label);
        $this->statuses = $statuses;
        $this->content(['type' => 'table'])
             ->tableConfig([
                 'searchable' => true,
                 'sortable' => true,
                 'filterable' => true,
                 'paginated' => true,
             ]);
    }

    /**
     * Define os status para filtrar
     */
    public function statuses(array $statuses): self
    {
        $this->statuses = $statuses;
        return $this;
    }

    /**
     * Define o campo de status para filtrar
     */
    public function statusField(string $field): self
    {
        $this->statusField = $field;
        return $this;
    }

    /**
     * Atalhos para tabs de status comuns
     */
    public static function open(string $label = 'Abertos'): self
    {
        return (new self('abertos', $label, ['open', 'in_progress']))
            ->icon('circle-dot')
            ->primary()
            ->order(1);
    }

    public static function closed(string $label = 'Fechados'): self
    {
        return (new self('fechados', $label, ['closed', 'resolved']))
            ->icon('check-circle')
            ->success()
            ->order(2);
    }

    public static function pending(string $label = 'Pendentes'): self
    {
        return (new self('pendentes', $label, ['pending']))
            ->icon('clock')
            ->warning()
            ->order(3);
    }

    public static function draft(string $label = 'Rascunhos'): self
    {
        return (new self('rascunhos', $label, ['draft']))
            ->icon('file-text')
            ->secondary()
            ->order(4);
    }

    /**
     * Obtém os status
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * Obtém o campo de status
     */
    public function getStatusField(): string
    {
        return $this->statusField;
    }

    /**
     * Serializa para array incluindo configurações de status
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'statuses' => $this->getStatuses(),
            'statusField' => $this->getStatusField(),
        ]);
    }
} 