# Landlord Multi-Tenant System

Sistema de multi-tenancy que permite isolamento de dados por tenant, com bypass automático para usuários landlord (ad## Configuração

### Arquivo: `config/tenant.php`

```php
return [
    'models' => [
        'tenant' => \Callcocam\ReactPapaLeguas\Models\Tenant::class,
    ],
    
    'tenant' => [
        'column' => 'tenant_id',
        'auto_scope' => true,
        'strict_mode' => true,
    ],
    
    // Colunas padrão usadas para scoping quando não especificadas no modelo
    'default_tenant_columns' => ['tenant_id'],
    
    'domain' => [
        'enabled' => true,
        'subdomain_enabled' => true,
        'cache_ttl' => 3600,
    ],
];
```

### Arquivo: `config/react-papa-leguas.php`

```php
return [
    'landlord' => [
        'model' => \Callcocam\ReactPapaLeguas\Models\Admin::class,
        'table' => 'admins',
        'routes' => [
            'prefix' => 'landlord',
            'middleware' => ['web'],
            // ... outras configurações de rotas
        ],
        // ... outras configurações de landlord
    ],
    
    'models' => [
        'role' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Role::class,
        'permission' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Permission::class,
    ],
];
```

### Arquivo: `config/shinobi.php`

Configurações específicas do sistema de roles e permissões:

```php
return [
    'models' => [
        'role' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Role::class,
        'permission' => \Callcocam\ReactPapaLeguas\Shinobi\Models\Permission::class,
    ],
    
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_user' => 'permission_user',
        'permission_role' => 'permission_role',
    ],
    
    'cache' => [
        'enabled' => true,
        'tag' => 'shinobi.permissions',
        'length' => 60 * 24, // 24 horas em minutos
    ],
];
```

## Configurações Unificadas

O sistema agora usa uma configuração unificada onde:

- **Tenant scoping**: Use `config('tenant.*')` para configurações de tenant
- **Landlord auth**: Use `config('react-papa-leguas.landlord.*')` para autenticação landlord  
- **Models**: Use `config('react-papa-leguas.models.*')` ou `config('shinobi.models.*')` para modelos
- **Colunas padrão**: Use `config('tenant.default_tenant_columns')` para colunas de tenant

## Compatibilidade

## Características

- **Auto-Scoping**: Filtragem automática por tenant em todos os modelos
- **Landlord Bypass**: Usuários do guard `landlord` têm acesso global
- **Domain/Subdomain Resolution**: Resolução automática de tenant por domínio
- **Flexible Control**: Métodos para controlar scoping dinamicamente

## Uso Básico

### 1. Adicionar trait aos modelos

```php
use Callcocam\ReactPapaLeguas\Landlord\BelongsToTenants;

class Post extends Model
{
    use BelongsToTenants;
    
    // O modelo será automaticamente filtrado por tenant_id
}
```

### 2. Configurar colunas de tenant personalizadas

```php
class Post extends Model
{
    use BelongsToTenants;
    
    protected $tenantColumns = ['tenant_id', 'company_id'];
}
```

## Bypass para Landlords

### Automático
- Usuários autenticados via guard `landlord` têm acesso global
- Rotas com prefixo `/landlord/*` ignoram tenant scoping

### Manual - Desabilitar temporariamente

```php
use Callcocam\ReactPapaLeguas\Landlord\TenantManager;

// Para uma operação específica
app(TenantManager::class)->withoutTenantScoping(function () {
    return Post::all(); // Retorna posts de todos os tenants
});

// Ou usar middleware
Route::middleware('disable.tenant.scoping')->group(function () {
    // Rotas sem tenant scoping
});
```

### Manual - Forçar scoping para landlord

```php
// Quando landlord precisa trabalhar no contexto de um tenant específico
app(TenantManager::class)->withTenantScoping(function () {
    return Post::all(); // Respeitará o tenant atual
});
```

## Controle Dinâmico

```php
$tenantManager = app(TenantManager::class);

// Desabilitar scoping globalmente
$tenantManager->disable();

// Habilitar scoping
$tenantManager->enable();

// Adicionar tenant específico
$tenantManager->addTenant('tenant_id', 123);

// Remover tenant
$tenantManager->removeTenant('tenant_id');

// Verificar se tenant está ativo
if ($tenantManager->hasTenant('tenant_id')) {
    // ...
}
```

## Configuração de Tenant

O sistema resolve automaticamente o tenant por:

1. **Domínio exato**: `empresa1.com` → tenant com domain = 'empresa1.com'
2. **Subdomínio**: `empresa1.app.com` → tenant com prefix = 'empresa1'

## Exemplos de Uso

### Controller Landlord
```php
class LandlordDashboardController extends Controller
{
    public function stats()
    {
        // Automaticamente sem scoping - vê todos os tenants
        $totalUsers = User::count();
        $totalPosts = Post::count();
        
        return view('landlord.stats', compact('totalUsers', 'totalPosts'));
    }
    
    public function tenantDetails($tenantId)
    {
        // Forçar contexto de tenant específico
        return app(TenantManager::class)->withTenantScoping(function () use ($tenantId) {
            app(TenantManager::class)->addTenant('tenant_id', $tenantId);
            return Post::with('user')->get();
        });
    }
}
```

### Model com bypass condicional
```php
class Post extends Model
{
    use BelongsToTenants;
    
    public function scopeGlobal($query)
    {
        // Scope que sempre ignora tenant
        return app(TenantManager::class)->withoutTenantScoping(function () use ($query) {
            return $query;
        });
    }
}

// Uso
$globalPosts = Post::global()->get(); // Posts de todos os tenants
$tenantPosts = Post::all(); // Posts do tenant atual (se houver)
```

## Middleware Disponível

- `landlord.auth`: Autentica usuário via guard landlord
- `disable.tenant.scoping`: Desabilita tenant scoping para a requisição

## Configuração

Veja `config/tenant.php` e `config/react-papa-leguas.php` para opções de configuração.vel & Lumen 5.2+
 
and set your `default_tenant_columns` setting, if you have an app-wide default. LandLord will use this setting to scope models that don’t have a `$tenantColumns` property set.

### Lumen

You'll need to set the service provider in your `bootstrap/app.php`:

```php
$app->register(Callcocam\PapaLeguas\Core\Landlord\LandlordServiceProvider::class);
```

And make sure you've un-commented `$app->withEloquent()`.

## Usage

This package assumes that you have at least one column on all of your Tenant scoped tables that references which tenant each row belongs to.

For example, you might have a `companies` table, and a bunch of other tables that have a `company_id` column.

### Adding and Removing Tenants

> **IMPORTANT NOTE:** Landlord is stateless. This means that when you call `addTenant()`, it will only scope the *current request*.
> 
> Make sure that you are adding your tenants in such a way that it happens on every request, and before you need Models scoped, like in a middleware or as part of a stateless authentication method like OAuth.

You can tell Landlord to automatically scope by a given Tenant by calling `addTenant()`, either from the `Landlord` facade, or by injecting an instance of `TenantManager()`.

You can pass in either a tenant column and id:

```php
Landlord::addTenant('tenant_id', 1);
```

Or an instance of a Tenant model:

```php
$tenant = Tenant::find(1);

Landlord::addTenant($tenant);
```

If you pass a Model instance, Landlord will use Eloquent’s `getForeignKey()` method to decide the tenant column name.

You can add as many tenants as you need to, however Landlord will only allow **one** of each type of tenant at a time.

To remove a tenant and stop scoping by it, simply call `removeTenant()`:

```php
Landlord::removeTenant('tenant_id');

// Or you can again pass a Model instance:
$tenant = Tenant::find(1);

Landlord::removeTenant($tenant);
```

You can also check whether Landlord currently is scoping by a given tenant:

```php
// As you would expect by now, $tenant can be either a string column name or a Model instance
Landlord::hasTenant($tenant);
```

And if for some reason you need to, you can retrieve Landlord's tenants:

```php
// $tenants is a Laravel Collection object, in the format 'tenant_id' => 1
$tenants = Landlord::getTenants();
```

### Setting up your Models

To set up a model to be scoped automatically, simply use the `BelongsToTenants` trait:

```php

use Illuminate\Database\Eloquent\Model;
use Callcocam\PapaLeguas\Core\Landlord\BelongsToTenants;

class ExampleModel extends Model
{
    use BelongsToTenants;
}
```

If you’d like to override the tenants that apply to a particular model, you can set the `$tenantColumns` property:

```php

use Illuminate\Database\Eloquent\Model;
use Callcocam\PapaLeguas\Core\Landlord\BelongsToTenants;

class ExampleModel extends Model
{
    use BelongsToTenants;
    
    public $tenantColumns = ['tenant_id'];
}
```

### Creating new Tenant scoped Models

When you create a new instance of a Model which uses `BelongsToTenants`, Landlord will automatically add any applicable Tenant ids, if they are not already set:

```php
// 'tenant_id' will automatically be set by Landlord
$model = ExampleModel::create(['name' => 'whatever']);
```

### Querying Tenant scoped Models

After you've added tenants, all queries against a Model which uses `BelongsToTenant` will be scoped automatically:

```php
// This will only include Models belonging to the current tenant(s)
ExampleModel::all();

// This will fail with a ModelNotFoundForTenantException if it belongs to the wrong tenant
ExampleModel::find(2);
```

> **Note:** When you are developing a multi tenanted application, it can be confusing sometimes why you keep getting `ModelNotFound` exceptions for rows that DO exist, because they belong to the wrong tenant.
>
> Landlord will catch those exceptions, and re-throw them as `ModelNotFoundForTenantException`, to help you out :)

If you need to query across all tenants, you can use `allTenants()`:

```php
// Will include results from ALL tenants, just for this query
ExampleModel::allTenants()->get()
```

Under the hood, Landlord uses Laravel's [anonymous global scopes](https://laravel.com/docs/5.3/eloquent#global-scopes). This means that if you are scoping by multiple tenants simultaneously, and you want to exclude one of the for a single query, you can do so:

```php
// Will not scope by 'tenant_id', but will continue to scope by any other tenants that have been set
ExampleModel::withoutGlobalScope('tenant_id')->get();
```


## Contributing

If you find an issue, or have a better way to do something, feel free to open an issue or a pull request.