<?php

namespace Callcocam\ReactPapaLeguas\Commands;

use Callcocam\ReactPapaLeguas\Models\Admin; 
use Callcocam\ReactPapaLeguas\Models\Role;
use Callcocam\ReactPapaLeguas\Enums\RoleStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'papa-leguas:create-admin 
                            {--email=admin@react-papa-leguas.test : Admin email} 
                            {--password=password : Admin password}
                            {--name=Administrador Papa Leguas : Admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default admin administrator for Papa Leguas system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🦘 Papa Leguas - Criando Administrador e Roles Padrão');
        $this->newLine();

        try {
            $email = $this->option('email');
            $password = $this->option('password');
            $name = $this->option('name');

            // Check if admin already exists
            $existingAdmin = Admin::where('email', $email)->first();
            $recreateAdmin = false;
            
            if ($existingAdmin) {
                $this->newLine();
                $this->warn('⚠️  Administrador já existe no sistema:');
                $this->line("   • Nome: {$existingAdmin->name}");
                $this->line("   • Email: {$existingAdmin->email}");
                $this->line("   • Criado em: {$existingAdmin->created_at->format('d/m/Y H:i')}");
                $status = is_bool($existingAdmin->status) ? ($existingAdmin->status ? 'Ativo' : 'Inativo') : $existingAdmin->status;
                $this->line("   • Status: {$status}");
                $this->newLine();
                
                $this->comment('📋 Opções disponíveis:');
                $this->comment('   • SIM: Deletar e recriar com novos dados');
                $this->comment('   • NÃO: Usar administrador existente (recomendado)');
                $this->newLine();
                
                $recreateAdmin = $this->confirm(
                    '🔄 Deseja recriar o administrador existente?',
                    false
                );
                
                if (!$recreateAdmin) {
                    $this->info('ℹ️  Usando administrador existente.');
                    $admin = $existingAdmin;
                }
            }

            // Iniciar transação
            DB::beginTransaction();

            // Create or recreate the admin
            if (!$existingAdmin || $recreateAdmin) {
                if ($recreateAdmin) {
                    // Deletar admin existente
                    $adminName = $existingAdmin->name;
                    $existingAdmin->delete();
                    $this->line("   🗑️  Administrador '{$adminName}' deletado");
                }
                
                // Criar novo admin
                $admin = Admin::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'status' => true, // Admin usa boolean para status
                    'company_name' => 'Papa Leguas Admin',
                    'phone' => '(11) 99999-9999',
                ]);

                $action = $recreateAdmin ? 'recriado' : 'criado';
                $this->info("✅ Administrador padrão {$action} com sucesso!");
            }

            // Criar roles padrão
            $roles = $this->createDefaultRoles();
            $this->info('✅ Roles padrão processadas com sucesso!');

            // Associar a role super-admin ao administrador (se ainda não estiver associada)
            $superAdminRole = $roles['super-admin'];
            if (!$admin->roles()->where('role_id', $superAdminRole->id)->exists()) {
                $admin->roles()->attach($superAdminRole->id);
                $this->info('✅ Role Super Admin associada ao administrador!');
            } else {
                $this->info('ℹ️  Role Super Admin já estava associada ao administrador.');
            }

            // Confirmar transação
            DB::commit();

            $this->newLine();
            
            $adminStatus = 'EXISTENTE';
            if (!$existingAdmin) {
                $adminStatus = 'NOVO';
            } elseif ($recreateAdmin) {
                $adminStatus = 'RECRIADO';
            }
            
            $this->comment('👤 Dados do administrador:');
            $this->comment("   Nome: {$admin->name}");
            $this->comment("   Email: {$admin->email}");
            if (!$existingAdmin || $recreateAdmin) {
                $this->comment("   Senha: {$password}");
            } else {
                $this->comment("   Senha: [Mantida - use a senha existente]");
            }
            $adminFinalStatus = is_bool($admin->status) ? ($admin->status ? 'Ativo' : 'Inativo') : $admin->status;
            $this->comment("   Status: {$adminFinalStatus}");
            $this->comment("   Situação: {$adminStatus}");
            $this->comment("   Role: Super Admin");
            $this->newLine();

            $this->comment('🔐 Roles padrão disponíveis:');
            foreach ($roles as $key => $role) {
                $status = $role->wasRecentlyCreated ? 'NOVA' : 'EXISTENTE';
                $this->comment("   {$role->name} (slug: {$role->slug}) - {$status}");
            }
            $this->newLine();
            
            $this->comment('🚀 Próximos passos:');
            $this->comment('1. Acessar /admin/login');
            $this->comment('2. Fazer login com as credenciais acima');
            $this->comment('3. Cadastrar o primeiro tenant');
            $this->comment('4. Gerenciar permissões das roles criadas');
            $this->newLine();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            
            $this->error('❌ Erro ao criar administrador e roles: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * Criar as roles padrão do sistema
     */
    protected function createDefaultRoles(): array
    {
        $defaultRoles = [
            'super-admin' => [
                'name' => 'Super Administrador',
                'slug' => 'super-admin',
                'description' => 'Acesso total ao sistema, incluindo gerenciamento de admins e configurações globais',
                'special' => true,
            ],
            'admin' => [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Administrador de tenant com acesso completo às funcionalidades do tenant',
                'special' => true,
            ],
            'user' => [
                'name' => 'Usuário',
                'slug' => 'user',
                'description' => 'Usuário padrão com acesso básico às funcionalidades',
                'special' => false,
            ]
        ];

        // Verificar quais roles já existem
        $existingRoles = [];
        $newRoles = [];
        
        foreach ($defaultRoles as $key => $roleData) {
            $existingRole = Role::where('slug', $roleData['slug'])
                               ->whereNull('tenant_id') // Role global (não associada a tenant)
                               ->first();
                               
            if ($existingRole) {
                $existingRoles[$key] = ['role' => $existingRole, 'data' => $roleData];
            } else {
                $newRoles[$key] = $roleData;
            }
        }

        // Se existem roles, perguntar ao usuário o que fazer
        $recreateExisting = false;
        if (!empty($existingRoles)) {
            $this->newLine();
            $this->warn('⚠️  Algumas roles padrão já existem no sistema:');
            foreach ($existingRoles as $key => $item) {
                $role = $item['role'];
                $this->line("   • {$role->name} (slug: {$role->slug}) - Criada em: {$role->created_at->format('d/m/Y H:i')}");
            }
            $this->newLine();
            
            $this->comment('📋 Opções disponíveis:');
            $this->comment('   • SIM: Deletar e recriar com configurações padrão atualizadas');
            $this->comment('   • NÃO: Manter roles existentes (recomendado se foram personalizadas)');
            $this->newLine();
            
            $recreateExisting = $this->confirm(
                '🔄 Deseja recriar as roles existentes?',
                false
            );
        }

        $createdRoles = [];

        // Processar roles existentes
        foreach ($existingRoles as $key => $item) {
            $existingRole = $item['role'];
            $roleData = $item['data'];
            
            if ($recreateExisting) {
                // Deletar role existente
                $roleName = $existingRole->name;
                $existingRole->delete();
                $this->line("   🗑️  Role '{$roleName}' deletada");
                
                // Criar nova role
                $role = Role::create([
                    'name' => $roleData['name'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'status' => RoleStatus::Published,
                    'special' => $roleData['special'],
                    'tenant_id' => null, // Role global
                    'user_id' => null,
                ]);
                
                $createdRoles[$key] = $role;
                $this->line("   ✅ Role '{$role->name}' recriada");
            } else {
                // Usar role existente
                $createdRoles[$key] = $existingRole;
                $this->line("   ℹ️  Role '{$existingRole->name}' mantida (existente)");
            }
        }

        // Processar roles novas
        foreach ($newRoles as $key => $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'slug' => $roleData['slug'],
                'description' => $roleData['description'],
                'status' => RoleStatus::Published,
                'special' => $roleData['special'],
                'tenant_id' => null, // Role global
                'user_id' => null,
            ]);

            $createdRoles[$key] = $role;
            $this->line("   ✅ Role '{$role->name}' criada");
        }

        return $createdRoles;
    }
} 