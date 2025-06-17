<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Examples;

use Callcocam\ReactPapaLeguas\Core\Table\Table;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\CreateHeaderAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\ExportHeaderAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\ViewRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\EditRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\DeleteRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\DeleteBulkAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\ActivateBulkAction;
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
                ->copyable()
            ->textColumn('slug', 'Slug')
                ->copyable()
            ->editableColumn('status', 'Status')
                ->asSelect()
                ->updateRoute('landlord.tenants.update-status')
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'inactive', 'label' => 'Inativo'],
                    ['value' => 'suspended', 'label' => 'Suspenso'],
                ])
                ->requiresConfirmation()
                ->confirmationTitle('Alterar status')
                ->confirmationDescription('Deseja alterar o status deste tenant?')
            ->badgeColumn('status', 'Status Atual')
                ->statusColors()
                ->statusIcons()
                ->statusLabels()
            ->dateColumn('created_at', 'Criado em')
                ->dateOnly()
                ->relative()
            ->booleanColumn('active', 'Ativo')
                ->activeInactive()
                ->asBadge()
            ->editableColumn('name', 'Nome (Editável)')
                ->asText()
                ->updateRoute('landlord.tenants.update-field')
                ->autosave()
                ->debounce(1000)
                ->validation(['required', 'string', 'max:255'])
            
            // Configure filters  
            ->textFilter('name', 'Nome')
                ->placeholder('Buscar por nome...')
                ->contains()
            ->selectFilter('status', 'Status')
                ->statusOptions()
                ->clearable()
            ->booleanFilter('active', 'Ativo')
                ->activeInactive()
                ->asSelect()
            ->dateRangeFilter('created_at', 'Data de criação')
                ->includeTime(false)
            ->selectFilter('domain_type', 'Tipo de Domínio')
                ->options([
                    ['value' => 'subdomain', 'label' => 'Subdomínio'],
                    ['value' => 'domain', 'label' => 'Domínio próprio'],
                ])
                ->multiple()
            
            // Configure search
            ->searchable()
            ->searchPlaceholder('Buscar tenants...')
            ->searchColumns(['name', 'slug', 'domain'])
            
            // Configure sorting
            ->defaultSort('name', 'asc')
            ->sortable()
            
            // Configure pagination
            ->perPage(15)
            ->perPageOptions([10, 15, 25, 50, 100])
            ->paginated()
            
            // Configure header actions
            ->headerActions([
                CreateHeaderAction::make()
                    ->route('landlord.tenants.create')
                    ->label('Novo Tenant')
                    ->color('primary'),
                    
                ExportHeaderAction::make()
                    ->route('landlord.tenants.export')
                    ->label('Exportar')
                    ->color('secondary'),
            ])
            
            // Configure row actions
            ->rowActions([
                ViewRowAction::make()
                    ->route('landlord.tenants.show')
                    ->label('Ver')
                    ->color('secondary'),
                    
                EditRowAction::make()
                    ->route('landlord.tenants.edit')
                    ->label('Editar')
                    ->color('primary'),
                    
                DeleteRowAction::make()
                    ->route('landlord.tenants.destroy')
                    ->label('Excluir')
                    ->color('destructive')
                    ->requiresConfirmation()
                    ->confirmationTitle('Confirmar exclusão')
                    ->confirmationDescription('Esta ação não pode ser desfeita. O tenant será excluído permanentemente.'),
            ])
            
            // Configure bulk actions
            ->bulkActions([
                ActivateBulkAction::make()
                    ->route('landlord.tenants.bulk-activate')
                    ->label('Ativar selecionados')
                    ->color('success'),
                    
                DeleteBulkAction::make()
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
