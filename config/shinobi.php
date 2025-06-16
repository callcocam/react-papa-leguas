<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Shinobi Models
    |--------------------------------------------------------------------------
    |
    | These are the models used by Shinobi to define the roles and permissions.
    | If you want to use your own models you may change them here.
    |
    */
    'models' => [
        'role' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Role::class,
        'permission' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Permission::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shinobi Tables
    |--------------------------------------------------------------------------
    |
    | These are the tables used by Shinobi to store roles and permissions.
    | You may change them if needed.
    |
    */
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_user' => 'permission_user',
        'permission_role' => 'permission_role',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shinobi Cache
    |--------------------------------------------------------------------------
    |
    | Configuration for permission caching.
    |
    */
    'cache' => [
        'enabled' => true,
        'key' => 'shinobi.permissions',
        'duration' => 60 * 24, // 24 hours in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Special Roles
    |--------------------------------------------------------------------------
    |
    | These roles have special behavior and bypass permission checks.
    |
    */
    'special_roles' => [
        'super-admin' => 'all-access',
        'admin' => 'admin-access',
    ],

];
