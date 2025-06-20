<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;

class EditableColumn extends TextColumn
{
    protected ?Closure $updateCallback = null;

    /**
     * Define a lógica que será executada para atualizar o valor.
     * O callback receberá o modelo e o novo valor.
     *
     * @param Closure $callback (Model $record, mixed $value): bool
     */
    public function updateUsing(Closure $callback): static
    {
        $this->updateCallback = $callback;
        return $this;
    }

    /**
     * Verifica se a coluna tem uma lógica de atualização definida.
     */
    public function hasUpdateCallback(): bool
    {
        return $this->updateCallback !== null;
    }

    /**
     * Executa o callback de atualização.
     */
    public function executeUpdate(Model $record, mixed $value): bool
    {
        if (!$this->hasUpdateCallback()) {
            return false;
        }

        return (bool) $this->evaluate($this->updateCallback, [
            'record' => $record,
            'value' => $value,
        ]);
    }

    /**
     * Sobrescreve o tipo para ser identificado no frontend.
     */
    public function getType(): string
    {
        return 'editable-text'; 
    }

    /**
     * Adiciona as propriedades de edição à serialização da coluna.
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'isEditable' => $this->hasUpdateCallback(),
            'updateActionKey' => $this->getKey(), // Usamos a chave da coluna como identificador da ação
        ]);
    }
} 