<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Core\Table\Table;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\EditableColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\NumberColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Columns\ImageColumn;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\DateFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\CreateHeaderAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\EditRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\DeleteBulkAction;
use Callcocam\ReactPapaLeguas\Models\Tenant;

describe('Table System', function () {
    
    describe('Basic Table Creation', function () {
        it('can create a basic table', function () {
            $table = Table::make()
                ->id('test-table')
                ->model(Tenant::class);

            expect($table)->toBeInstanceOf(Table::class);
            expect($table->getId())->toBe('test-table');
            expect($table->getModel())->toBe(Tenant::class);
        });

        it('can set table configuration', function () {
            $table = Table::make()
                ->searchable()
                ->sortable()
                ->paginated()
                ->perPage(25);

            expect($table->isSearchable())->toBeTrue();
            expect($table->isSortable())->toBeTrue();
            expect($table->isPaginated())->toBeTrue();
            expect($table->getPerPage())->toBe(25);
        });
    });

    describe('Columns System', function () {
        it('can create text columns', function () {
            $column = TextColumn::make('name', 'Nome')
                ->searchable()
                ->copyable();

            expect($column->getKey())->toBe('name');
            expect($column->getLabel())->toBe('Nome');
            expect($column->isSearchable())->toBeTrue();
            expect($column->isCopyable())->toBeTrue();
        });

        it('can create editable columns', function () {
            $column = EditableColumn::make('status', 'Status')
                ->asSelect()
                ->updateRoute('test.update')
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'inactive', 'label' => 'Inativo'],
                ]);

            expect($column->getInputType())->toBe('select');
            expect($column->getUpdateRoute())->toBe('test.update');
            expect($column->getOptions())->toHaveCount(2);
        });

        it('can create number columns', function () {
            $column = NumberColumn::make('price', 'Preço')
                ->currency('BRL')
                ->precision(2);

            expect($column->getCurrency())->toBe('BRL');
            expect($column->getPrecision())->toBe(2);
        });

        it('can create badge columns', function () {
            $column = BadgeColumn::make('status', 'Status')
                ->statusColors()
                ->statusIcons();

            expect($column->hasStatusColors())->toBeTrue();
            expect($column->hasStatusIcons())->toBeTrue();
        });

        it('can create date columns', function () {
            $column = DateColumn::make('created_at', 'Criado em')
                ->dateOnly()
                ->relative();

            expect($column->getFormat())->toBe('d/m/Y');
            expect($column->isRelative())->toBeTrue();
            expect($column->shouldShowTime())->toBeFalse();
        });

        it('can create boolean columns', function () {
            $column = BooleanColumn::make('active', 'Ativo')
                ->activeInactive()
                ->asBadge();

            expect($column->getTrueLabel())->toBe('Ativo');
            expect($column->getFalseLabel())->toBe('Inativo');
            expect($column->shouldDisplayAsBadge())->toBeTrue();
        });

        it('can create image columns', function () {
            $column = ImageColumn::make('avatar', 'Avatar')
                ->circular()
                ->size(64)
                ->defaultImage('/default-avatar.png');

            expect($column->isCircular())->toBeTrue();
            expect($column->getSize())->toBe(64);
            expect($column->getDefaultImage())->toBe('/default-avatar.png');
        });

        it('can add columns to table', function () {
            $table = Table::make()
                ->textColumn('name', 'Nome')
                ->numberColumn('price', 'Preço')
                ->dateColumn('created_at', 'Criado em');

            $columns = $table->getColumns();
            expect($columns)->toHaveCount(3);
            expect($columns[0])->toBeInstanceOf(TextColumn::class);
            expect($columns[1])->toBeInstanceOf(NumberColumn::class);
            expect($columns[2])->toBeInstanceOf(DateColumn::class);
        });
    });

    describe('Filters System', function () {
        it('can create text filters', function () {
            $filter = TextFilter::make('name', 'Nome')
                ->placeholder('Buscar por nome...')
                ->contains();

            expect($filter->getKey())->toBe('name');
            expect($filter->getLabel())->toBe('Nome');
            expect($filter->getPlaceholder())->toBe('Buscar por nome...');
            expect($filter->getOperator())->toBe('contains');
        });

        it('can create select filters', function () {
            $filter = SelectFilter::make('status', 'Status')
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'inactive', 'label' => 'Inativo'],
                ])
                ->multiple();

            expect($filter->getOptions())->toHaveCount(2);
            expect($filter->isMultiple())->toBeTrue();
        });

        it('can create date filters', function () {
            $filter = DateFilter::make('created_at', 'Data de criação')
                ->includeTime(false);

            expect($filter->shouldIncludeTime())->toBeFalse();
        });

        it('can create boolean filters', function () {
            $filter = BooleanFilter::make('active', 'Ativo')
                ->activeInactive()
                ->asSelect();

            expect($filter->getTrueLabel())->toBe('Ativo');
            expect($filter->getFalseLabel())->toBe('Inativo');
            expect($filter->getDisplayType())->toBe('select');
        });

        it('can add filters to table', function () {
            $table = Table::make()
                ->textFilter('name', 'Nome')
                ->selectFilter('status', 'Status')
                ->dateFilter('created_at', 'Criado em');

            $filters = $table->getFilters();
            expect($filters)->toHaveCount(3);
            expect($filters[0])->toBeInstanceOf(TextFilter::class);
            expect($filters[1])->toBeInstanceOf(SelectFilter::class);
            expect($filters[2])->toBeInstanceOf(DateFilter::class);
        });
    });

    describe('Actions System', function () {
        it('can create header actions', function () {
            $action = CreateHeaderAction::make()
                ->route('test.create')
                ->label('Criar')
                ->color('primary');

            expect($action->getRoute())->toBe('test.create');
            expect($action->getLabel())->toBe('Criar');
            expect($action->getColor())->toBe('primary');
        });

        it('can create row actions', function () {
            $action = EditRowAction::make()
                ->route('test.edit')
                ->label('Editar')
                ->color('primary');

            expect($action->getRoute())->toBe('test.edit');
            expect($action->getLabel())->toBe('Editar');
            expect($action->getColor())->toBe('primary');
        });

        it('can create bulk actions', function () {
            $action = DeleteBulkAction::make()
                ->route('test.bulk-delete')
                ->label('Excluir selecionados')
                ->confirmationTitle('Confirmar exclusão');

            expect($action->getRoute())->toBe('test.bulk-delete');
            expect($action->getLabel())->toBe('Excluir selecionados');
            expect($action->getConfirmationTitle())->toBe('Confirmar exclusão');
        });

        it('can add actions to table', function () {
            $table = Table::make()
                ->headerActions([
                    CreateHeaderAction::make()->route('test.create'),
                ])
                ->rowActions([
                    EditRowAction::make()->route('test.edit'),
                ])
                ->bulkActions([
                    DeleteBulkAction::make()->route('test.bulk-delete'),
                ]);

            expect($table->getHeaderActions())->toHaveCount(1);
            expect($table->getRowActions())->toHaveCount(1);
            expect($table->getBulkActions())->toHaveCount(1);
        });
    });

    describe('Table Data Generation', function () {
        it('can generate table data structure', function () {
            $table = Table::make()
                ->id('test-table')
                ->textColumn('name', 'Nome')
                ->textFilter('name', 'Nome');

            $data = $table->getTableData();

            expect($data)->toHaveKey('id');
            expect($data)->toHaveKey('columns');
            expect($data)->toHaveKey('filters');
            expect($data)->toHaveKey('searchable');
            expect($data)->toHaveKey('sortable');
            expect($data)->toHaveKey('paginated');
        });

        it('can generate table props', function () {
            $table = Table::make()
                ->id('test-table')
                ->model(Tenant::class)
                ->textColumn('name', 'Nome');

            $props = $table->getProps();

            expect($props)->toHaveKey('table');
            expect($props)->toHaveKey('data');
            expect($props)->toHaveKey('meta');
        });
    });

    describe('Search and Sorting', function () {
        it('can configure search', function () {
            $table = Table::make()
                ->searchable()
                ->searchPlaceholder('Buscar...')
                ->searchColumns(['name', 'email']);

            expect($table->isSearchable())->toBeTrue();
            expect($table->getSearchPlaceholder())->toBe('Buscar...');
            expect($table->getSearchColumns())->toBe(['name', 'email']);
        });

        it('can configure sorting', function () {
            $table = Table::make()
                ->sortable()
                ->defaultSort('name', 'asc');

            expect($table->isSortable())->toBeTrue();
            expect($table->getDefaultSort())->toBe(['column' => 'name', 'direction' => 'asc']);
        });
    });

    describe('Pagination', function () {
        it('can configure pagination', function () {
            $table = Table::make()
                ->paginated()
                ->perPage(25)
                ->perPageOptions([10, 25, 50, 100]);

            expect($table->isPaginated())->toBeTrue();
            expect($table->getPerPage())->toBe(25);
            expect($table->getPerPageOptions())->toBe([10, 25, 50, 100]);
        });
    });

    describe('Column Serialization', function () {
        it('serializes text column correctly', function () {
            $column = TextColumn::make('name', 'Nome')
                ->searchable()
                ->copyable();

            $array = $column->toArray();

            expect($array)->toHaveKey('key', 'name');
            expect($array)->toHaveKey('label', 'Nome');
            expect($array)->toHaveKey('type', 'text');
            expect($array)->toHaveKey('searchable', true);
            expect($array)->toHaveKey('copyable', true);
        });

        it('serializes editable column correctly', function () {
            $column = EditableColumn::make('status', 'Status')
                ->asSelect()
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                ]);

            $array = $column->toArray();

            expect($array)->toHaveKey('inputType', 'select');
            expect($array)->toHaveKey('options');
            expect($array['options'])->toHaveCount(1);
        });

        it('serializes date column correctly', function () {
            $column = DateColumn::make('created_at', 'Criado em')
                ->datetime()
                ->relative();

            $array = $column->toArray();

            expect($array)->toHaveKey('format');
            expect($array)->toHaveKey('relative', true);
            expect($array)->toHaveKey('showTime', true);
        });
    });

    describe('Filter Serialization', function () {
        it('serializes text filter correctly', function () {
            $filter = TextFilter::make('name', 'Nome')
                ->placeholder('Buscar...')
                ->contains();

            $array = $filter->toArray();

            expect($array)->toHaveKey('key', 'name');
            expect($array)->toHaveKey('label', 'Nome');
            expect($array)->toHaveKey('type', 'text');
            expect($array)->toHaveKey('placeholder', 'Buscar...');
            expect($array)->toHaveKey('operator', 'contains');
        });

        it('serializes select filter correctly', function () {
            $filter = SelectFilter::make('status', 'Status')
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                ])
                ->multiple();

            $array = $filter->toArray();

            expect($array)->toHaveKey('options');
            expect($array)->toHaveKey('multiple', true);
            expect($array['options'])->toHaveCount(1);
        });
    });
});
