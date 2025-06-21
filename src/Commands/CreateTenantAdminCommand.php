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
use Illuminate\Support\Facades\Config;
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
                            {--tenant-email= : Email do tenant (se não fornecido, será gerado automaticamente)}
                            {--tenant-domain= : Domínio do tenant (se não fornecido, será gerado automaticamente)}
                            {--admin-name=Administrador : Nome do usuário admin}
                            {--admin-email=admin : Email do usuário admin (se não fornecido, será gerado automaticamente)}
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
            $tenantEmail = $this->option('tenant-email') ?: $this->generateTenantEmail($tenantName);
            $tenantDomain = $this->option('tenant-domain') ?: $this->generateTenantDomain();
            $tenantDescription = $this->option('tenant-description');
            
            $adminName = $this->option('admin-name');
            $adminEmail = $this->option('admin-email');
            $adminEmail = sprintf('%s@%s', $adminEmail, $tenantDomain);
            $adminPassword = $this->option('admin-password');

            $this->info("📧 Email do tenant: {$tenantEmail}");
            $this->info("🌐 Domínio do tenant: {$tenantDomain}");
            $this->info("👤 Email do admin: {$adminEmail}");
            $this->newLine();

            // Iniciar transação
            DB::beginTransaction();

            // Verificar ou criar o tenant
            $tenant = $this->findOrCreateTenant($tenantName, $tenantEmail, $tenantDomain, $tenantDescription);
            
            // Verificar ou criar o usuário admin
            $adminUser = $this->findOrCreateAdminUser($tenant, $adminName, $adminEmail, $adminPassword);
            
            // Verificar ou criar e associar a role admin
            $adminRole = $this->findOrCreateAdminRole($tenant, $adminUser);

            // Confirmar transação
            DB::commit();

            $this->newLine();
            $this->comment('🎉 Processo concluído com sucesso!');
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
     * Gerar email do tenant automaticamente baseado no app.url
     */
    protected function generateTenantEmail(string $tenantName): string
    {
        $domain = $this->extractDomainFromAppUrl();
        $slug = Str::slug($tenantName);
        
        return "{$slug}@{$domain}";
    }

    /**
     * Gerar domínio do tenant automaticamente baseado no app.url
     */
    protected function generateTenantDomain(): string
    {
        return $this->extractDomainFromAppUrl();
    }

    /**
     * Extrair domínio do config app.url
     */
    protected function extractDomainFromAppUrl(): string
    {
        $appUrl = Config::get('app.url');
        
        // Remover protocolo (http://, https://)
        $domain = preg_replace('/^https?:\/\//', '', $appUrl);
        
        // Remover porta se existir
        $domain = preg_replace('/:\d+$/', '', $domain);
        
        // Remover path se existir
        $domain = explode('/', $domain)[0];
        
        return $domain;
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

    /**
     * Encontrar ou criar o tenant
     */
    protected function findOrCreateTenant(string $name, string $email, ?string $domain, ?string $description): Tenant
    {
        $existingTenant = Tenant::where('email', $email)->first();
        
        if ($existingTenant) {
            $this->warn("ℹ️  Tenant com email {$email} já existe - reutilizando existente.");
            return $existingTenant;
        }
        
        $tenant = $this->createTenant($name, $email, $domain, $description);
        $this->info("✅ Tenant '{$tenant->name}' criado com sucesso!");
        
        return $tenant;
    }

    /**
     * Encontrar ou criar o usuário administrador
     */
    protected function findOrCreateAdminUser(Tenant $tenant, string $name, string $email, string $password): User
    {
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->warn("ℹ️  Usuário com email {$email} já existe - reutilizando existente.");
            
            // Verificar se o usuário pertence ao tenant correto
            if ($existingUser->tenant_id !== $tenant->id) {
                $this->comment("🔄 Atualizando tenant_id do usuário existente.");
                $existingUser->update(['tenant_id' => $tenant->id]);
            }
            
            return $existingUser;
        }
        
        $adminUser = $this->createAdminUser($tenant, $name, $email, $password);
        $this->info("✅ Usuário admin '{$adminUser->name}' criado com sucesso!");
        
        return $adminUser;
    }

    /**
     * Encontrar ou criar a role admin para o tenant
     */
    protected function findOrCreateAdminRole(Tenant $tenant, User $user): Role
    {
        // Verificar se já existe uma role admin para este tenant
        $existingRole = Role::where('slug', 'admin')
            ->first();
        
        if ($existingRole) {
            $this->warn("ℹ️  Role admin já existe para este tenant - reutilizando existente.");
            
            // Verificar se o usuário já tem esta role associada
            if (!$user->roles()->where('role_id', $existingRole->id)->exists()) {
                $user->roles()->attach($existingRole->id);
                $this->comment("🔗 Role admin associada ao usuário.");
            } else {
                $this->comment("ℹ️  Usuário já possui a role admin associada.");
            }
            
            return $existingRole;
        }
        
        // Criar nova role admin
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
        
        $this->info("✅ Role admin criada e associada com sucesso!");

        return $role;
    }
} 