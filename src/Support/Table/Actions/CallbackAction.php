<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Closure;

/**
 * Ação baseada em callback/closure
 * 
 * Representa uma ação customizada que executa um closure
 */
class CallbackAction extends Action
{
    /**
     * Callback da ação
     */
    protected ?Closure $callback = null;

    /**
     * Dados extras para enviar ao frontend
     */
    protected array $data = [];

    /**
     * Tipo da ação (pode ser sobrescrito)
     */
    protected string $type = 'callback';

    /**
     * Define o callback da ação
     */
    public function callback(Closure $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Define dados extras
     */
    public function data(array $data): static
    { 
        $this->data = $data;
        return $this;
    }

    /**
     * Define o tipo da ação
     */
    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Executa a ação
     */
    public function execute($item = null, array $context = []): mixed
    {
        if (!$this->callback) {
            return [
                'success' => false,
                'message' => 'Ação não implementada no backend.',
                'reload' => false,
            ];
        }

        return $this->evaluate($this->callback, [
            'item' => $item,
            'context' => $context,
            'action' => $this,
        ]);
    }

    /**
     * Obtém a URL da ação (não aplicável para callbacks)
     */
    public function getUrl($item = null, array $context = []): ?string
    {
        if ($this->urlUsing) {
            $result = $this->evaluate($this->urlUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]);
            return is_string($result) ? $result : null;
        }

        return null;
    }

    /**
     * Obtém o método HTTP (não aplicável para callbacks)
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * Obtém o tipo da ação
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Serializa para array incluindo dados específicos do callback
     */
    public function toArray($item = null, array $context = []): array
    {
        $array = parent::toArray($item, $context);

        if (empty($array)) {
            return $array;
        }

        $array['data'] = $this->data;
        $array['has_callback'] = !is_null($this->callback);

        return $array;
    }
} 