<?php

namespace Callcocam\ReactPapaLeguas\Database\Seeders;

use Callcocam\ReactPapaLeguas\Models\Landlord;
use Callcocam\ReactPapaLeguas\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LandlordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default landlord admin if it doesn't exist
        if (!Landlord::where('email', 'admin@papaleguas.com')->exists()) {
            Landlord::create([
                'name' => 'Administrador Papa Leguas',
                'email' => 'admin@papaleguas.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => UserStatus::Published,
                'company_name' => 'Papa Leguas Admin',
                'phone' => '(11) 99999-9999',
            ]);

            echo "✅ Administrador padrão criado:\n";
            echo "   Email: admin@papaleguas.com\n";
            echo "   Senha: password\n";
            echo "   Status: Ativo\n\n";
        } else {
            echo "ℹ️  Administrador padrão já existe.\n\n";
        }
    }
}
