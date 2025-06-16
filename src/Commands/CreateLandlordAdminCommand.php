<?php

namespace Callcocam\ReactPapaLeguas\Commands;

use Callcocam\ReactPapaLeguas\Database\Seeders\LandlordSeeder;
use Illuminate\Console\Command;

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
            // Run the landlord seeder
            $seeder = new LandlordSeeder();
            $seeder->run();

            $this->info('✅ Processo concluído com sucesso!');
            $this->newLine();
            
            $this->comment('Agora você pode:');
            $this->comment('1. Acessar /landlord/login');
            $this->comment('2. Fazer login com admin@papaleguas.com / password');
            $this->comment('3. Cadastrar o primeiro tenant');
            $this->newLine();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao criar administrador: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
