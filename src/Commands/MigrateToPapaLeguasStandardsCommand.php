<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrateToPapaLeguasStandardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'papa-leguas:migrate-standards 
                           {--backup : Create backup of existing files}
                           {--force : Force migration without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing User model and migrations to Papa Leguas standards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🦘 Migração para Padrões Papa Leguas');
        $this->info('=====================================');

        if (!$this->option('force') && !$this->confirm('Isso irá modificar seu modelo User e criar novas migrations. Deseja continuar?')) {
            $this->info('Migração cancelada.');
            return;
        }

        // Create backup if requested
        if ($this->option('backup')) {
            $this->createBackups();
        }

        // Migrate User model
        $this->migrateUserModel(); 
 
        // Show summary
        $this->showSummary();
    }

    /**
     * Create backups of existing files.
     */
    protected function createBackups()
    {
        $this->info('📁 Criando backups...');

        $backupDir = base_path('backups/papa-leguas-migration-' . date('Y-m-d-H-i-s'));
        File::makeDirectory($backupDir, 0755, true);

        // Backup User model
        $userModelPath = app_path('Models/User.php');
        if (File::exists($userModelPath)) {
            File::copy($userModelPath, $backupDir . '/User.php.backup');
            $this->line("✅ User model salvo em backup: {$backupDir}/User.php.backup");
        }

        // Backup existing migrations
        $migrationsPath = database_path('migrations');
        $userMigrations = File::glob($migrationsPath . '/*_create_users_table.php');
        
        foreach ($userMigrations as $migration) {
            $filename = basename($migration);
            File::copy($migration, $backupDir . '/' . $filename . '.backup');
            $this->line("✅ Migration salva em backup: {$filename}");
        }

        $this->info("📁 Backups criados em: {$backupDir}");
    }

    /**
     * Migrate the User model to Papa Leguas standards.
     */
    protected function migrateUserModel()
    {
        $this->info('🔄 Migrando modelo User...');

        $userModelPath = app_path('Models/User.php');
        
        if (!File::exists($userModelPath)) {
            $this->error('Modelo User não encontrado em app/Models/User.php');
            return;
        }

        $currentContent = File::get($userModelPath);
        
        // Check if already using Papa Leguas standards
        if (Str::contains($currentContent, 'AbstractModel')) {
            $this->warn('O modelo User já parece usar os padrões Papa Leguas.');
            return;
        }

        $newContent = $this->generateUpdatedUserModel($currentContent);
        
        File::put($userModelPath, $newContent);
        $this->line('✅ Modelo User atualizado com padrões Papa Leguas');
    }    /**
     * Generate the updated User model content.
     */
    protected function generateUpdatedUserModel(string $currentContent): string
    {
         return File::get(__DIR__ . '/stubs/UserModel.stub');
    }

    /**
     * Show migration summary.
     */
    protected function showSummary()
    {
        $this->info('');
        $this->info('🎉 Migração concluída com sucesso!');
        $this->info('=====================================');
        
        $this->line('✅ Modelo User atualizado com padrões Papa Leguas');
        $this->line('✅ As migrations do pacote já incluem a estrutura atualizada da tabela users');
        
        $this->info('');
        $this->warn('⚠️  Próximos Passos:');
        $this->line('1. As migrations necessárias já foram publicadas pelo pacote');
        $this->line('2. Execute: php artisan migrate');
        $this->line('3. Para projetos existentes: considere fazer backup dos dados antes da migração');
        $this->line('4. Gere slugs para usuários existentes: User::whereNull("slug")->each(fn($u) => $u->save())');
        $this->line('5. Revise e teste o modelo User atualizado');
        
        if ($this->option('backup')) {
            $this->info('');
            $this->info('📁 Backups foram criados caso precise fazer rollback');
        }
        
        $this->info('');
        $this->info('📚 Documentação: Veja DEVELOPMENT_STANDARDS.md para detalhes completos');
        $this->info('🗂️  Migrations disponíveis: database/migrations/ (publicadas pelo pacote)');
    }
}
