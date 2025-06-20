<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditableColumn extends TextColumn
{
    protected string $view = 'react-papa-leguas::columns.editable-column';
    protected ?Closure $updateCallback = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type('editable-text'); // Define o tipo da coluna como 'editable'
        $this->renderAs('editable-text'); // Define o renderAs como 'editable-text'
    }

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
        $data = parent::toArray();

        // Garante que o tipo seja sempre 'editable' no JSON final
        $data['type'] = 'editable';

        return $data;
    }
} 