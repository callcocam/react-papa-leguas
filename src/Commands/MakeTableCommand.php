<?php

namespace Callcocam\ReactPapaLeguas\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'papa-leguas:make-table 
                            {table : Nome da tabela no banco de dados}
                            {--model= : Nome do modelo (opcional)}
                            {--name= : Nome da classe da tabela (opcional)}
                            {--namespace= : Namespace customizado (opcional)}
                            {--output= : Diretório de saída (opcional)}
                            {--force : Sobrescrever arquivos existentes}
                            {--with-frontend : Gerar também a página React}
                            {--with-controller : Gerar também o controller}
                            {--controller-name= : Nome do controller (opcional)}
                            {--controller-namespace= : Namespace do controller (opcional)}';

    /**
     * The console command description.
     */
    protected $description = 'Gera uma tabela Papa Leguas inteligente baseada na estrutura do banco de dados';

    /**
     * Informações da tabela
     */
    protected array $tableInfo = [];
    protected array $columns = [];
    protected array $relationships = [];
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        
        // Verificar se a tabela existe
        if (!Schema::hasTable($tableName)) {
            $this->error("❌ Tabela '{$tableName}' não encontrada no banco de dados.");
            return 1;
        }

        $this->info("🔍 Analisando estrutura da tabela '{$tableName}'...");
        
        // Analisar estrutura da tabela
        $this->analyzeTable($tableName);
        
        // Gerar arquivos
        $this->generateTableClass($tableName);
        
        if ($this->option('with-controller')) {
            $this->generateController($tableName);
        }
        
        if ($this->option('with-frontend')) {
            $this->generateFrontendPage($tableName);
        }
        
        // Mostrar resumo
        $this->showSummary($tableName);
        
        return 0;
    }

    /**
     * Analisar estrutura da tabela
     */
    protected function analyzeTable(string $tableName): void
    {
        // Obter informações das colunas
        $this->columns = Schema::getColumns($tableName);
        
        // Obter informações de chaves estrangeiras
        $foreignKeys = Schema::getForeignKeys($tableName);
        
        // Analisar relacionamentos
        $this->analyzeRelationships($tableName, $foreignKeys);
        
        // Obter informações adicionais da tabela
        $this->tableInfo = [
            'name' => $tableName,
            'singular' => Str::singular($tableName),
            'studly' => Str::studly(Str::singular($tableName)),
            'camel' => Str::camel(Str::singular($tableName)),
            'columns_count' => count($this->columns),
            'has_timestamps' => $this->hasTimestamps(),
            'has_soft_deletes' => $this->hasSoftDeletes(),
            'primary_key' => $this->getPrimaryKey(),
        ];

        $this->line("📊 Tabela analisada:");
        $this->line("   • Colunas: {$this->tableInfo['columns_count']}");
        $this->line("   • Timestamps: " . ($this->tableInfo['has_timestamps'] ? '✅' : '❌'));
        $this->line("   • Soft Deletes: " . ($this->tableInfo['has_soft_deletes'] ? '✅' : '❌'));
        $this->line("   • Relacionamentos: " . count($this->relationships));
    }

    /**
     * Analisar relacionamentos
     */
    protected function analyzeRelationships(string $tableName, array $foreignKeys): void
    {
        foreach ($foreignKeys as $fk) {
            $columnName = $fk['columns'][0] ?? null;
            $referencedTable = $fk['foreign_table'] ?? null;
            $referencedColumn = $fk['foreign_columns'][0] ?? null;

            if ($columnName && $referencedTable) {
                $this->relationships[] = [
                    'type' => 'belongsTo',
                    'column' => $columnName,
                    'table' => $referencedTable,
                    'referenced_column' => $referencedColumn,
                    'method_name' => $this->getRelationshipMethodName($columnName),
                    'model_name' => Str::studly(Str::singular($referencedTable)),
                ];
            }
        }
    }

    /**
     * Gerar classe da tabela
     */
    protected function generateTableClass(string $tableName): void
    {
        $className = $this->option('name') ?: Str::studly($tableName) . 'Table';
        $modelName = $this->option('model') ?: Str::studly(Str::singular($tableName));
        $namespace = $this->option('namespace') ?: 'App\\Tables';
        $outputDir = $this->option('output') ?: app_path('Tables');

        // Criar diretório se não existir
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $filePath = $outputDir . '/' . $className . '.php';

        // Verificar se arquivo já existe
        if (File::exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("Arquivo '{$className}.php' já existe. Deseja sobrescrever?")) {
                $this->info("⏭️ Geração cancelada.");
                return;
            }
        }

        // Gerar conteúdo do arquivo
        $content = $this->generateTableContent($className, $modelName, $namespace);
        
        // Salvar arquivo
        File::put($filePath, $content);
        
        $this->info("📄 Classe da tabela criada: {$filePath}");
    }

    /**
     * Gerar conteúdo da classe da tabela
     */
    protected function generateTableContent(string $className, string $modelName, string $namespace): string
    {
        $stub = File::get(__DIR__ . '/stubs/table.stub');
        
        $relationshipsCode = $this->generateRelationshipsCode();
        $withRelationships = $relationshipsCode ? "\n            ->with([{$relationshipsCode}])" : '';
        
        $replacements = [
            '{{namespace}}' => $namespace,
            '{{className}}' => $className,
            '{{modelName}}' => $modelName,
            '{{tableName}}' => $this->tableInfo['name'],
            '{{tableId}}' => Str::kebab($this->tableInfo['name']) . '-table',
            '{{tableTitle}}' => Str::title(str_replace('_', ' ', $this->tableInfo['name'])),
            '{{columns}}' => $this->generateColumnsCode(),
            '{{filters}}' => $this->generateFiltersCode(),
            '{{actions}}' => $this->generateActionsCode(),
            '{{bulkActions}}' => $this->generateBulkActionsCode(),
            '{{relationships}}' => $relationshipsCode,
            '{{withRelationships}}' => $withRelationships,
            '{{imports}}' => $this->generateImportsCode(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Gerar código das colunas
     */
    protected function generateColumnsCode(): string
    {
        $columns = [];
        
        foreach ($this->columns as $column) {
            $columnType = $this->getColumnType($column);
            $columnName = $column['name'];
            
            // Pular colunas de sistema
            if (in_array($columnName, ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'])) {
                continue;
            }

            $columnCode = $this->generateSingleColumnCode($column, $columnType);
            if ($columnCode) {
                $columns[] = $columnCode;
            }
        }

        // Adicionar coluna de timestamps se existir
        if ($this->tableInfo['has_timestamps']) {
            $columns[] = $this->generateTimestampColumn();
        }

        // Adicionar coluna de ações
        $columns[] = $this->generateActionsColumn();

        return implode(",\n            ", $columns);
    }

    /**
     * Gerar código de uma única coluna
     */
    protected function generateSingleColumnCode(array $column, string $type): string
    {
        $name = $column['name'];
        $label = Str::title(str_replace('_', ' ', $name));
        $nullable = $column['nullable'] ?? false;
        
        switch ($type) {
            case 'email':
                return "TextColumn::make('{$name}')
                ->label('{$label}')
                ->searchable()
                ->copyable()
                ->icon('mail')";
                
            case 'boolean':
                return "BooleanColumn::make('{$name}')
                ->label('{$label}')
                ->trueIcon('check-circle')
                ->falseIcon('x-circle')
                ->trueColor('success')
                ->falseColor('danger')";
                
            case 'date':
                return "DateColumn::make('{$name}')
                ->label('{$label}')
                ->sortable()
                ->dateFormat('d/m/Y')";
                
            case 'datetime':
                return "DateColumn::make('{$name}')
                ->label('{$label}')
                ->sortable()
                ->dateFormat('d/m/Y H:i')";
                
            case 'money':
                return "TextColumn::make('{$name}')
                ->label('{$label}')
                ->sortable()
                ->currency('BRL')
                ->alignRight()";
                
            case 'status':
                return "BadgeColumn::make('{$name}')
                ->label('{$label}')
                ->colors([
                    'active' => 'success',
                    'inactive' => 'secondary',
                    'pending' => 'warning',
                    'cancelled' => 'danger',
                ])";
                
            case 'image':
                return "TextColumn::make('{$name}')
                ->label('{$label}')
                ->renderAsImage()
                ->limit(50)";
                
            case 'relationship':
                $relationship = $this->findRelationshipByColumn($name);
                if ($relationship) {
                    $relationMethod = $relationship['method_name'];
                    return "TextColumn::make('{$relationMethod}.name')
                ->label('{$label}')
                ->searchable()
                ->sortable()";
                }
                return $this->generateDefaultTextColumn($name, $label);
                
            default:
                return $this->generateDefaultTextColumn($name, $label, $nullable);
        }
    }

    /**
     * Gerar coluna de texto padrão
     */
    protected function generateDefaultTextColumn(string $name, string $label, bool $nullable = false): string
    {
        $column = "TextColumn::make('{$name}')
                ->label('{$label}')
                ->searchable()
                ->sortable()";
        
        if ($nullable) {
            $column .= "\n                ->placeholder('N/A')";
        }
        
        return $column;
    }

    /**
     * Gerar coluna de timestamp
     */
    protected function generateTimestampColumn(): string
    {
        return "DateColumn::make('created_at')
                ->label('Criado em')
                ->sortable()
                ->dateFormat('d/m/Y H:i')
                ->since()";
    }

    /**
     * Gerar coluna de ações
     */
    protected function generateActionsColumn(): string
    {
        return "ActionsColumn::make()
                ->label('Ações')
                ->alignRight()";
    }

    /**
     * Determinar tipo da coluna
     */
    protected function getColumnType(array $column): string
    {
        $name = $column['name'];
        $type = strtolower($column['type_name'] ?? '');
        
        // Verificar por nome
        if (Str::contains($name, 'email')) return 'email';
        if (Str::contains($name, ['status', 'state'])) return 'status';
        if (Str::contains($name, ['price', 'amount', 'cost', 'value']) && Str::contains($type, ['decimal', 'float', 'double'])) return 'money';
        if (Str::contains($name, ['image', 'photo', 'avatar', 'picture'])) return 'image';
        if (Str::endsWith($name, '_id') && $name !== 'id') return 'relationship';
        
        // Verificar por tipo
        if (in_array($type, ['boolean', 'bool', 'tinyint(1)'])) return 'boolean';
        if (in_array($type, ['date'])) return 'date';
        if (in_array($type, ['datetime', 'timestamp'])) return 'datetime';
        
        return 'text';
    }

    /**
     * Gerar código dos filtros
     */
    protected function generateFiltersCode(): string
    {
        $filters = [];
        
        foreach ($this->columns as $column) {
            $name = $column['name'];
            $type = $this->getColumnType($column);
            
            // Adicionar apenas filtros relevantes
            if (in_array($name, ['name', 'title', 'email']) || $type === 'status') {
                $filterCode = $this->generateSingleFilterCode($column, $type);
                if ($filterCode) {
                    $filters[] = $filterCode;
                }
            }
        }

        return implode(",\n            ", $filters);
    }

    /**
     * Gerar código de um único filtro
     */
    protected function generateSingleFilterCode(array $column, string $type): string
    {
        $name = $column['name'];
        $label = Str::title(str_replace('_', ' ', $name));
        
        switch ($type) {
            case 'status':
                return "SelectFilter::make('{$name}')
                ->label('{$label}')
                ->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'pending' => 'Pendente',
                    'cancelled' => 'Cancelado',
                ])";
                
            case 'boolean':
                return "BooleanFilter::make('{$name}')
                ->label('{$label}')";
                
            default:
                return "TextFilter::make('{$name}')
                ->label('{$label}')
                ->placeholder('Buscar por {$label}...')";
        }
    }

    /**
     * Gerar código das ações
     */
    protected function generateActionsCode(): string
    {
        $singular = $this->tableInfo['singular'];
        
        return "HeaderAction::create()
                ->label('Novo " . Str::title($singular) . "')
                ->icon('plus')
                ->route('{$this->tableInfo['name']}.create'),
            HeaderAction::export()
                ->label('Exportar')
                ->icon('download')
                ->route('{$this->tableInfo['name']}.export')";
    }

    /**
     * Gerar código das ações em lote
     */
    protected function generateBulkActionsCode(): string
    {
        return "BulkAction::make('delete')
                ->label('Excluir Selecionados')
                ->icon('trash-2')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (Collection \$records) {
                    \$records->each->delete();
                })";
    }

    /**
     * Gerar código dos relacionamentos
     */
    protected function generateRelationshipsCode(): string
    {
        if (empty($this->relationships)) {
            return '';
        }

        $relationships = [];
        foreach ($this->relationships as $rel) {
            $relationships[] = "'{$rel['method_name']}'";
        }

        return implode(', ', $relationships);
    }

    /**
     * Gerar código dos imports
     */
    protected function generateImportsCode(): string
    {
        $imports = [
            'use Callcocam\ReactPapaLeguas\Support\Table\Table;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Columns\ActionsColumn;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Actions\HeaderAction;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;',
            'use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;',
            'use Illuminate\Support\Collection;',
        ];

        return implode("\n", $imports);
    }

    /**
     * Gerar página frontend React
     */
    protected function generateFrontendPage(string $tableName): void
    {
        $this->info("🎨 Gerando página React...");
        
        $pageName = Str::studly($tableName);
        $outputDir = base_path("packages/callcocam/react-papa-leguas/resources/js/pages/" . Str::lower($tableName));
        
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $filePath = $outputDir . '/index.tsx';
        
        if (File::exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("Página React '{$pageName}' já existe. Deseja sobrescrever?")) {
                return;
            }
        }

        $stub = File::get(__DIR__ . '/stubs/react-page.stub');
        
        $replacements = [
            '{{pageName}}' => $pageName,
            '{{tableName}}' => $tableName,
            '{{tableTitle}}' => Str::title(str_replace('_', ' ', $tableName)),
            '{{singularName}}' => Str::title(Str::singular($tableName)),
            '{{camelName}}' => Str::camel($tableName),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        File::put($filePath, $content);
        
        $this->info("🎨 Página React criada: {$filePath}");
    }

    /**
     * Verificar se tem timestamps
     */
    protected function hasTimestamps(): bool
    {
        return collect($this->columns)->contains('name', 'created_at') && 
               collect($this->columns)->contains('name', 'updated_at');
    }

    /**
     * Verificar se tem soft deletes
     */
    protected function hasSoftDeletes(): bool
    {
        return collect($this->columns)->contains('name', 'deleted_at');
    }

    /**
     * Obter chave primária
     */
    protected function getPrimaryKey(): string
    {
        $primaryColumn = collect($this->columns)->firstWhere('primary', true);
        return $primaryColumn['name'] ?? 'id';
    }

    /**
     * Obter nome do método de relacionamento
     */
    protected function getRelationshipMethodName(string $columnName): string
    {
        return Str::camel(str_replace('_id', '', $columnName));
    }

    /**
     * Encontrar relacionamento por coluna
     */
    protected function findRelationshipByColumn(string $columnName): ?array
    {
        return collect($this->relationships)->firstWhere('column', $columnName);
    }

    /**
     * Gerar controller
     */
    protected function generateController(string $tableName): void
    {
        $this->info("🎛️ Gerando controller...");
        
        $controllerName = $this->option('controller-name') ?: Str::studly($tableName) . 'Controller';
        $controllerNamespace = $this->option('controller-namespace') ?: 'App\\Http\\Controllers';
        $modelName = $this->option('model') ?: Str::studly(Str::singular($tableName));
        $className = $this->option('name') ?: Str::studly($tableName) . 'Table';
        
        $outputDir = app_path('Http/Controllers');
        
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $filePath = $outputDir . '/' . $controllerName . '.php';
        
        if (File::exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("Controller '{$controllerName}' já existe. Deseja sobrescrever?")) {
                return;
            }
        }

        $stub = File::get(__DIR__ . '/stubs/controller.stub');
        
        $tableNamespace = $this->option('namespace') ?: 'App\\Tables';
        $modelNamespace = 'App\\Models';
        
        $replacements = [
            '{{namespace}}' => $controllerNamespace,
            '{{controllerName}}' => $controllerName,
            '{{modelName}}' => $modelName,
            '{{modelNamespace}}' => $modelNamespace,
            '{{className}}' => $className,
            '{{tableNamespace}}' => $tableNamespace,
            '{{tableName}}' => $tableName,
            '{{tableTitle}}' => Str::title(str_replace('_', ' ', $tableName)),
            '{{singularTitle}}' => Str::title(Str::singular($tableName)),
            '{{camelSingular}}' => Str::camel(Str::singular($tableName)),
            '{{routeName}}' => Str::kebab($tableName),
            '{{reactPagePath}}' => Str::studly($tableName) . '/Index',
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        File::put($filePath, $content);
        
        $this->info("🎛️ Controller criado: {$filePath}");
    }

    /**
     * Mostrar resumo da geração
     */
    protected function showSummary(string $tableName): void
    {
        $this->info("✅ Tabela Papa Leguas gerada com sucesso!");
        
        $this->line("");
        $this->line("📋 <fg=cyan>Resumo da Geração:</fg=cyan>");
        $this->line("   • Tabela analisada: <fg=yellow>{$tableName}</fg=yellow>");
        $this->line("   • Colunas detectadas: <fg=yellow>{$this->tableInfo['columns_count']}</fg=yellow>");
        $this->line("   • Relacionamentos: <fg=yellow>" . count($this->relationships) . "</fg=yellow>");
        
        $this->line("");
        $this->line("📂 <fg=cyan>Arquivos Gerados:</fg=cyan>");
        
        // Classe da tabela
        $className = $this->option('name') ?: Str::studly($tableName) . 'Table';
        $outputDir = $this->option('output') ?: app_path('Tables');
        $this->line("   • Classe da Tabela: <fg=green>{$outputDir}/{$className}.php</fg=green>");
        
        // Controller
        if ($this->option('with-controller')) {
            $controllerName = $this->option('controller-name') ?: Str::studly($tableName) . 'Controller';
            $this->line("   • Controller: <fg=green>app/Http/Controllers/{$controllerName}.php</fg=green>");
        }
        
        // Página React
        if ($this->option('with-frontend')) {
            $pagePath = "packages/callcocam/react-papa-leguas/resources/js/pages/" . Str::lower($tableName) . "/index.tsx";
            $this->line("   • Página React: <fg=green>{$pagePath}</fg=green>");
        }
        
        $this->line("");
        $this->line("🚀 <fg=cyan>Próximos Passos:</fg=cyan>");
        $this->line("   1. Revise e customize os arquivos gerados conforme necessário");
        $this->line("   2. Adicione as rotas no arquivo de rotas apropriado");
        $this->line("   3. Configure as validações no controller");
        $this->line("   4. Teste a funcionalidade gerada");
        
        if ($this->option('with-controller')) {
            $routeName = Str::kebab($tableName);
            $controllerName = $this->option('controller-name') ?: Str::studly($tableName) . 'Controller';
            
            $this->line("");
            $this->line("📝 <fg=cyan>Exemplo de Rotas (web.php):</fg=cyan>");
            $this->line("   <fg=yellow>Route::resource('{$routeName}', {$controllerName}::class);</fg=yellow>");
            $this->line("   <fg=yellow>Route::post('{$routeName}/export', [{$controllerName}::class, 'export'])->name('{$routeName}.export');</fg=yellow>");
            $this->line("   <fg=yellow>Route::post('{$routeName}/bulk-delete', [{$controllerName}::class, 'bulkDelete'])->name('{$routeName}.bulk-delete');</fg=yellow>");
        }
        
        $this->line("");
        $this->line("💡 <fg=cyan>Dicas:</fg=cyan>");
        $this->line("   • Use <fg=yellow>--force</fg=yellow> para sobrescrever arquivos existentes");
        $this->line("   • Use <fg=yellow>--with-frontend</fg=yellow> para gerar também a página React");
        $this->line("   • Use <fg=yellow>--with-controller</fg=yellow> para gerar também o controller");
        $this->line("   • Customize os namespaces com <fg=yellow>--namespace</fg=yellow> e <fg=yellow>--controller-namespace</fg=yellow>");
    }
}