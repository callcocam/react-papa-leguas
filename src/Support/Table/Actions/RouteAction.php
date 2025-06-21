<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Closure;

/**
 * Ação baseada em rota Laravel
 * 
 * Representa uma ação que navega para uma rota específica
 */
class RouteAction extends Action
{
    /**
     * Nome da rota
     */
    protected ?string $route = null;

    /**
     * Parâmetros da rota
     */
    protected array $parameters = [];

    /**
     * Closure para parâmetros dinâmicos
     */
    protected ?Closure $parametersUsing = null;

    /**
     * Método HTTP (GET, POST, PUT, DELETE)
     */
    protected string $method = 'GET';

    /**
     * Se deve abrir em nova aba
     */
    protected bool $openInNewTab = false;

    /**
     * Define a rota da ação
     */
    public function route(string $route, array $parameters = []): static
    {
        $this->route = $route;
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Define parâmetros da rota
     */
    public function parameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Define closure para parâmetros dinâmicos
     */
    public function parametersUsing(Closure $callback): static
    {
        $this->parametersUsing = $callback;
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
     * Obtém os parâmetros da rota para um item
     */
    protected function getParameters($item = null, array $context = []): array
    {
        if ($this->parametersUsing) {
            return $this->evaluate($this->parametersUsing, [
                'item' => $item,
                'context' => $context,
                'action' => $this,
            ]) ?? $this->parameters;
        }
        // Se tem item e parâmetros vazios, tenta inferir o parâmetro da rota a partir do modelo.
        if ($item && empty($this->parameters)) {
            $record = is_array($item) ? (object) $item : $item;
            if (isset($this->context['model']) && is_object($record)) {
                $modelClass = $this->context['model'];
                $model = new $modelClass();

                // Determina o nome do parâmetro da rota (ex: 'user' para o model User)
                $parameterName = strtolower(class_basename($modelClass));

                // Obtém o valor da chave da rota (normalmente o ID)
                $routeKeyValue = $record->{$model->getRouteKeyName()};

                return [$parameterName => $routeKeyValue];
            }
            // Fallback para o comportamento antigo se o modelo não estiver no contexto
            if (is_object($record) && isset($record->id)) {
                $parameterName = strtolower(class_basename($record));
                return [$parameterName => $record->id];
            } elseif (is_array($item) && isset($item['id'])) {
                $parameterName = strtolower(class_basename($item));
                return [$parameterName => $item['id']];
            }
        }

        return $this->parameters;
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

        if (!$this->route) {
            return null;
        }

        try {
            $parameters = $this->getParameters($item, $context);

            return route($this->route, $parameters);
        } catch (\Exception $e) {
            // Log do erro se necessário
            return null;
        }
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
        return 'route';
    }

    /**
     * Serializa para array incluindo dados específicos da rota
     */
    public function toArray($item = null, array $context = []): array
    {
        $array = parent::toArray($item, $context);

        if (empty($array)) {
            return $array;
        }

        $array['url'] = $this->getUrl($item, $context);
        $array['route'] = $this->route ?? null;
        $array['parameters'] = $this->getParameters($item, $context);
        $array['open_in_new_tab'] = $this->openInNewTab;
        
        return $array;
    }
}
