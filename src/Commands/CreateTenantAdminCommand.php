<?php

namespace Callcocam\ReactPapaLeguas\Commands;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Models\Tenant;
use Callcocam\ReactPapaLeguas\Models\Role;
use Callcocam\ReactPapaLeguas\Enums\TenantStatus;
use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Enums\RoleStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTenantAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'papa-leguas:create-tenant-admin 
                            {--tenant-name=Empresa Exemplo : Nome do tenant}
                            {--tenant-email=admin@empresa.com : Email do tenant}
                            {--tenant-domain= : Domínio do tenant (opcional)}
                            {--admin-name=Administrador : Nome do usuário admin}
                            {--admin-email=admin@empresa.com : Email do usuário admin}
                            {--admin-password=password : Senha do usuário admin}
                            {--tenant-description= : Descrição do tenant (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um tenant e seu usuário administrador com role admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🦘 Papa Leguas - Criando Tenant e Administrador');
        $this->newLine();

        try {
            $tenantName = $this->option('tenant-name');
            $tenantEmail = $this->option('tenant-email');
            $tenantDomain = $this->option('tenant-domain');
            $tenantDescription = $this->option('tenant-description');
            
            $adminName = $this->option('admin-name');
            $adminEmail = $this->option('admin-email');
            $adminPassword = $this->option('admin-password');

            // Verificar se o tenant já existe
            if (Tenant::where('email', $tenantEmail)->exists()) {
                $this->warn("ℹ️  Tenant com email {$tenantEmail} já existe.");
                $this->newLine();
                return Command::SUCCESS;
            }

            // Verificar se o usuário admin já existe
            if (User::where('email', $adminEmail)->exists()) {
                $this->warn("ℹ️  Usuário com email {$adminEmail} já existe.");
                $this->newLine();
                return Command::SUCCESS;
            }

            // Iniciar transação
            DB::beginTransaction();

            // Criar o tenant
            $tenant = $this->createTenant($tenantName, $tenantEmail, $tenantDomain, $tenantDescription);
            $this->info("✅ Tenant '{$tenant->name}' criado com sucesso!");

            // Criar o usuário admin
            $adminUser = $this->createAdminUser($tenant, $adminName, $adminEmail, $adminPassword);
            $this->info("✅ Usuário admin '{$adminUser->name}' criado com sucesso!");

            // Criar e associar a role admin
            $adminRole = $this->createAdminRole($tenant, $adminUser);
            $this->info("✅ Role admin criada e associada com sucesso!");

            // Confirmar transação
            DB::commit();

            $this->newLine();
            $this->comment('🎉 Tenant e administrador criados com sucesso!');
            $this->newLine();
            
            $this->comment('📋 Dados do tenant:');
            $this->comment("   Nome: {$tenant->name}");
            $this->comment("   Email: {$tenant->email}");
            $this->comment("   Slug: {$tenant->slug}");
            $this->comment("   Status: {$tenant->status->value}");
            if ($tenant->domain) {
                $this->comment("   Domínio: {$tenant->domain}");
            }
            $this->newLine();
            
            $this->comment('👤 Dados do administrador:');
            $this->comment("   Nome: {$adminUser->name}");
            $this->comment("   Email: {$adminUser->email}");
            $this->comment("   Senha: {$adminPassword}");
            $this->comment("   Status: {$adminUser->status->value}");
            $this->newLine();
            
            $this->comment('🔐 Dados da role:');
            $this->comment("   Nome: {$adminRole->name}");
            $this->comment("   Slug: {$adminRole->slug}");
            $this->comment("   Status: {$adminRole->status->value}");
            $this->newLine();
            
            $this->comment('🚀 Próximos passos:');
            $this->comment('1. Fazer login com as credenciais do administrador');
            $this->comment('2. Configurar permissões específicas se necessário');
            $this->comment('3. Adicionar mais usuários ao tenant');
            $this->newLine();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            
            $this->error('❌ Erro ao criar tenant e administrador: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * Criar o tenant
     */
    protected function createTenant(string $name, string $email, ?string $domain, ?string $description): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => $email,
            'domain' => $domain,
            'description' => $description,
            'status' => TenantStatus::Published,
            'is_primary' => false,
            'settings' => [],
        ]);
    }

    /**
     * Criar o usuário administrador
     */
    protected function createAdminUser(Tenant $tenant, string $name, string $email, string $password): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'status' => BaseStatus::Published,
        ]);
    }

    /**
     * Criar a role admin para o tenant
     */
    protected function createAdminRole(Tenant $tenant, User $user): Role
    {
        // Criar a role admin
        $role = Role::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Administrador do tenant com acesso total',
            'status' => RoleStatus::Published,
            'special' => true,
        ]);

        // Associar a role ao usuário
        $user->roles()->attach($role->id);

        return $role;
    }
} 