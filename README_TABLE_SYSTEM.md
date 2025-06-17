# Sistema de Tabelas React Papa Leguas

Sistema completo de tabelas interativas com colunas editáveis, actions, filtros avançados e integração com React/Inertia.js.

## ✨ Características Principais

- 🔧 **Colunas Editáveis**: Edição inline direta na tabela
- 🎯 **Actions Organizadas**: Header, Row e Bulk actions
- 🔍 **Filtros Avançados**: Sistema robusto de filtros tipados
- 📊 **Paginação Inteligente**: Controle completo de paginação
- 🔎 **Busca Global**: Busca em múltiplas colunas
- ↕️ **Ordenação**: Ordenação por qualquer coluna
- 📁 **Exportação**: Exportação para CSV/Excel
- 🏗️ **Extensível**: Sistema baseado em traits e herança
- ✅ **Testado**: Cobertura completa de testes

## 🚀 Instalação e Configuração

### 1. Registro no ServiceProvider

O sistema é automaticamente registrado via ServiceProvider:

```php
// packages/callcocam/react-papa-leguas/src/ReactPapaLeguasServiceProvider.php
public function boot()
{
    // Migrations, commands, views, etc. são registrados automaticamente
}
```

### 2. Traits Disponíveis

O sistema utiliza traits para organização modular:

```php
// Core/Concerns/
BelongsToTenant.php      // Multi-tenancy
BelongsToUser.php        // Relacionamento com usuário
BelongsToLandlord.php    // Relacionamento com landlord
BelongsToTable.php       // Funcionalidades de tabela
BelongsToForm.php        // Funcionalidades de formulário
```

## 📋 Uso Básico

### Controller Simples

```php
<?php

use Callcocam\ReactPapaLeguas\Core\Table\Table;
use Callcocam\ReactPapaLeguas\Models\Tenant;

class TenantController extends Controller
{
    public function index()
    {
        $table = Table::make()
            ->id('tenants-table')
            ->model(Tenant::class)
            ->query(fn() => Tenant::query())
            
            // Colunas
            ->textColumn('name', 'Nome')->searchable()
            ->editableColumn('status', 'Status')->asSelect()
                ->options([
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'inactive', 'label' => 'Inativo'],
                ])
                ->updateRoute('tenants.update-status')
            ->dateColumn('created_at', 'Criado em')->relative()
            
            // Filtros
            ->textFilter('name', 'Nome')->contains()
            ->selectFilter('status', 'Status')->statusOptions()
            
            // Configurações
            ->searchable()
            ->sortable()
            ->paginated()
            ->perPage(15);

        return inertia('tenants/index', [
            'table' => $table->getTableData(),
            'data' => $table->getRecords(),
            'meta' => $table->getMeta(),
        ]);
    }
}
```

## 🏗️ Geração Automática de Controllers

Use o comando Artisan para gerar controllers automaticamente:

```bash
# Controller básico
php artisan papa-leguas:generate-controller UserController

# Controller com modelo
php artisan papa-leguas:generate-controller UserController --model=User

# Controller com recursos completos
php artisan papa-leguas:generate-controller UserController --model=User --resource --table --form

# Controller para área admin
php artisan papa-leguas:generate-controller Admin/UserController --type=admin --model=User --table

# Controller para landlord
php artisan papa-leguas:generate-controller Landlord/TenantController --type=landlord --model=Tenant --resource
```

### Opções do Comando:

- `--model`: Especifica o modelo para integração
- `--resource`: Gera métodos CRUD completos
- `--api`: Gera controller API (sem create/edit)
- `--type`: admin, landlord, ou padrão
- `--table`: Adiciona funcionalidades de tabela
- `--form`: Adiciona funcionalidades de formulário
- `--force`: Força a criação, sobrescrevendo arquivos existentes

## 📊 Sistema de Colunas

### Tipos Disponíveis

