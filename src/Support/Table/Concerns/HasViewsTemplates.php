<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

trait HasViewsTemplates
{

    protected array $views = [];

    protected function getViews(): array
    {
        return $this->views;
    }

    protected function getViewsWithWorkflowSupport(): array
    {
        return $this->views;
    }
}