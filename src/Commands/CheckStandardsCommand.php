<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckStandardsCommand extends Command
{
    protected $signature = 'papa-leguas:check-standards {--show-details : Show detailed analysis}';
    
    protected $description = 'Check if your project follows Papa Leguas standards';

    public function handle(): int
    {
        $this->info('🔍 Verificando padrões Papa Leguas...');
        $this->newLine();

        $checks = $this->performChecks();
        $passed = count(array_filter($checks, fn($check) => $check['status']));
        $total = count($checks);

        // Show summary
        $this->info("📊 Resultado: {$passed}/{$total} verificações passaram");
        $this->newLine();

        // Show detailed results
        foreach ($checks as $check) {
            $icon = $check['status'] ? '✅' : '❌';
            $this->line("{$icon} {$check['name']}");
            
            if ($this->option('show-details') && !empty($check['details'])) {
                $this->line("   {$check['details']}");
            }
        }

        $this->newLine();

        if ($passed < $total) {
            $this->warn('⚠️  Seu projeto não está seguindo todos os padrões Papa Leguas.');
            $this->info('💡 Execute: php artisan papa-leguas:migrate-standards --backup');
            $this->newLine();
            
            return self::FAILURE;
        } else {
            $this->info('🎉 Parabéns! Seu projeto segue todos os padrões Papa Leguas.');
            $this->newLine();
            
            return self::SUCCESS;
        }
    }

    protected function performChecks(): array
    {
        $checks = [];

        // Check User model
        $userModelPath = app_path('Models/User.php');
        $checks[] = $this->checkUserModel($userModelPath);

        // Check migrations structure
        $checks[] = $this->checkMigrationsStructure();

        // Check config files
        $checks[] = $this->checkConfigFiles();

        // Check if Shinobi is properly configured
        $checks[] = $this->checkShinobiConfiguration();

        return $checks;
    }

    protected function checkUserModel(string $path): array
    {
        if (!File::exists($path)) {
            return [
                'name' => 'User Model Exists',
                'status' => false,
                'details' => 'User model not found at ' . $path
            ];
        }

        $content = File::get($path);
        
        $hasUlid = str_contains($content, 'HasUlids') || str_contains($content, 'ulid');
        $hasSlug = str_contains($content, 'HasSlug') || str_contains($content, 'slug');
        $hasStatus = str_contains($content, 'status') || str_contains($content, 'BaseStatus');
        $hasTenantId = str_contains($content, 'tenant_id');
        $hasSoftDeletes = str_contains($content, 'SoftDeletes');
        $hasPermissions = str_contains($content, 'HasPermissions') || str_contains($content, 'HasRoles');

        $missing = [];
        if (!$hasUlid) $missing[] = 'ULID';
        if (!$hasSlug) $missing[] = 'Slug';
        if (!$hasStatus) $missing[] = 'Status Enum';
        if (!$hasTenantId) $missing[] = 'Tenant ID';
        if (!$hasSoftDeletes) $missing[] = 'Soft Deletes';
        if (!$hasPermissions) $missing[] = 'Permissions/Roles';

        return [
            'name' => 'User Model Standards',
            'status' => empty($missing),
            'details' => empty($missing) ? 'Todos os padrões implementados' : 'Faltando: ' . implode(', ', $missing)
        ];
    }

    protected function checkMigrationsStructure(): array
    {
        $migrationPath = database_path('migrations');
        $migrations = File::glob($migrationPath . '/*_create_users_table.php');

        if (empty($migrations)) {
            return [
                'name' => 'Users Migration Exists',
                'status' => false,
                'details' => 'Migration create_users_table not found'
            ];
        }

        $migrationContent = File::get($migrations[0]);
        
        $hasUlidColumn = str_contains($migrationContent, "table->ulid('id')") || 
                        str_contains($migrationContent, '$table->ulid(');
        $hasStatusEnum = str_contains($migrationContent, "enum('status'") || 
                        str_contains($migrationContent, 'status');
        $hasSlugColumn = str_contains($migrationContent, "string('slug')") || 
                        str_contains($migrationContent, 'slug');
        $hasTenantColumn = str_contains($migrationContent, 'tenant_id');
        $hasIndexes = str_contains($migrationContent, 'index(') || 
                     str_contains($migrationContent, 'unique(');

        $missing = [];
        if (!$hasUlidColumn) $missing[] = 'ULID primary key';
        if (!$hasStatusEnum) $missing[] = 'Status enum';
        if (!$hasSlugColumn) $missing[] = 'Slug column';
        if (!$hasTenantColumn) $missing[] = 'Tenant ID';
        if (!$hasIndexes) $missing[] = 'Performance indexes';

        return [
            'name' => 'Users Migration Standards',
            'status' => empty($missing),
            'details' => empty($missing) ? 'Migration segue os padrões' : 'Faltando: ' . implode(', ', $missing)
        ];
    }

    protected function checkConfigFiles(): array
    {
        $configFiles = ['react-papa-leguas', 'shinobi', 'tenant'];
        $published = 0;

        foreach ($configFiles as $config) {
            if (File::exists(config_path($config . '.php'))) {
                $published++;
            }
        }

        return [
            'name' => 'Config Files Published',
            'status' => $published === count($configFiles),
            'details' => "{$published}/" . count($configFiles) . " arquivos de configuração publicados"
        ];
    }

    protected function checkShinobiConfiguration(): array
    {
        $configPath = config_path('shinobi.php');
        
        if (!File::exists($configPath)) {
            return [
                'name' => 'Shinobi Configuration',
                'status' => false,
                'details' => 'Config file shinobi.php not published'
            ];
        }

        // Check if Shinobi migrations exist
        $migrationPath = database_path('migrations');
        $rolesMigration = !empty(File::glob($migrationPath . '/*_create_roles_table.php'));
        $permissionsMigration = !empty(File::glob($migrationPath . '/*_create_permissions_table.php'));

        $status = $rolesMigration && $permissionsMigration;

        return [
            'name' => 'Shinobi ACL System',
            'status' => $status,
            'details' => $status ? 'Sistema ACL configurado' : 'Faltam migrations do Shinobi'
        ];
    }
}
