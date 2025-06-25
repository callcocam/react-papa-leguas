# 🧭 Sistema de Navegação Papa Leguas

Guia completo para implementar navegação dinâmica com permissões no Papa Leguas.

## 📋 **Índice**

1. [Visão Geral](#visão-geral)
2. [Instalação e Configuração](#instalação-e-configuração)
3. [Criando Navegação](#criando-navegação)
4. [NavigationBuilder](#navigationbuilder)
5. [Configuração de Permissões](#configuração-de-permissões)
6. [Integração com Middleware](#integração-com-middleware)
7. [Frontend](#frontend)
8. [Exemplos Práticos](#exemplos-práticos)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 **Visão Geral**

O sistema de navegação do Papa Leguas oferece:

- ✅ **Interface Fluente** para construção de menus
- ✅ **Permissões Integradas** com filtros automáticos
- ✅ **Sub Menus Hierárquicos** ilimitados
- ✅ **Badges e Contadores** dinâmicos
- ✅ **Ícones** do Lucide React
- ✅ **Ordenação** customizável
- ✅ **Modo Desenvolvimento** sem permissões

---

## ⚙️ **Instalação e Configuração**

### **1. Configuração Básica**

```php
// config/app.php
'validate_navigation_permissions' => env('VALIDATE_NAVIGATION_PERMISSIONS', true),
```

```bash
# .env - Produção (validar permissões)
VALIDATE_NAVIGATION_PERMISSIONS=true

# .env - Desenvolvimento (ignorar permissões)
VALIDATE_NAVIGATION_PERMISSIONS=false
```

### **2. Middleware Integration**

```php
// app/Http/Middleware/HandleInertiaRequests.php
use Callcocam\ReactPapaLeguas\Navigation\AdminNavigation;

public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'navigation' => $this->getNavigation($request),
    ];
}

protected function getNavigation(Request $request): array
{
    $user = $request->user();
    if (!$user) return [];
    
    $validatePermissions = config('app.validate_navigation_permissions', true);
    
    // Detectar tipo de usuário
    if ($user->hasRole('admin')) {
        return AdminNavigation::build()->build($validatePermissions);
    }
    
    return [];
}
```

---

## 🏗️ **Criando Navegação**

### **1. Estrutura Básica**

```php
// app/Navigation/AdminNavigation.php
<?php

namespace App\Navigation;

use Callcocam\ReactPapaLeguas\Navigation\NavigationBuilder;

class AdminNavigation
{
    public static function build(): NavigationBuilder
    {
        return NavigationBuilder::create()
            ->item('dashboard')
                ->label('Dashboard')
                ->route('dashboard')
                ->icon('Home')
                ->permission('dashboard.view')
                ->order(1);
    }
}
```

### **2. Navegação Completa**

```php
public static function build(): NavigationBuilder
{
    return NavigationBuilder::create()
        
        // Dashboard
        ->item('dashboard')
            ->label('Dashboard')
            ->route('dashboard')
            ->icon('Home')
            ->permission('dashboard.view')
            ->order(1)
        
        // Administração com Sub Menu
        ->item('admin')
            ->label('Administração')
            ->icon('Shield')
            ->order(2)
            ->submenu(function($menu) {
                $menu->subitem('categories')
                    ->label('Categorias')
                    ->route('admin.categories.index')
                    ->icon('Tag')
                    ->permission('categories.view')
                    ->order(1);
                
                $menu->subitem('products')
                    ->label('Produtos')
                    ->route('admin.products.index')
                    ->icon('Package')
                    ->permission('products.view')
                    ->badge('12', 'success')
                    ->order(2);
            })
        
        // Usuários
        ->item('users')
            ->label('Usuários')
            ->icon('Users')
            ->order(3)
            ->submenu(function($menu) {
                $menu->subitem('users-list')
                    ->label('Lista de Usuários')
                    ->route('users.index')
                    ->icon('UserCheck')
                    ->permission('users.view');
                
                $menu->subitem('roles')
                    ->label('Perfis')
                    ->route('roles.index')
                    ->icon('UserCog')
                    ->permission('roles.view');
            });
}
```

---

## 🔧 **NavigationBuilder**

### **Métodos Disponíveis**

#### **Items Principais**
```php
NavigationBuilder::create()
    ->item('key')                    // Criar novo item
    ->label('Nome do Menu')          // Texto do menu
    ->route('route.name')            // Rota nomeada
    ->url('/custom/url')             // URL customizada
    ->icon('IconName')               // Ícone Lucide
    ->permission('permission.name')  // Permissão necessária
    ->permissions(['perm1', 'perm2']) // Múltiplas permissões (OR)
    ->badge('5', 'destructive')      // Badge com contador
    ->order(1)                       // Ordem de exibição
    ->active(true)                   // Marcar como ativo
    ->target('_blank')               // Target do link
    ->className('custom-class');     // Classes CSS customizadas
```

#### **Sub Menus**
```php
->submenu(function($menu) {
    $menu->subitem('subkey')
        ->label('Sub Item')
        ->route('sub.route')
        ->icon('SubIcon')
        ->permission('sub.permission')
        ->order(1);
});
```

#### **Badges e Contadores**
```php
// Badge simples
->badge('Novo')

// Badge com variante
->badge('5', 'destructive')    // Vermelho
->badge('12', 'success')       // Verde
->badge('3', 'warning')        // Amarelo
->badge('Info', 'secondary')   // Cinza
```

#### **Ícones Lucide**
```php
// Ícones comuns
->icon('Home')           // Casa
->icon('Users')          // Usuários
->icon('Settings')       // Configurações
->icon('Shield')         // Admin
->icon('Package')        // Produtos
->icon('Tag')           // Categorias
->icon('Ticket')        // Tickets
->icon('BarChart3')     // Relatórios
->icon('TestTube')      // Testes
```

### **Construção Final**
```php
// Com validação de permissões (padrão)
$navigation = AdminNavigation::build()->build();

// Sem validação (desenvolvimento)
$navigation = AdminNavigation::build()->build(false);

// Método auxiliar
$navigation = AdminNavigation::build()->buildWithoutPermissions();
```

---

## 🔐 **Configuração de Permissões**

### **1. Permissões Simples**
```php
->permission('categories.view')        // Uma permissão
->permissions(['edit', 'delete'])      // Múltiplas (OR logic)
```

### **2. Permissões Hierárquicas**
```php
// Item pai
->item('admin')
    ->permission('admin.access')       // Acesso à seção
    ->submenu(function($menu) {
        $menu->subitem('categories')
            ->permission('categories.view');  // Permissão específica
    });
```

### **3. Integração com Shinobi**
```php
// O sistema detecta automaticamente:
$user->hasPermissionTo('categories.view')  // Shinobi
$user->can('categories.view')              // Laravel Gates
```

### **4. Desenvolvimento sem Permissões**
```bash
# .env
VALIDATE_NAVIGATION_PERMISSIONS=false
```

---

## 🔗 **Integração com Middleware**

### **1. HandleInertiaRequests Completo**
```php
<?php

namespace App\Http\Middleware;

use Callcocam\ReactPapaLeguas\Navigation\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'navigation' => $this->getNavigation($request),
        ];
    }

    protected function getNavigation(Request $request): array
    {
        $user = $request->user();
        
        if (!$user) {
            return [];
        }

        $userType = $this->getUserType($user);
        $validatePermissions = config('app.validate_navigation_permissions', true);
        
        switch ($userType) {
            case 'admin':
                try {
                    $navigation = AdminNavigation::build();
                    return $navigation->build($validatePermissions);
                } catch (\Exception $e) {
                    Log::warning('Erro na navegação admin', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                    ]);
                    return $this->getSimpleNavigation();
                }
                
            case 'landlord':
                // return LandlordNavigation::build()->build($validatePermissions);
                return $this->getSimpleNavigation();
                
            default:
                return $this->getBasicNavigation();
        }
    }

    protected function getUserType($user): string
    {
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) return 'admin';
            if ($user->hasRole('landlord')) return 'landlord';
        }
        
        return 'user';
    }

    protected function getSimpleNavigation(): array
    {
        return [
            [
                'key' => 'dashboard',
                'title' => 'Dashboard',
                'href' => route('dashboard'),
                'icon' => 'Home',
                'order' => 1
            ]
        ];
    }
}
```

### **2. Múltiplos Tipos de Usuário**
```php
// Criar navegações específicas
class LandlordNavigation
{
    public static function build(): NavigationBuilder
    {
        return NavigationBuilder::create()
            ->item('tenants')
                ->label('Inquilinos')
                ->route('landlord.tenants.index')
                ->icon('Building')
                ->permission('tenants.view');
    }
}

class TenantNavigation
{
    public static function build(): NavigationBuilder
    {
        return NavigationBuilder::create()
            ->item('profile')
                ->label('Meu Perfil')
                ->route('tenant.profile')
                ->icon('User');
    }
}
```

---

## 🎨 **Frontend**

### **1. Layout Integration**
```tsx
// Layout automático
import { ReactAppLayout } from '@rpl';

<ReactAppLayout 
    breadcrumbs={breadcrumbs}
    title="Página"
>
    {/* Conteúdo */}
</ReactAppLayout>
```

### **2. Navegação Manual**
```tsx
import { usePage } from '@inertiajs/react';

function CustomNavigation() {
    const { props } = usePage();
    const navigation = props.navigation as any[] || [];
    
    return (
        <nav>
            {navigation.map(item => (
                <div key={item.key}>
                    <a href={item.href}>{item.title}</a>
                    {item.subitems && (
                        <ul>
                            {item.subitems.map(subitem => (
                                <li key={subitem.key}>
                                    <a href={subitem.href}>{subitem.title}</a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            ))}
        </nav>
    );
}
```

---

## 💡 **Exemplos Práticos**

### **1. E-commerce**
```php
NavigationBuilder::create()
    ->item('dashboard')
        ->label('Dashboard')
        ->route('dashboard')
        ->icon('Home')
    
    ->item('catalog')
        ->label('Catálogo')
        ->icon('Package')
        ->submenu(function($menu) {
            $menu->subitem('products')
                ->label('Produtos')
                ->route('products.index')
                ->icon('Package')
                ->badge('156', 'success');
            
            $menu->subitem('categories')
                ->label('Categorias')
                ->route('categories.index')
                ->icon('Tag');
        })
    
    ->item('orders')
        ->label('Pedidos')
        ->route('orders.index')
        ->icon('ShoppingCart')
        ->badge('12', 'destructive')
        ->permission('orders.view');
```

### **2. Sistema de Tickets**
```php
NavigationBuilder::create()
    ->item('tickets')
        ->label('Suporte')
        ->icon('Ticket')
        ->submenu(function($menu) {
            $menu->subitem('tickets-open')
                ->label('Tickets Abertos')
                ->route('tickets.index', ['status' => 'open'])
                ->icon('AlertCircle')
                ->badge('42', 'destructive');
            
            $menu->subitem('tickets-kanban')
                ->label('Kanban')
                ->route('tickets.kanban')
                ->icon('Columns');
        });
```

### **3. Multi-tenant**
```php
// AdminNavigation
NavigationBuilder::create()
    ->item('tenants')
        ->label('Inquilinos')
        ->route('admin.tenants.index')
        ->permission('tenants.manage');

// LandlordNavigation  
NavigationBuilder::create()
    ->item('properties')
        ->label('Propriedades')
        ->route('landlord.properties.index')
        ->permission('properties.view');

// TenantNavigation
NavigationBuilder::create()
    ->item('rent')
        ->label('Meu Aluguel')
        ->route('tenant.rent.show');
```

---

## 🐛 **Troubleshooting**

### **Navegação não aparece**
```php
// 1. Verificar permissões
Log::info('User permissions', [
    'user_id' => $user->id,
    'permissions' => $user->getAllPermissions()->pluck('name'),
]);

// 2. Desabilitar validação temporariamente
VALIDATE_NAVIGATION_PERMISSIONS=false

// 3. Verificar logs
Log::info('Navigation built', ['navigation' => $navigation]);
```

### **Rota não encontrada**
```php
// 1. Verificar se rota existe
Route::get('/admin/categories', [CategoryController::class, 'index'])
    ->name('admin.categories.index');

// 2. Usar URL direta temporariamente
->url('/admin/categories')
```

### **Ícone não aparece**
```tsx
// 1. Verificar nome do ícone Lucide
->icon('Home')        // ✅ Correto
->icon('home')        // ❌ Incorreto
->icon('HomeIcon')    // ❌ Incorreto

// 2. Ver ícones disponíveis em: https://lucide.dev/icons/
```

### **Permissões não funcionam**
```php
// 1. Verificar método do usuário
if (method_exists($user, 'hasPermissionTo')) {
    // Shinobi instalado
} else {
    // Usar Gates do Laravel
}

// 2. Verificar nome da permissão
$user->hasPermissionTo('categories.view'); // ✅
$user->hasPermissionTo('category.view');   // ❌
```

---

## 🚀 **Próximos Passos**

1. **Implementar cache** de navegação
2. **Adicionar breadcrumbs** automáticos
3. **Criar navegação** para mobile
4. **Implementar favoritos** do usuário
5. **Adicionar busca** na navegação

---

## 📚 **Recursos Adicionais**

- [Lucide Icons](https://lucide.dev/icons/) - Lista completa de ícones
- [Shinobi Permissions](https://github.com/caffeinated/shinobi) - Sistema de permissões
- [Inertia.js](https://inertiajs.com/) - Framework para SPAs
- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS

---

**🎯 Sistema de navegação Papa Leguas - Simples, poderoso e flexível!**
