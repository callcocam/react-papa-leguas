<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

class SlaTab extends Tab
{
    protected string $slaType = 'expiring';
    protected int $hours = 24;
    protected string $dateField = 'due_date';

    public function __construct(string $id, string $label, string $slaType = 'expiring')
    {
        parent::__construct($id, $label);
        $this->slaType = $slaType;
        $this->icon('clock')
             ->danger()
             ->content(['type' => 'table'])
             ->tableConfig([
                 'searchable' => true,
                 'sortable' => true,
                 'paginated' => true,
             ]);
    }

    /**
     * Define o tipo de SLA
     */
    public function slaType(string $type): self
    {
        $this->slaType = $type;
        return $this;
    }

    /**
     * Define as horas para considerar como "vencendo"
     */
    public function hours(int $hours): self
    {
        $this->hours = $hours;
        return $this;
    }

    /**
     * Define o campo de data para verificar SLA
     */
    public function dateField(string $field): self
    {
        $this->dateField = $field;
        return $this;
    }

    /**
     * Atalhos para tabs de SLA comuns
     */
    public static function expiring(string $label = 'SLA Vencendo'): self
    {
        return (new self('sla_vencendo', $label, 'expiring'))
            ->icon('clock')
            ->danger()
            ->hours(24)
            ->order(200);
    }

    public static function expired(string $label = 'SLA Vencido'): self
    {
        return (new self('sla_vencido', $label, 'expired'))
            ->icon('alert-circle')
            ->danger()
            ->order(201);
    }

    public static function critical(string $label = 'SLA Crítico'): self
    {
        return (new self('sla_critico', $label, 'critical'))
            ->icon('alert-triangle')
            ->danger()
            ->hours(4)
            ->order(199);
    }

    /**
     * Obtém o tipo de SLA
     */
    public function getSlaType(): string
    {
        return $this->slaType;
    }

    /**
     * Obtém as horas
     */
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * Obtém o campo de data
     */
    public function getDateField(): string
    {
        return $this->dateField;
    }

    /**
     * Serializa para array incluindo configurações de SLA
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'slaType' => $this->getSlaType(),
            'hours' => $this->getHours(),
            'dateField' => $this->getDateField(),
        ]);
    }
} 