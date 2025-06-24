<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Views;


class KanbanView extends View
{
    protected array $columns = [];

    public function __construct(string $id, string $label)
    {
        parent::__construct($id, $label);
        $this->icon('Kanban');
        // Habilitar drag and drop por padrão
        $this->config['dragAndDrop'] = true;
        $this->config['height'] = '700px';
        $this->config['apiEndpoint'] = '/api/admin/tickets/kanban/move-card';
    }

    public function columns(array $columns): self
    {
        $this->config['columns'] = $columns;
        return $this;
    }

    public function workflow_slug(string $workflow_slug): self
    {
        $this->config['workflow_slug'] = $workflow_slug;
        return $this;
    }

    public function workflow_name(string $workflow_name): self
    {
        $this->config['workflow_name'] = $workflow_name;
        return $this;
    }

    public function getWorkflowSlug(): string
    {
        return $this->config['workflow_slug'];
    }

    public function getWorkflowName(): string
    {
        return $this->config['workflow_name'];
    }

    public function getColumns(): array
    {
        return $this->config['columns'];
    }

    public function dragAndDrop(bool $enabled = true): self
    {
        $this->config['dragAndDrop'] = $enabled;
        return $this;
    }

    public function height(string $height): self
    {
        $this->config['height'] = $height;
        return $this;
    }

    public function apiEndpoint(string $endpoint): self
    {
        $this->config['apiEndpoint'] = $endpoint;
        return $this;
    }
}
