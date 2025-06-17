<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna editável inline
 */
class EditableColumn extends Column
{
    protected string $type = 'editable';

    /**
     * Tipo de input para edição
     */
    protected string $editType = 'text';

    /**
     * Rota para atualização
     */
    protected string $updateRoute = '';

    /**
     * Parâmetros da rota de atualização
     */
    protected array $updateRouteParameters = [];

    /**
     * Se requer confirmação antes de salvar
     */
    protected bool $requiresConfirmation = false;

    /**
     * Título da confirmação
     */
    protected string $confirmationTitle = 'Confirmar alteração';

    /**
     * Descrição da confirmação
     */
    protected string $confirmationDescription = 'Deseja salvar a alteração?';

    /**
     * Placeholder do input
     */
    protected ?string $placeholder = null;

    /**
     * Opções para select/radio
     */
    protected array $options = [];

    /**
     * Se deve salvar automaticamente
     */
    protected bool $autosave = true;

    /**
     * Debounce em milissegundos
     */
    protected int $debounceMs = 500;

    /**
     * Definir tipo de edição
     */
    public function editType(string $editType): static
    {
        $this->editType = $editType;
        return $this;
    }

    /**
     * Edição como texto
     */
    public function asText(): static
    {
        return $this->editType('text');
    }

    /**
     * Edição como select
     */
    public function asSelect(): static
    {
        return $this->editType('select');
    }

    /**
     * Edição como textarea
     */
    public function asTextarea(): static
    {
        return $this->editType('textarea');
    }

    /**
     * Edição como number
     */
    public function asNumber(): static
    {
        return $this->editType('number');
    }

    /**
     * Edição como email
     */
    public function asEmail(): static
    {
        return $this->editType('email');
    }

    /**
     * Edição como moeda
     */
    public function asCurrency(): static
    {
        return $this->editType('currency');
    }

    /**
     * Definir rota de atualização
     */
    public function updateRoute(string $route, array $parameters = []): static
    {
        $this->updateRoute = $route;
        $this->updateRouteParameters = $parameters;
        return $this;
    }

    /**
     * Requer confirmação
     */
    public function requiresConfirmation(bool $requiresConfirmation = true): static
    {
        $this->requiresConfirmation = $requiresConfirmation;
        return $this;
    }

    /**
     * Título da confirmação
     */
    public function confirmationTitle(string $title): static
    {
        $this->confirmationTitle = $title;
        return $this;
    }

    /**
     * Descrição da confirmação
     */
    public function confirmationDescription(string $description): static
    {
        $this->confirmationDescription = $description;
        return $this;
    }

    /**
     * Placeholder
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Opções para select
     */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Auto save
     */
    public function autosave(bool $autosave = true): static
    {
        $this->autosave = $autosave;
        return $this;
    }

    /**
     * Debounce
     */
    public function debounce(int $debounceMs): static
    {
        $this->debounceMs = $debounceMs;
        return $this;
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        return [
            'value' => $value,
            'editable' => true,
            'editType' => $this->editType,
            'updateRoute' => $this->updateRoute,
            'updateRouteParameters' => $this->updateRouteParameters,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmationTitle' => $this->confirmationTitle,
            'confirmationDescription' => $this->confirmationDescription,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'autosave' => $this->autosave,
            'debounceMs' => $this->debounceMs,
            'validation' => $this->validationRules,
        ];
    }
} 