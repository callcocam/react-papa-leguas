<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
// config for Callcocam/ReactPapaLeguas
return [
    /*
    |--------------------------------------------------------------------------
    | Landlord Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the authentication settings for landlords.
    | This includes the model class, session timeout, and other options.
    |
    */
    'landlord' => [
        /*
        |--------------------------------------------------------------------------
        | Landlord Model
        |--------------------------------------------------------------------------
        |
        | The model class that represents a landlord user.
        |
        */
        'model' => \Callcocam\ReactPapaLeguas\Models\Landlord::class,

        /*
        |--------------------------------------------------------------------------
        | Authentication Table
        |--------------------------------------------------------------------------
        |
        | The database table used to store landlord authentication data.
        |
        */
        'table' => 'admins',

        /*
        |--------------------------------------------------------------------------
        | Session Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for landlord authentication sessions.
        |
        */
        'session' => [
            'lifetime' => 120, // minutes
            'expire_on_close' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Remember Me
        |--------------------------------------------------------------------------
        |
        | Configuration for "remember me" functionality.
        |
        */
        'remember' => [
            'enabled' => true,
            'duration' => 2592000, // 30 days in seconds
        ],

        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        |
        | Configuration for password reset functionality.
        |
        */
        'passwords' => [
            'expire' => 60, // minutes
            'throttle' => 60, // seconds
        ],

        /*
        |--------------------------------------------------------------------------
        | Login Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for login behavior.
        |
        */
        'login' => [
            'username_field' => 'email',
            'password_field' => 'password',
            'remember_field' => 'remember',
            'max_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes in seconds
        ],

        /*
        |--------------------------------------------------------------------------
        | Routes Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for landlord authentication routes.
        |
        */
        'routes' => [
            'prefix' => 'landlord',
            'middleware' => ['web'],
            'login' => '/login',
            'logout' => '/logout',
            'dashboard' => '/dashboard',
            'register' => '/register',
            'forgot_password' => '/forgot-password',
            'reset_password' => '/reset-password',
        ],

        /*
        |--------------------------------------------------------------------------
        | Status Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for landlord status management.
        |
        */
        'status' => [
            'require_active' => true,
            'require_email_verification' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the models used by the React Papa Leguas package.
    |
    */
    'models' => [
        'role' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Role::class,
        'permission' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Permission::class,
    ],

];
