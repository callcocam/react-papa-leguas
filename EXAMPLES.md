# Exemplos Práticos - Papa Leguas Standards

## 🚀 Cenários de Uso Real

### 1. **Blog System**

#### Migration:
```php
// create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('title');                    // fonte do slug
    $table->string('slug')->unique();
    $table->text('content');
    $table->text('excerpt')->nullable();
    $table->string('featured_image')->nullable();
    $table->enum('status', BaseStatus::values())->default(BaseStatus::Draft->value);
    $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['slug', 'tenant_id']);
    $table->index(['category_id', 'status']);
    $table->index(['user_id', 'tenant_id']);
});
```

#### Model:
```php
class Post extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug', 
        'content',
        'excerpt',
        'featured_image',
        'status',
        'user_id',
        'tenant_id',
        'category_id',
    ];

    protected function getSlugSource(): string
    {
        return 'title';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

#### Controller:
```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['category', 'user'])
                    ->published()
                    ->latest()
                    ->paginate(12);
                    
        return inertia('Blog/Index', compact('posts'));
    }

    public function show(Post $post) // Route binding por slug
    {
        $post->load(['category', 'user', 'tags']);
        return inertia('Blog/Show', compact('post'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|max:500',
            'category_id' => 'required|exists:categories,id',
        ]);

        $post = Post::create($validated);
        // user_id e tenant_id preenchidos automaticamente
        
        return redirect()->route('posts.show', $post);
    }
}
```

### 2. **E-commerce Products**

#### Migration:
```php
// create_products_table.php
Schema::create('products', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->text('short_description')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->integer('stock')->default(0);
    $table->string('sku')->unique();
    $table->json('images')->nullable();
    $table->enum('status', BaseStatus::values())->default(BaseStatus::Draft->value);
    $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['slug', 'tenant_id']);
    $table->index(['category_id', 'status']);
    $table->index(['sku', 'tenant_id']);
    $table->index('price');
});
```

#### Model:
```php
class Product extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description', 
        'short_description',
        'price',
        'sale_price',
        'stock',
        'sku',
        'images',
        'status',
        'user_id',
        'tenant_id',
        'category_id',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected function getSlugSource(): string
    {
        return 'name';
    }

    // Scopes específicos
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price');
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price);
    }
}
```

### 3. **CRM Contacts**

#### Model:
```php
class Contact extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'phone',
        'company',
        'slug',
        'notes',
        'status',
        'user_id',
        'tenant_id',
    ];

    protected function getSlugSource(): string
    {
        return 'email'; // Slug baseado no email
    }

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
```

## 🎯 Frontend Components (React + TypeScript)

### PostCard Component:
```tsx
interface Post {
    id: string;
    title: string;
    slug: string;
    excerpt: string;
    status: 'draft' | 'published';
    created_at: string;
    user: {
        id: string;
        name: string;
    };
    category: {
        id: string;
        name: string;
        slug: string;
    };
}

const PostCard: React.FC<{ post: Post }> = ({ post }) => {
    return (
        <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div className="p-6">
                <div className="flex items-center justify-between mb-2">
                    <Link 
                        href={`/categories/${post.category.slug}`}
                        className="text-sm text-blue-600 hover:text-blue-800"
                    >
                        {post.category.name}
                    </Link>
                    <StatusBadge status={post.status} />
                </div>
                
                <h3 className="text-xl font-semibold mb-2">
                    <Link 
                        href={`/posts/${post.slug}`}
                        className="text-gray-900 hover:text-blue-600"
                    >
                        {post.title}
                    </Link>
                </h3>
                
                <p className="text-gray-600 mb-4">{post.excerpt}</p>
                
                <div className="flex items-center text-sm text-gray-500">
                    <span>Por {post.user.name}</span>
                    <span className="mx-2">•</span>
                    <time>{new Date(post.created_at).toLocaleDateString('pt-BR')}</time>
                </div>
            </div>
        </div>
    );
};
```

### StatusBadge Component:
```tsx
const StatusBadge: React.FC<{ status: 'draft' | 'published' }> = ({ status }) => {
    const config = {
        draft: {
            label: 'Rascunho',
            className: 'bg-gray-100 text-gray-800 border-gray-200'
        },
        published: {
            label: 'Publicado', 
            className: 'bg-green-100 text-green-800 border-green-200'
        }
    };

    const { label, className } = config[status];

    return (
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${className}`}>
            {label}
        </span>
    );
};
```

## 🔧 Comandos Úteis

### Gerar estrutura completa:
```bash
# Model + Migration + Factory + Controller
php artisan papa-leguas:make-model Product --migration

# Apenas model
php artisan papa-leguas:make-model Category
```

### Estrutura gerada:
```
app/Models/Product.php          # Model seguindo padrões
database/migrations/xxx_create_products_table.php  # Migration padrão
```

## 📊 Query Examples

### Busca otimizada com relacionamentos:
```php
// ✅ Eficiente - Eager loading
$posts = Post::with(['user', 'category'])
            ->published()
            ->latest()
            ->paginate(10);

// ❌ Ineficiente - N+1 queries
$posts = Post::published()->get();
foreach ($posts as $post) {
    echo $post->user->name; // Query para cada post
}
```

### Filtering com scopes:
```php
// Busca com múltiplos filtros
$products = Product::published()
                  ->inStock() 
                  ->onSale()
                  ->where('category_id', $categoryId)
                  ->orderBy('price')
                  ->get();
```

### Tenant isolation automático:
```php
// ✅ Automático - apenas produtos do tenant atual
$products = Product::all();

// ✅ Manual - desabilitar tenant scoping (landlord)
$allProducts = app(TenantManager::class)->withoutTenantScoping(function () {
    return Product::all();
});
```

## 🎨 Tailwind CSS Patterns

### Card Pattern:
```html
<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <div class="p-6">
        <!-- Content -->
    </div>
</div>
```

### Status Badge Pattern:
```html
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
             bg-green-100 text-green-800 border border-green-200">
    Publicado
</span>
```

### Form Pattern:
```html
<form class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nome</label>
        <input type="text" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                      focus:border-blue-500 focus:ring-blue-500">
    </div>
</form>
```

---

**Estes exemplos demonstram como usar os padrões Papa Leguas em cenários reais! 🦘**