```php
// Texto simples
->textColumn('name', 'Nome')
    ->searchable()
    ->copyable()
    ->truncate(50)

// Coluna editável ⭐ NOVO
->editableColumn('status', 'Status')
    ->asSelect()
    ->updateRoute('items.update-field')
    ->autosave()
    ->debounce(1000)
    ->requiresConfirmation()

// Números/Moeda
->numberColumn('price', 'Preço')
    ->currency('BRL')
    ->precision(2)

// Datas
->dateColumn('created_at', 'Criado em')
    ->dateOnly()
    ->relative()
    ->format('d/m/Y')

// Boolean/Status
->booleanColumn('active', 'Ativo')
    ->activeInactive()
    ->asBadge()

// Badges/Status
->badgeColumn('status', 'Status')
    ->statusColors()
    ->statusIcons()

// Imagens
->imageColumn('avatar', 'Avatar')
    ->circular()
    ->size(64)
    ->defaultImage('/default.png')
```

### Edição Inline

As colunas editáveis permitem modificação direta na tabela:

```php
->editableColumn('name', 'Nome')
    ->asText()                           // Tipo: text, textarea, number, select, boolean, date
    ->updateRoute('items.update-field')  // Rota para salvar
    ->autosave()                         // Salva automaticamente
    ->debounce(1000)                     // Delay antes de salvar
    ->validation(['required', 'string']) // Validação
    ->placeholder('Digite...')           // Placeholder
```

## 🔍 Sistema de Filtros

### Tipos Disponíveis

```php
// Filtro de texto
->textFilter('name', 'Nome')
    ->placeholder('Buscar por nome...')
    ->contains()    // Operadores: contains, exact, starts_with, ends_with

// Filtro de seleção
->selectFilter('status', 'Status')
    ->options([
        ['value' => 'active', 'label' => 'Ativo'],
        ['value' => 'inactive', 'label' => 'Inativo'],
    ])
    ->multiple()
    ->clearable()

// Filtro de data
->dateFilter('created_at', 'Data de criação')
    ->includeTime(false)
    ->format('d/m/Y')

// Filtro de intervalo de datas
->dateRangeFilter('created_at', 'Período')
    ->includeTime(true)

// Filtro numérico
->numberFilter('price', 'Preço')
    ->currency('BRL')
    ->operators(['=', '>', '<', '>=', '<='])

// Filtro boolean
->booleanFilter('active', 'Ativo')
    ->activeInactive()
    ->asSelect()    // ou asSwitch(), asCheckbox()
```

## ⚡ Sistema de Actions

### Header Actions

```php
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\CreateHeaderAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Header\ExportHeaderAction;

->headerActions([
    CreateHeaderAction::make()
        ->route('items.create')
        ->label('Novo Item')
        ->color('primary')
        ->icon('plus'),
        
    ExportHeaderAction::make()
        ->route('items.export')
        ->label('Exportar')
        ->color('secondary'),
])
```

### Row Actions

```php
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\ViewRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\EditRowAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows\DeleteRowAction;

->rowActions([
    ViewRowAction::make()
        ->route('items.show')
        ->label('Ver'),
        
    EditRowAction::make()
        ->route('items.edit')
        ->label('Editar'),
        
    DeleteRowAction::make()
        ->route('items.destroy')
        ->label('Excluir')
        ->requiresConfirmation()
        ->confirmationTitle('Confirmar exclusão'),
])
```

### Bulk Actions

```php
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\DeleteBulkAction;
use Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk\ActivateBulkAction;

->bulkActions([
    ActivateBulkAction::make()
        ->route('items.bulk-activate')
        ->label('Ativar selecionados'),
        
    DeleteBulkAction::make()
        ->route('items.bulk-delete')
        ->label('Excluir selecionados')
        ->confirmationTitle('Confirmar exclusão em massa'),
])
```

## 🔧 Configurações Avançadas

### Busca e Ordenação

```php
$table = Table::make()
    // Busca
    ->searchable()
    ->searchPlaceholder('Buscar items...')
    ->searchColumns(['name', 'description', 'tags'])
    
    // Ordenação
    ->sortable()
    ->defaultSort('name', 'asc')
    
    // Paginação
    ->paginated()
    ->perPage(25)
    ->perPageOptions([10, 25, 50, 100]);
```

### Integração com Modelos

```php
$table = Table::make()
    ->model(Tenant::class)
    ->query(function () {
        return Tenant::query()
            ->with(['users', 'subscriptions'])
            ->whereHas('subscriptions', function ($query) {
                $query->where('active', true);
            });
    });
```

