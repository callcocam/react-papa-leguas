<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

use Callcocam\ReactPapaLeguas\Core\Table\Columns\Column;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\NumberColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\ImageColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\EditableColumn;

trait HasColumns
{
    /**
     * The table columns.
     *
     * @var array
     */
    protected array $columns = [];

    /**
     * Add a column to the table.
     *
     * @param Column $column
     * @return static
     */
    public function column(Column $column): static
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * Add multiple columns to the table.
     *
     * @param array $columns
     * @return static
     */
    public function columns(array $columns): static
    {
        foreach ($columns as $column) {
            $this->column($column);
        }
        return $this;
    }

    /**
     * Add a text column.
     *
     * @param string $key
     * @param string|null $label
     * @return TextColumn
     */
    public function textColumn(string $key, ?string $label = null): TextColumn
    {
        $column = TextColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add an editable column.
     *
     * @param string $key
     * @param string|null $label
     * @return EditableColumn
     */
    public function editableColumn(string $key, ?string $label = null): EditableColumn
    {
        $column = EditableColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add a number column.
     *
     * @param string $key
     * @param string|null $label
     * @return NumberColumn
     */
    public function numberColumn(string $key, ?string $label = null): NumberColumn
    {
        $column = NumberColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add a date column.
     *
     * @param string $key
     * @param string|null $label
     * @return DateColumn
     */
    public function dateColumn(string $key, ?string $label = null): DateColumn
    {
        $column = DateColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add a boolean column.
     *
     * @param string $key
     * @param string|null $label
     * @return BooleanColumn
     */
    public function booleanColumn(string $key, ?string $label = null): BooleanColumn
    {
        $column = BooleanColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add a badge column.
     *
     * @param string $key
     * @param string|null $label
     * @return BadgeColumn
     */
    public function badgeColumn(string $key, ?string $label = null): BadgeColumn
    {
        $column = BadgeColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Add an image column.
     *
     * @param string $key
     * @param string|null $label
     * @return ImageColumn
     */
    public function imageColumn(string $key, ?string $label = null): ImageColumn
    {
        $column = ImageColumn::make($key, $label);
        $this->column($column);
        return $column;
    }

    /**
     * Get the table columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return array_map(function ($column) {
            return $column->toArray();
        }, $this->columns);
    }

    /**
     * Get visible columns only.
     *
     * @return array
     */
    public function getVisibleColumns(): array
    {
        return array_filter($this->getColumns(), function ($column) {
            return !($column['hidden'] ?? false);
        });
    }

    /**
     * Get searchable columns only.
     *
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return array_filter($this->getColumns(), function ($column) {
            return $column['searchable'] ?? false;
        });
    }

    /**
     * Get sortable columns only.
     *
     * @return array
     */
    public function getSortableColumns(): array
    {
        return array_filter($this->getColumns(), function ($column) {
            return $column['sortable'] ?? false;
        });
    }

    /**
     * Get editable columns only.
     *
     * @return array
     */
    public function getEditableColumns(): array
    {
        return array_filter($this->getColumns(), function ($column) {
            return $column['type'] === 'editable';
        });
    }
}
