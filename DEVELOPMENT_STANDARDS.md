# Papa Leguas - Padrões de Desenvolvimento

## 📋 Padrões Obrigatórios

### 1. **Estrutura de Banco de Dados**

#### Campos Padrão (Todas as tabelas):
```php
// Primary Key
$table->ulid('id')->primary();

// Campos básicos
$table->string('name');                    // Nome/título do registro
$table->string('slug')->unique();         // URL-friendly identifier
$table->text('description')->nullable();  // Descrição opcional

// Status enum
$table->enum('status', BaseStatus::values())->default(BaseStatus::Draft->value);

// Relationships
$table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
$table->foreignUlid('tenant_id')->nullable()->constrained()->nullOnDelete();

// Timestamps e soft deletes
$table->timestamps();
$table->softDeletes();

// Índices obrigatórios
$table->index(['tenant_id', 'status']);
$table->index(['slug', 'tenant_id']);
$table->index(['user_id', 'tenant_id']);
$table->index('status');
```

#### Exceções (Tabelas Pivot):
- Não precisam de `status`, `slug`, `description`
- Manter apenas relacionamentos, timestamps e índices

### 2. **Models Padrão**

#### Estrutura Base:
```php
<?php

namespace App\Models;

use Callcocam\ReactPapaLeguas\Models\AbstractModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExampleModel extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', 
        'description',
        'status',
        'user_id',
        'tenant_id',
    ];

    // Override se necessário
    protected function getSlugSource(): string
    {
        return 'name'; // ou 'title', etc.
    }
}
```

#### Funcionalidades Herdadas do AbstractModel:
- ✅ **ULID** como primary key
- ✅ **Slug automático** via `callcocam/tall-sluggable` 
- ✅ **SoftDeletes** habilitado
- ✅ **Tenant Scoping** automático
- ✅ **Status enum** com métodos helper
- ✅ **User relationship** automático
- ✅ **Route model binding** por slug

### 3. **Enums Padrão**

#### BaseStatus (Padrão do sistema):
```php
enum BaseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
```

#### Métodos disponíveis:
```php
$model->isPublished();      // bool
$model->isDraft();          // bool  
$model->publish();          // bool - muda status
$model->draft();            // bool - muda status
$model->status->label();    // string - "Rascunho"/"Publicado"
$model->status->color();    // string - "gray"/"green"
$model->status->badgeClass(); // string - classes Tailwind
```

### 4. **Scopes Automáticos**

#### Disponíveis em todos os models:
```php
Model::published()->get();           // Apenas publicados
Model::draft()->get();               // Apenas rascunhos  
Model::bySlug('meu-slug')->first();  // Busca por slug
```

### 5. **Packages Obrigatórios**

#### Backend:
- `callcocam/tall-sluggable` - Geração automática de slugs
- `laravel/framework` - HasUlids trait para ULID
- Multi-tenancy via `BelongsToTenants` trait

#### Frontend:
- **TailwindCSS** - Estilização
- **React** ou **Vue** - Componentes
- **Inertia.js** - Bridge SPA + Laravel
- **TypeScript** - Tipagem (recomendado)

### 6. **Migrations Template**

#### Criar migration padrão:
```bash
php artisan papa-leguas:make-model ExampleModel --migration
```

#### Template gerado (`template_standard_table.php.stub`):
```php
Schema::create('example_models', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->enum('status', BaseStatus::values())->default(BaseStatus::Draft->value);
    $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
    
    // Performance indexes
    $table->index(['tenant_id', 'status']);
    $table->index(['slug', 'tenant_id']);
    $table->index(['user_id', 'tenant_id']);
    $table->index('status');
});
```

## 🚀 Comandos Úteis

### Gerar Model + Migration:
```bash
php artisan papa-leguas:make-model Post --migration
```

### Gerar apenas Model:
```bash
php artisan papa-leguas:make-model Category
```

## 📖 Exemplos Práticos

### 1. **Model Blog Post**:
```php
class Post extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'title',        // <- fonte do slug
        'slug',
        'content',
        'excerpt',
        'status',
        'user_id',
        'tenant_id',
    ];

    protected function getSlugSource(): string
    {
        return 'title'; // Slug gerado do título
    }

    // Relacionamentos específicos
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 2. **Controller Example**:
```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()  // Scope automático
                    ->latest()
                    ->paginate(10);
                    
        return inertia('Posts/Index', compact('posts'));
    }

    public function show(Post $post) // Route binding por slug
    {
        return inertia('Posts/Show', compact('post'));
    }
}
```

### 3. **Frontend Component (React)**:
```tsx
interface Post {
    id: string;
    title: string;
    slug: string;
    content: string;
    status: 'draft' | 'published';
    user: User;
}

const PostCard = ({ post }: { post: Post }) => (
    <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-xl font-semibold">{post.title}</h3>
        <span className={`inline-block px-2 py-1 rounded text-xs ${
            post.status === 'published' 
                ? 'bg-green-100 text-green-800' 
                : 'bg-gray-100 text-gray-800'
        }`}>
            {post.status === 'published' ? 'Publicado' : 'Rascunho'}
        </span>
    </div>
);
```

## ⚠️ Regras Importantes

### ❌ **NÃO FAÇA:**
- Usar `auto_increment` (usar ULID)
- Esquecer do campo `tenant_id` 
- Deixar de implementar `status` enum
- Pular índices de performance
- Usar `id` numérico em URLs (usar slug)

### ✅ **SEMPRE FAÇA:**
- Estender `AbstractModel`
- Implementar `SoftDeletes`
- Adicionar índices compostos
- Validar tenant isolation
- Usar TypeScript no frontend
- Seguir convenções de nomenclatura

## 🔧 Configuração Inicial

### 1. Publicar assets:
```bash
php artisan vendor:publish --provider="Callcocam\ReactPapaLeguas\ReactPapaLeguasServiceProvider"
```

### 2. Rodar migrations:
```bash
php artisan migrate
```

### 3. Instalar frontend:
```bash
npm install
npm run dev
```

## 📚 Documentação de Referência

- [HasUlids Documentation](https://laravel.com/docs/eloquent#uuid-and-ulid-keys)
- [Tall Sluggable Package](https://github.com/callcocam/tall-sluggable)
- [TailwindCSS](https://tailwindcss.com/)
- [Inertia.js](https://inertiajs.com/)
- [React](https://react.dev/) ou [Vue](https://vuejs.org/)

---

**Estes padrões são obrigatórios para manter consistência e performance em todo o projeto Papa Leguas! 🦘**