## 🧪 Testes

O sistema inclui testes abrangentes:

```bash
# Testes do sistema de tabelas
php artisan test --filter=TableSystemTest

# Testes de CRUD
php artisan test --filter=TenantCrudTest

# Testes do comando de geração
php artisan test --filter=GenerateControllerCommandTest

# Todos os testes do pacote
php artisan test packages/callcocam/react-papa-leguas/tests/
```

### Factories Incluídas

```php
// Tenants para teste
Tenant::factory()->active()->create();
Tenant::factory()->suspended()->subdomain()->create();

// Landlords para teste  
Landlord::factory()->superAdmin()->create();
Landlord::factory()->withRoles(['admin', 'manager'])->create();
```

## 📚 Exemplos Completos

### Exemplo: Tabela de Produtos

```php
public function index()
{
    $table = Table::make()
        ->id('products-table')
        ->model(Product::class)
        ->query(fn() => Product::with(['category', 'supplier']))
        
        // Colunas
        ->imageColumn('image', 'Imagem')
            ->size(48)
            ->circular(false)
        ->textColumn('name', 'Nome')
            ->searchable()
            ->copyable()
        ->editableColumn('price', 'Preço')
            ->asNumber()
            ->currency('BRL')
            ->updateRoute('products.update-price')
            ->autosave()
        ->badgeColumn('status', 'Status')
            ->statusColors()
        ->numberColumn('stock', 'Estoque')
            ->precision(0)
        ->dateColumn('created_at', 'Criado em')
            ->relative()
            
        // Filtros
        ->textFilter('name', 'Nome')
            ->contains()
        ->selectFilter('category_id', 'Categoria')
            ->relationship('category', 'name')
            ->clearable()
        ->numberFilter('price', 'Preço')
            ->currency('BRL')
            ->operators(['>=', '<='])
        ->selectFilter('status', 'Status')
            ->statusOptions()
            
        // Actions
        ->headerActions([
            CreateHeaderAction::make()
                ->route('products.create')
                ->label('Novo Produto'),
            ExportHeaderAction::make()
                ->route('products.export'),
        ])
        ->rowActions([
            ViewRowAction::make()->route('products.show'),
            EditRowAction::make()->route('products.edit'),
            DeleteRowAction::make()
                ->route('products.destroy')
                ->requiresConfirmation(),
        ])
        ->bulkActions([
            ActivateBulkAction::make()
                ->route('products.bulk-activate'),
            DeleteBulkAction::make()
                ->route('products.bulk-delete')
                ->confirmationTitle('Excluir produtos selecionados?'),
        ])
        
        // Configurações
        ->searchable()
        ->sortable()
        ->defaultSort('name', 'asc')
        ->paginated()
        ->perPage(20);

    return inertia('products/index', [
        'table' => $table->getTableData(),
        'products' => $table->getRecords(),
        'meta' => $table->getMeta(),
    ]);
}
```

### Rotas para Edição Inline

```php
// routes/web.php ou routes/admin.php
Route::put('products/{product}/update-price', [ProductController::class, 'updatePrice'])
    ->name('products.update-price');

Route::put('products/{product}/update-field', [ProductController::class, 'updateField'])
    ->name('products.update-field');

// Controller
public function updatePrice(Product $product, Request $request)
{
    $request->validate(['price' => 'required|numeric|min:0']);
    
    $product->update(['price' => $request->price]);
    
    return response()->json(['success' => true]);
}

public function updateField(Product $product, Request $request)
{
    $field = $request->input('field');
    $value = $request->input('value');
    
    // Validação dinâmica baseada no campo
    $rules = $this->getFieldValidationRules($field);
    $request->validate([$field => $rules]);
    
    $product->update([$field => $value]);
    
    return response()->json(['success' => true]);
}
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

- 📧 Email: callcocam@gmail.com, contato@sigasmart.com.br
- 🌐 Website: https://www.sigasmart.com.br
- 📚 Documentação: [TABLE_SYSTEM.md](docs/TABLE_SYSTEM.md)

---

*Sistema desenvolvido por Claudio Campos para o ecossistema React Papa Leguas*
