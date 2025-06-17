<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Examples;

use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\HeaderAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RowAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;
use Callcocam\ReactPapaLeguas\Models\Tenant;

/**
 * Example of how to use the Table class with all features
 */
class TenantTableExample
{
    public static function create(): Table
    {
        return Table::make()
            ->id('tenants-table')
            ->model(Tenant::class)
            ->query(fn() => Tenant::query())
            
            // Configure columns
            ->textColumn('name', 'Nome')
                ->searchable()
            ->textColumn('slug', 'Slug')
            ->badgeColumn('status', 'Status')
            ->dateColumn('created_at', 'Criado em')
            ->booleanColumn('active', 'Ativo')
            
            // Configure filters  
            // ->textFilter('name', 'Nome')
            
            // Configure basic settings
            ->searchable()
            ->sortable()
            ->paginated()
            
            // Configure header actions
            ->headerActions([
                HeaderAction::make('create')
                    ->route('landlord.tenants.create')
                    ->label('Novo Tenant')
                    ->color('primary'),
                    
                HeaderAction::make('export')
                    ->route('landlord.tenants.export')
                    ->label('Exportar')
                    ->color('secondary'),
            ])
            
            // Configure row actions
            ->rowActions([
                RowAction::make('view')
                    ->route('landlord.tenants.show')
                    ->label('Ver')
                    ->color('secondary'),
                    
                RowAction::make('edit')
                    ->route('landlord.tenants.edit')
                    ->label('Editar')
                    ->color('primary'),
                    
                RowAction::make('delete')
                    ->route('landlord.tenants.destroy')
                    ->label('Excluir')
                    ->color('destructive')
                    ->requiresConfirmation()
                    ->confirmationTitle('Confirmar exclusão')
                    ->confirmationDescription('Esta ação não pode ser desfeita. O tenant será excluído permanentemente.'),
            ])
            
            // Configure bulk actions
            ->bulkActions([
                BulkAction::make('activate')
                    ->route('landlord.tenants.bulk-activate')
                    ->label('Ativar selecionados')
                    ->color('success'),
                    
                BulkAction::make('delete')
                    ->route('landlord.tenants.bulk-delete')
                    ->label('Excluir selecionados')
                    ->color('destructive')
                    ->confirmationTitle('Confirmar exclusão em massa')
                    ->confirmationDescription('Esta ação não pode ser desfeita. Os tenants selecionados serão excluídos permanentemente.'),
            ]);
    }
    
    /**
     * Get table data for API response
     */
    public static function getTableData(): array
    {
        $table = self::create();
        return $table->getTableData();
    }
    
    /**
     * Get table props for Inertia response
     */
    public static function getTableProps(): array
    {
        $table = self::create();
        return $table->getProps();
    }
}
