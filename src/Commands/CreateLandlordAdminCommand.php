<?php

namespace Callcocam\ReactPapaLeguas\Commands;

use Callcocam\ReactPapaLeguas\Models\Landlord;
use Callcocam\ReactPapaLeguas\Enums\UserStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateLandlordAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'papa-leguas:create-admin 
                            {--email=admin@papaleguas.com : Admin email} 
                            {--password=password : Admin password}
                            {--name=Administrador Papa Leguas : Admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default landlord administrator for Papa Leguas system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🦘 Papa Leguas - Criando Administrador');
        $this->newLine();

        try {
            $email = $this->option('email');
            $password = $this->option('password');
            $name = $this->option('name');

            // Check if admin already exists
            if (Landlord::where('email', $email)->exists()) {
                $this->warn("ℹ️  Administrador com email {$email} já existe.");
                $this->newLine();
                return Command::SUCCESS;
            }

            // Create the admin
            $admin = Landlord::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => UserStatus::Published,
                'company_name' => 'Papa Leguas Admin',
                'phone' => '(11) 99999-9999',
            ]);

            $this->info('✅ Administrador padrão criado com sucesso!');
            $this->newLine();
            
            $this->comment('Dados do administrador:');
            $this->comment("   Nome: {$admin->name}");
            $this->comment("   Email: {$admin->email}");
            $this->comment("   Senha: {$password}");
            $this->comment("   Status: Ativo");
            $this->newLine();
            
            $this->comment('Próximos passos:');
            $this->comment('1. Acessar /landlord/login');
            $this->comment('2. Fazer login com as credenciais acima');
            $this->comment('3. Cadastrar o primeiro tenant');
            $this->newLine();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao criar administrador: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
