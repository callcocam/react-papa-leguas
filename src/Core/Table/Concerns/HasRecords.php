<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

use Illuminate\Http\Request;

trait HasRecords
{
    /**
     * Get table data with all features applied.
     *
     * @param Request|null $request
     * @return array
     */
    public function getData(?Request $request = null): array
    {
        $request = $request ?? request();
        
        $query = $this->getQuery();

        // Apply search
        if (method_exists($this, 'applySearch')) {
            $query = $this->applySearch($query, $request->get('search'));
        }

        // Apply filters
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $request->get('filters', []));
        }

        // Apply sorting
        if (method_exists($this, 'applySorting')) {
            $query = $this->applySorting(
                $query,
                $request->get('sort'),
                $request->get('direction')
            );
        }

        // Get pagination data
        if (method_exists($this, 'getPaginationData')) {
            return $this->getPaginationData($query, $request->get('per_page'));
        }

        // Fallback to simple get
        return [
            'data' => $query->get()->toArray(),
            'pagination' => null,
        ];
    }

    /**
     * Get complete table props for frontend.
     *
     * @param Request|null $request
     * @return array
     */
    public function getTableData(?Request $request = null): array
    {
        $request = $request ?? request();
        
        $data = $this->getData($request);

        $props = [
            'component' => $this->component,
            'columns' => method_exists($this, 'getColumns') ? $this->getColumns() : $this->columns,
            'data' => $data['data'],
            'pagination' => $data['pagination'],
        ];

        // Add actions if available
        if (method_exists($this, 'getActions')) {
            $props['actions'] = $this->getActions();
        }

        // Add filters if available
        if (method_exists($this, 'getFilters')) {
            $props['filters'] = $this->getFilters();
        }

        // Add search data if available
        if (method_exists($this, 'getSearchData')) {
            $props = array_merge($props, $this->getSearchData($request->get('search')));
        }

        // Add sorting data if available
        if (method_exists($this, 'getSortingData')) {
            $props = array_merge($props, $this->getSortingData(
                $request->get('sort'),
                $request->get('direction')
            ));
        }

        return $props;
    }

    /**
     * Transform a single record for the table.
     *
     * @param mixed $record
     * @return array
     */
    public function transformRecord($record): array
    {
        if (is_array($record)) {
            return $record;
        }

        if (method_exists($record, 'toArray')) {
            return $record->toArray();
        }

        return (array) $record;
    }

    /**
     * Transform multiple records for the table.
     *
     * @param mixed $records
     * @return array
     */
    public function transformRecords($records): array
    {
        if (is_array($records)) {
            return array_map([$this, 'transformRecord'], $records);
        }

        if (method_exists($records, 'toArray')) {
            return $records->toArray();
        }

        return [];
    }

    /**
     * Get record count from query.
     *
     * @param Request|null $request
     * @return int
     */
    public function getRecordCount(?Request $request = null): int
    {
        $request = $request ?? request();
        
        $query = $this->getQuery();

        // Apply search
        if (method_exists($this, 'applySearch')) {
            $query = $this->applySearch($query, $request->get('search'));
        }

        // Apply filters
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $request->get('filters', []));
        }

        return $query->count();
    }
}
