<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

use Callcocam\ReactPapaLeguas\Core\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\BulkAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\HeaderAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\RowAction;

trait HasActions
{
    /**
     * The table actions.
     *
     * @var array
     */
    protected array $actions = [];

    /**
     * Add an action to the table.
     *
     * @param Action $action
     * @return static
     */
    public function action(Action $action): static
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Add multiple actions to the table.
     *
     * @param array $actions
     * @return static
     */
    public function actions(array $actions): static
    {
        foreach ($actions as $action) {
            $this->action($action);
        }

        return $this;
    }

    /**
     * Add bulk actions to the table.
     *
     * @param array $actions
     * @return static
     */
    public function bulkActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof BulkAction) {
                $this->action($action);
            }
        }

        return $this;
    }

    /**
     * Add header actions to the table.
     *
     * @param array $actions
     * @return static
     */
    public function headerActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof HeaderAction) {
                $this->action($action);
            }
        }

        return $this;
    }

    /**
     * Add row actions to the table.
     *
     * @param array $actions
     * @return static
     */
    public function rowActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof RowAction) {
                $this->action($action);
            }
        }

        return $this;
    }

    /**
     * Get the table actions.
     *
     * @return array
     */
    public function getActions(): array
    {
        return array_map(function ($action) {
            return $action->toArray();
        }, $this->actions);
    }

    /**
     * Get bulk actions only.
     *
     * @return array
     */
    public function getBulkActions(): array
    {
        return array_filter($this->getActions(), function ($action) {
            return $action['type'] === 'bulk';
        });
    }

    /**
     * Get header actions only.
     *
     * @return array
     */
    public function getHeaderActions(): array
    {
        return array_filter($this->getActions(), function ($action) {
            return $action['type'] === 'header';
        });
    }

    /**
     * Get row actions only.
     *
     * @return array
     */
    public function getRowActions(): array
    {
        return array_filter($this->getActions(), function ($action) {
            return $action['type'] === 'row';
        });
    }
}
