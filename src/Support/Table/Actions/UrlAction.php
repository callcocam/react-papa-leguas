<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ação baseada em URL direta
 * 
 * Representa uma ação que navega para uma URL específica
 */
class UrlAction extends Action
{
    /**
     * URL da ação
     */
    protected ?string $url = null;

    /**
     * Método HTTP (GET, POST, PUT, DELETE)
     */
    protected string $method = 'GET';

    /**
     * Se deve abrir em nova aba
     */
    protected bool $openInNewTab = false;

    /**
     * Define a URL da ação
     */
    public function url(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Define o método HTTP
     */
    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Marca para abrir em nova aba
     */
    public function openInNewTab(bool $openInNewTab = true): static
    {
        $this->openInNewTab = $openInNewTab;
        return $this;
    }

    /**
     * Configurações rápidas para métodos HTTP
     */
    public function get(): static
    {
        return $this->method('GET');
    }

    public function post(): static
    {
        return $this->method('POST');
    }

    public function put(): static
    {
        return $this->method('PUT');
    }

    public function patch(): static
    {
        return $this->method('PATCH');
    }

    public function deleteMethod(): static
    {
        return $this->method('DELETE');
    }

    /**
     * Obtém a URL da ação
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

        return $this->url;
    }

    /**
     * Obtém o método HTTP
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Obtém o tipo da ação
     */
    public function getType(): string
    {
        return 'url';
    }

    /**
     * Serializa para array incluindo dados específicos da URL
     */
    public function toArray($item = null, array $context = []): array
    {
        $array = parent::toArray($item, $context);

        if (empty($array)) {
            return $array;
        }

        $array['open_in_new_tab'] = $this->openInNewTab;

        return $array;
    }
} 