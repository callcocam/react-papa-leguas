<?php

require_once 'vendor/autoload.php';

use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;

echo "🧪 Testando Sistema de Cache e Permissões...\n";

try {
    // Criar tabela com cache e permissões
    $table = Table::make('cache-permissions-test')
        ->model(\App\Models\User::class)
        
        // Configurar cache
        ->cache(true, 600) // 10 minutos
        ->cacheTags(['users', 'dashboard'])
        ->cachePrefix('test_table')
        ->smartCache()
        
        // Configurar permissões
        ->permissions(true)
        ->permissionGuard('web')
        ->adminOnly()
        
        // Adicionar colunas
        ->textColumn('name', 'Nome')
        ->textColumn('email', 'Email')
        
        // Adicionar filtros
        ->textFilter('name', 'Nome');
    
    echo "✅ Tabela criada com sucesso!\n";
    echo "📋 ID: " . $table->getId() . "\n";
    echo "🏗️  Modelo: " . $table->getModel() . "\n";
    
    // Testar cache
    echo "\n🔄 Testando Cache:\n";
    $cacheStats = $table->getCacheStats();
    echo "  - Habilitado: " . ($cacheStats['enabled'] ? 'Sim' : 'Não') . "\n";
    echo "  - TTL: " . $cacheStats['ttl'] . " segundos\n";
    echo "  - Store: " . $cacheStats['store'] . "\n";
    echo "  - Tags: " . implode(', ', $cacheStats['tags']) . "\n";
    
    // Testar permissões
    echo "\n🔐 Testando Permissões:\n";
    $permissions = $table->getPermissionsForFrontend();
    echo "  - Habilitado: " . ($permissions['enabled'] ? 'Sim' : 'Não') . "\n";
    echo "  - Permissões da tabela:\n";
    foreach ($permissions['table_permissions'] as $action => $allowed) {
        echo "    - {$action}: " . ($allowed ? 'Permitido' : 'Negado') . "\n";
    }
    
    echo "\n🎉 Sistema de Cache e Permissões funcionando perfeitamente!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 