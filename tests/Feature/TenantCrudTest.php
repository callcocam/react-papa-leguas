<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Models\Tenant;
use Callcocam\ReactPapaLeguas\Models\Landlord;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Tenant CRUD Operations', function () {
    
    beforeEach(function () {
        // Create a landlord for authentication
        $this->landlord = Landlord::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->actingAs($this->landlord, 'landlord');
    });

    describe('Tenant Index', function () {
        it('can view tenants index page', function () {
            $response = $this->get(route('landlord.tenants.index'));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->component('landlord/tenants/index')
                ->has('tenants')
            );
        });

        it('can search tenants', function () {
            Tenant::factory()->create(['name' => 'Test Tenant']);
            Tenant::factory()->create(['name' => 'Another Tenant']);

            $response = $this->get(route('landlord.tenants.index', ['search' => 'Test']));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->where('tenants.data.0.name', 'Test Tenant')
                ->where('tenants.total', 1)
            );
        });

        it('can filter tenants by status', function () {
            Tenant::factory()->create(['status' => 'active']);
            Tenant::factory()->create(['status' => 'inactive']);

            $response = $this->get(route('landlord.tenants.index', ['filter' => ['status' => 'active']]));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->where('tenants.total', 1)
                ->where('tenants.data.0.status', 'active')
            );
        });

        it('can sort tenants', function () {
            Tenant::factory()->create(['name' => 'Z Tenant']);
            Tenant::factory()->create(['name' => 'A Tenant']);

            $response = $this->get(route('landlord.tenants.index', ['sort' => 'name', 'direction' => 'asc']));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->where('tenants.data.0.name', 'A Tenant')
                ->where('tenants.data.1.name', 'Z Tenant')
            );
        });
    });

    describe('Tenant Creation', function () {
        it('can view create tenant page', function () {
            $response = $this->get(route('landlord.tenants.create'));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->component('landlord/tenants/create')
            );
        });

        it('can create a new tenant', function () {
            $tenantData = [
                'name' => 'New Tenant',
                'slug' => 'new-tenant',
                'domain' => 'new-tenant.example.com',
                'status' => 'active',
                'settings' => json_encode(['theme' => 'default']),
            ];

            $response = $this->post(route('landlord.tenants.store'), $tenantData);
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', 'Tenant criado com sucesso!');
            
            $this->assertDatabaseHas('tenants', [
                'name' => 'New Tenant',
                'slug' => 'new-tenant',
                'domain' => 'new-tenant.example.com',
                'status' => 'active',
            ]);
        });

        it('validates required fields when creating tenant', function () {
            $response = $this->post(route('landlord.tenants.store'), []);
            
            $response->assertSessionHasErrors(['name', 'slug', 'domain']);
        });

        it('validates unique fields when creating tenant', function () {
            $existingTenant = Tenant::factory()->create([
                'slug' => 'existing-tenant',
                'domain' => 'existing.example.com',
            ]);

            $response = $this->post(route('landlord.tenants.store'), [
                'name' => 'New Tenant',
                'slug' => 'existing-tenant',
                'domain' => 'existing.example.com',
                'status' => 'active',
            ]);
            
            $response->assertSessionHasErrors(['slug', 'domain']);
        });
    });

    describe('Tenant View', function () {
        it('can view tenant details', function () {
            $tenant = Tenant::factory()->create();

            $response = $this->get(route('landlord.tenants.show', $tenant));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->component('landlord/tenants/show')
                ->where('tenant.id', $tenant->id)
                ->where('tenant.name', $tenant->name)
            );
        });

        it('returns 404 for non-existent tenant', function () {
            $response = $this->get(route('landlord.tenants.show', 'non-existent-id'));
            
            $response->assertStatus(404);
        });
    });

    describe('Tenant Update', function () {
        it('can view edit tenant page', function () {
            $tenant = Tenant::factory()->create();

            $response = $this->get(route('landlord.tenants.edit', $tenant));
            
            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => $page
                ->component('landlord/tenants/edit')
                ->where('tenant.id', $tenant->id)
            );
        });

        it('can update tenant', function () {
            $tenant = Tenant::factory()->create();

            $updateData = [
                'name' => 'Updated Tenant Name',
                'status' => 'inactive',
                'settings' => json_encode(['theme' => 'dark']),
            ];

            $response = $this->put(route('landlord.tenants.update', $tenant), $updateData);
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', 'Tenant atualizado com sucesso!');
            
            $this->assertDatabaseHas('tenants', [
                'id' => $tenant->id,
                'name' => 'Updated Tenant Name',
                'status' => 'inactive',
            ]);
        });

        it('can update tenant status via AJAX', function () {
            $tenant = Tenant::factory()->create(['status' => 'active']);

            $response = $this->put(route('landlord.tenants.update-status', $tenant), [
                'status' => 'inactive',
            ]);
            
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            
            $this->assertDatabaseHas('tenants', [
                'id' => $tenant->id,
                'status' => 'inactive',
            ]);
        });

        it('can update tenant field via AJAX', function () {
            $tenant = Tenant::factory()->create(['name' => 'Original Name']);

            $response = $this->put(route('landlord.tenants.update-field', $tenant), [
                'field' => 'name',
                'value' => 'Updated Name',
            ]);
            
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            
            $this->assertDatabaseHas('tenants', [
                'id' => $tenant->id,
                'name' => 'Updated Name',
            ]);
        });
    });

    describe('Tenant Deletion', function () {
        it('can delete tenant', function () {
            $tenant = Tenant::factory()->create();

            $response = $this->delete(route('landlord.tenants.destroy', $tenant));
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', 'Tenant excluído com sucesso!');
            
            $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
        });

        it('can force delete tenant', function () {
            $tenant = Tenant::factory()->create();
            $tenant->delete(); // Soft delete first

            $response = $this->delete(route('landlord.tenants.force-delete', $tenant));
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', 'Tenant excluído permanentemente!');
            
            $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
        });

        it('can restore tenant', function () {
            $tenant = Tenant::factory()->create();
            $tenant->delete(); // Soft delete

            $response = $this->put(route('landlord.tenants.restore', $tenant));
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', 'Tenant restaurado com sucesso!');
            
            $this->assertDatabaseHas('tenants', [
                'id' => $tenant->id,
                'deleted_at' => null,
            ]);
        });
    });

    describe('Bulk Operations', function () {
        it('can bulk delete tenants', function () {
            $tenants = Tenant::factory()->count(3)->create();
            $tenantIds = $tenants->pluck('id')->toArray();

            $response = $this->delete(route('landlord.tenants.bulk-delete'), [
                'ids' => $tenantIds,
            ]);
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', '3 tenants excluídos com sucesso!');
            
            foreach ($tenantIds as $id) {
                $this->assertSoftDeleted('tenants', ['id' => $id]);
            }
        });

        it('can bulk activate tenants', function () {
            $tenants = Tenant::factory()->count(3)->create(['status' => 'inactive']);
            $tenantIds = $tenants->pluck('id')->toArray();

            $response = $this->put(route('landlord.tenants.bulk-activate'), [
                'ids' => $tenantIds,
            ]);
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', '3 tenants ativados com sucesso!');
            
            foreach ($tenantIds as $id) {
                $this->assertDatabaseHas('tenants', [
                    'id' => $id,
                    'status' => 'active',
                ]);
            }
        });

        it('can bulk deactivate tenants', function () {
            $tenants = Tenant::factory()->count(3)->create(['status' => 'active']);
            $tenantIds = $tenants->pluck('id')->toArray();

            $response = $this->put(route('landlord.tenants.bulk-deactivate'), [
                'ids' => $tenantIds,
            ]);
            
            $response->assertRedirect(route('landlord.tenants.index'));
            $response->assertSessionHas('success', '3 tenants desativados com sucesso!');
            
            foreach ($tenantIds as $id) {
                $this->assertDatabaseHas('tenants', [
                    'id' => $id,
                    'status' => 'inactive',
                ]);
            }
        });
    });

    describe('Export', function () {
        it('can export tenants', function () {
            Tenant::factory()->count(5)->create();

            $response = $this->get(route('landlord.tenants.export'));
            
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
            $response->assertHeader('content-disposition', 'attachment; filename=tenants.csv');
        });

        it('can export filtered tenants', function () {
            Tenant::factory()->create(['status' => 'active']);
            Tenant::factory()->create(['status' => 'inactive']);

            $response = $this->get(route('landlord.tenants.export', ['filter' => ['status' => 'active']]));
            
            $response->assertStatus(200);
            
            $content = $response->getContent();
            $this->assertStringContainsString('active', $content);
            $this->assertStringNotContainsString('inactive', $content);
        });
    });

    describe('Authorization', function () {
        it('requires authentication for all routes', function () {
            auth('landlord')->logout();

            $tenant = Tenant::factory()->create();

            $routes = [
                ['GET', route('landlord.tenants.index')],
                ['GET', route('landlord.tenants.create')],
                ['POST', route('landlord.tenants.store')],
                ['GET', route('landlord.tenants.show', $tenant)],
                ['GET', route('landlord.tenants.edit', $tenant)],
                ['PUT', route('landlord.tenants.update', $tenant)],
                ['DELETE', route('landlord.tenants.destroy', $tenant)],
            ];

            foreach ($routes as [$method, $url]) {
                $response = $this->call($method, $url);
                $response->assertRedirect(route('landlord.login'));
            }
        });
    });
});
