<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna de ações para tabelas
 */
class ActionsColumn extends Column
{
    protected array $actions = [];

    /**
     * Criar nova instância da coluna de ações
     */
    public static function make(string $key = 'actions', ?string $label = null): static
    {
        return new static($key, $label ?? 'Ações');
    }

    /**
     * Adicionar ação à coluna
     */
    public function addAction(string $name, array $config): static
    {
        $this->actions[$name] = $config;
        return $this;
    }

    /**
     * Obter ações configuradas
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Renderizar coluna de ações
     */
    public function render($record, $value = null): string
    {
        $html = '<div class="flex items-center justify-end gap-2">';
        
        foreach ($this->actions as $name => $config) {
            $url = $this->buildActionUrl($config, $record);
            $icon = $config['icon'] ?? 'more-horizontal';
            $label = $config['label'] ?? ucfirst($name);
            $class = $config['class'] ?? 'btn btn-sm btn-outline';
            
            $html .= sprintf(
                '<a href="%s" class="%s" title="%s">
                    <i class="lucide lucide-%s"></i>
                </a>',
                $url,
                $class,
                $label,
                $icon
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Construir URL da ação
     */
    protected function buildActionUrl(array $config, $record): string
    {
        $route = $config['route'] ?? '';
        $params = $config['params'] ?? [];
        
        // Substituir placeholders nos parâmetros
        foreach ($params as $key => $value) {
            if (is_string($value) && str_starts_with($value, ':')) {
                $field = substr($value, 1);
                $params[$key] = $record->{$field} ?? '';
            }
        }
        
        return route($route, $params);
    }
}