<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

class AllTab extends Tab
{
    public function __construct(string $label = 'Todos')
    {
        parent::__construct('todos', $label);
        $this->icon('list')
             ->color('default')
             ->order(0)
             ->content(['type' => 'table'])
             ->tableConfig([
                 'searchable' => true,
                 'sortable' => true,
                 'filterable' => true,
                 'paginated' => true,
                 'selectable' => true,
             ]);
    }
} 