<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table;

use Callcocam\ReactPapaLeguas\Core\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToId;
use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Core\Table\Concerns\BelongsToQuery;

class Table
{
    use EvaluatesClosures;
    use BelongsToId;
    use BelongsToModel;
    use BelongsToQuery;
    use Concerns\HasActions;
    use Concerns\HasColumns;
    use Concerns\HasFilters;
    use Concerns\HasPagination {
        Concerns\HasPagination::perPage insteadof BelongsToQuery;
        Concerns\HasPagination::perPageOptions insteadof BelongsToQuery;
        Concerns\HasPagination::getPerPage insteadof BelongsToQuery;
        Concerns\HasPagination::getPerPageOptions insteadof BelongsToQuery;
        Concerns\HasPagination::paginated insteadof BelongsToQuery;
        Concerns\HasPagination::isPaginated insteadof BelongsToQuery;
    }
    use Concerns\HasSorting {
        Concerns\HasSorting::defaultSort insteadof BelongsToQuery;
        Concerns\HasSorting::getDefaultSortDirection insteadof BelongsToQuery;
    }
    use Concerns\HasSearch;
    use Concerns\HasRecords;

    /**
     * The component name.
     *
     * @var string
     */
    protected string $component = 'PapaLeguasTable';

    /**
     * Create a new table instance.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Get the component props.
     *
     * @return array
     */
    public function getProps(): array
    {
        return $this->getTableData();
    }

    /**
     * Render the table component.
     *
     * @return array
     */
    public function render(): array
    {
        return [
            'component' => $this->component,
            'props' => $this->getProps(),
        ];
    }

    /**
     * Convert the table to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'component' => $this->component,
            'id' => $this->getId(),
            'props' => $this->getProps(),
        ];
    }
}
