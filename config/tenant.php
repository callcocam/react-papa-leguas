<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Models
    |--------------------------------------------------------------------------
    |
    | These are the models used by the Landlord system to manage tenants.
    |
    */
    'models' => [
        'tenant' => \Callcocam\ReactPapaLeguas\Models\Tenant::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for tenant management.
    |
    */
    'tenant' => [
        'column' => 'tenant_id',
        'auto_scope' => true,
        'strict_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tenant Columns
    |--------------------------------------------------------------------------
    |
    | Default columns used for tenant scoping in models.
    |
    */
    'default_tenant_columns' => ['tenant_id'],

    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for domain-based tenant resolution.
    |
    */
    'domain' => [
        'enabled' => true,
        'subdomain_enabled' => true,
        'cache_ttl' => 3600, // 1 hour in seconds
    ],

];
