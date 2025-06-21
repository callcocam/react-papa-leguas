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

    /**
     * Construtor que define configurações padrão
     */
    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        
        // Define o renderAs padrão como 'editable-text'
        // Será sobrescrito pelo método options() se opções forem definidas
        $this->renderAs('editable-text');
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
        return 'editable'; 
    }

    /**
     * Adiciona as propriedades de edição à serialização da coluna.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Manter o tipo como 'editable' para identificação no frontend
        $data['type'] = 'editable';
        
        // Adicionar propriedades específicas de edição
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