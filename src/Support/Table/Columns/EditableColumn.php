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
use \Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;

class EditableColumn extends TextColumn
{
    use EvaluatesClosures;

    protected string $view = 'react-papa-leguas::columns.editable-column';
    protected ?Closure $updateCallback = null;
    protected array $options = [];
    protected ?string $fetchUrl = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type('editable'); // Define o tipo da coluna como 'editable'
        $this->renderAs('editable-text'); // Define o renderAs como 'editable-text' por padrão
    }

    /**
     * Define a lógica que será executada para atualizar o valor.
     * O callback receberá o modelo e o novo valor.
     *
     * @param Closure $callback (Model $record, mixed $value): bool
     */
    public function updateUsing(?Closure $callback): self
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
        if ($this->getUpdate() === null) {
            return false;
        }

        return (bool) $this->evaluate($this->getUpdate(), [
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
        $data['type'] = 'editable';

        $data['update_using'] = $this->getUpdate() !== null;

        if (!empty($this->getOptions())) {
            $data['options'] = $this->getOptions();
        }

        if ($this->fetchUrl) {
            $data['fetchUrl'] = $this->fetchUrl;
        }

        return $data;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        if (!empty($options)) {
            $this->renderAs('editable-select');
        } else {
            $this->renderAs('editable-text');
        }

        return $this;
    }

    /**
     * Define uma URL de API para buscar as opções dinamicamente.
     */
    public function fetchOptionsFrom(string $url): static
    {
        $this->fetchUrl = $url;
        $this->renderAs('editable-select');

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getUpdate(): ?Closure
    {
        return $this->updateCallback;
    }
} 