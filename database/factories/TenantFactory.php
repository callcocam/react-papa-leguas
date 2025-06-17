<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Database\Factories;

use Callcocam\ReactPapaLeguas\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company;
        $slug = Str::slug($name);
        
        return [
            'id' => Str::ulid(),
            'name' => $name,
            'slug' => $slug,
            'domain' => $slug . '.example.com',
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'settings' => json_encode([
                'theme' => $this->faker->randomElement(['default', 'dark', 'light']),
                'locale' => $this->faker->randomElement(['pt_BR', 'en_US', 'es_ES']),
                'timezone' => $this->faker->timezone,
            ]),
            'active' => $this->faker->boolean(80), // 80% chance of being active
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'active' => true,
        ]);
    }

    /**
     * Indicate that the tenant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'active' => false,
        ]);
    }

    /**
     * Indicate that the tenant is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'active' => false,
        ]);
    }

    /**
     * Indicate that the tenant has a subdomain.
     */
    public function subdomain(): static
    {
        return $this->state(function (array $attributes) {
            $slug = $attributes['slug'] ?? Str::slug($attributes['name']);
            return [
                'domain' => $slug . '.example.com',
                'domain_type' => 'subdomain',
            ];
        });
    }

    /**
     * Indicate that the tenant has a custom domain.
     */
    public function customDomain(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'domain' => $this->faker->domainName,
                'domain_type' => 'domain',
            ];
        });
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Tenant $tenant) {
            // Ensure slug is unique
            $baseSlug = $tenant->slug;
            $counter = 1;
            
            while (Tenant::where('slug', $tenant->slug)->exists()) {
                $tenant->slug = $baseSlug . '-' . $counter;
                $counter++;
            }
        });
    }
}
