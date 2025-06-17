<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Database\Factories;

use Callcocam\ReactPapaLeguas\Models\Landlord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LandlordFactory extends Factory
{
    protected $model = Landlord::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'settings' => json_encode([
                'theme' => $this->faker->randomElement(['default', 'dark', 'light']),
                'locale' => $this->faker->randomElement(['pt_BR', 'en_US', 'es_ES']),
                'timezone' => $this->faker->timezone,
                'notifications' => [
                    'email' => $this->faker->boolean(80),
                    'sms' => $this->faker->boolean(40),
                ],
            ]),
            'active' => $this->faker->boolean(90), // 90% chance of being active
            'remember_token' => Str::random(10),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the landlord's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the landlord is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'active' => true,
        ]);
    }

    /**
     * Indicate that the landlord is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'active' => false,
        ]);
    }

    /**
     * Indicate that the landlord is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(function (array $attributes) {
            $settings = json_decode($attributes['settings'], true);
            $settings['roles'] = ['super_admin'];
            $settings['permissions'] = ['*'];
            
            return [
                'settings' => json_encode($settings),
            ];
        });
    }

    /**
     * Indicate that the landlord has specific roles.
     */
    public function withRoles(array $roles): static
    {
        return $this->state(function (array $attributes) use ($roles) {
            $settings = json_decode($attributes['settings'], true);
            $settings['roles'] = $roles;
            
            return [
                'settings' => json_encode($settings),
            ];
        });
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Landlord $landlord) {
            // Ensure email is unique
            $baseEmail = $landlord->email;
            $counter = 1;
            
            while (Landlord::where('email', $landlord->email)->exists()) {
                $emailParts = explode('@', $baseEmail);
                $landlord->email = $emailParts[0] . $counter . '@' . $emailParts[1];
                $counter++;
            }
        });
    }
}
