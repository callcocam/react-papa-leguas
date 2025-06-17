# Papa Leguas Table - Versão Simplificada

Uma versão simplificada e otimizada da tabela Papa Leguas que se integra diretamente com o backend PHP. Esta versão foca na **simplicidade** e **performance**, deixando toda a lógica de processamento para o servidor.

## 🎯 Filosofia

- **Backend-First**: Toda lógica de filtros, ordenação e paginação é processada no servidor
- **Zero Configuração**: Dados vêm prontos do backend via Inertia.js
- **TypeScript Nativo**: Tipagem completa para melhor DX
- **Shadcn/UI**: Interface moderna e consistente
- **Performance**: Renderização otimizada com mínimo JavaScript

## 📦 Estrutura

```
papa-leguas-table/
├── index.tsx              # Componente principal
├── types.ts               # Definições TypeScript
├── components/            # Componentes auxiliares
│   ├── TableHeader.tsx    # Cabeçalho com ordenação
│   ├── TableBody.tsx      # Corpo com dados e ações
│   ├── TableCell.tsx      # Células formatadas
│   ├── TablePagination.tsx# Paginação inteligente
│   ├── TableFilters.tsx   # Filtros expansíveis
│   ├── TableActions.tsx   # Ações do cabeçalho
│   └── TableRowActions.tsx# Ações das linhas
├── examples/              # Exemplos de uso
│   └── SimpleExample.tsx  # Exemplo básico
└── README.md             # Esta documentação
```

## 🚀 Uso Básico

### 1. Importar o Componente

```tsx
import PapaLeguasTable from '@/components/papa-leguas-table';
import { TableColumn, TableRow } from '@/components/papa-leguas-table/types';
```

### 2. Definir Colunas

```tsx
const columns: TableColumn[] = [
    {
        key: 'id',
        label: 'ID',
        type: 'text',
        sortable: true,
        width: '80px',
        align: 'center',
    },
    {
        key: 'name',
        label: 'Nome',
        type: 'text',
        sortable: true,
        searchable: true,
    },
    {
        key: 'email',
        label: 'E-mail',
        type: 'text',
        formatConfig: {
            icon: 'mail',
            copyable: true,
        },
    },
    {
        key: 'status',
        label: 'Status',
        type: 'badge',
        formatConfig: {
            colors: {
                ativo: 'success',
                inativo: 'secondary',
            },
        },
    },
];
```

### 3. Usar o Componente

```tsx
<PapaLeguasTable
    data={users}
    columns={columns}
    filters={filters}
    actions={actions}
    pagination={pagination}
    onFilterChange={handleFilterChange}
    onSortChange={handleSortChange}
    onPageChange={handlePageChange}
    onActionClick={handleActionClick}
/>
```

## 🎨 Tipos de Colunas

### Text
```tsx
{
    key: 'name',
    label: 'Nome',
    type: 'text',
    formatConfig: {
        icon: 'user',           // Ícone Lucide
        copyable: true,         // Botão de copiar
        limit: 50,              // Limite de caracteres
        placeholder: 'N/A',     // Texto para valores vazios
    },
}
```

### Badge
```tsx
{
    key: 'status',
    label: 'Status',
    type: 'badge',
    formatConfig: {
        colors: {
            ativo: 'success',
            inativo: 'secondary',
            bloqueado: 'destructive',
        },
    },
}
```

### Boolean
```tsx
{
    key: 'is_admin',
    label: 'Admin',
    type: 'boolean',
    formatConfig: {
        trueIcon: 'shield-check',
        falseIcon: 'shield-x',
        trueColor: 'text-green-600',
        falseColor: 'text-gray-400',
        trueLabel: 'Sim',
        falseLabel: 'Não',
    },
}
```

### Date
```tsx
{
    key: 'created_at',
    label: 'Criado em',
    type: 'date',
    formatConfig: {
        dateFormat: 'dd/MM/yyyy HH:mm',
        since: true, // Mostra "há X dias"
    },
}
```

### Currency
```tsx
{
    key: 'price',
    label: 'Preço',
    type: 'currency',
    formatConfig: {
        currency: 'BRL',
    },
}
```

### Image
```tsx
{
    key: 'avatar',
    label: 'Avatar',
    type: 'image',
    formatConfig: {
        renderAsImage: true,
    },
}
```

## 🔧 Ações

### Ações do Cabeçalho
```tsx
const headerActions: TableAction[] = [
    {
        key: 'create',
        label: 'Novo',
        icon: 'plus',
        color: 'primary',
        route: 'users.create',
    },
    {
        key: 'export',
        label: 'Exportar',
        icon: 'download',
        variant: 'outline',
    },
];
```

### Ações das Linhas
```tsx
const rowActions: TableAction[] = [
    {
        key: 'edit',
        label: 'Editar',
        icon: 'edit',
        variant: 'ghost',
    },
    {
        key: 'delete',
        label: 'Excluir',
        icon: 'trash-2',
        color: 'danger',
        confirm: true,
        confirmTitle: 'Confirmar exclusão',
        confirmMessage: 'Esta ação não pode ser desfeita.',
    },
];
```

## 🔍 Filtros

```tsx
const filters: TableFilter[] = [
    {
        key: 'search',
        label: 'Buscar',
        type: 'text',
        placeholder: 'Nome ou e-mail...',
    },
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        options: [
            { value: 'ativo', label: 'Ativo' },
            { value: 'inativo', label: 'Inativo' },
        ],
    },
    {
        key: 'created_at',
        label: 'Data de Criação',
        type: 'date',
    },
    {
        key: 'is_verified',
        label: 'Apenas Verificados',
        type: 'boolean',
    },
];
```

## 📄 Paginação

A paginação é automática quando os dados do backend incluem informações de paginação:

```php
// No Controller PHP
return Inertia::render('Users/Index', [
    'table' => [
        'data' => $users->items(),
        'pagination' => [
            'currentPage' => $users->currentPage(),
            'lastPage' => $users->lastPage(),
            'perPage' => $users->perPage(),
            'total' => $users->total(),
            'from' => $users->firstItem(),
            'to' => $users->lastItem(),
            'hasPages' => $users->hasPages(),
            'hasMorePages' => $users->hasMorePages(),
            'onFirstPage' => $users->onFirstPage(),
            'onLastPage' => $users->onLastPage(),
        ],
    ],
]);
```

## 🎯 Integração com Backend

### Controller PHP
```php
<?php

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $table = new UsersTable();
        
        // Aplicar filtros, ordenação e paginação
        $query = $table->query($request);
        
        return Inertia::render('Users/Index', [
            'table' => [
                'data' => $query->paginate()->items(),
                'columns' => $table->getColumns(),
                'filters' => $table->getFilters(),
                'actions' => $table->getActions(),
                'pagination' => $query->paginate()->toArray(),
            ],
        ]);
    }
}
```

### Página React
```tsx
import { PageProps } from '@/types';
import PapaLeguasTable from '@/components/papa-leguas-table';
import { PapaLeguasTableData } from '@/components/papa-leguas-table/types';

interface Props extends PageProps {
    table: PapaLeguasTableData['table'];
}

export default function Users({ table }: Props) {
    const handleFilterChange = (filters: Record<string, any>) => {
        router.get(route('users.index'), filters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSortChange = (column: string, direction: 'asc' | 'desc') => {
        router.get(route('users.index'), {
            sort: column,
            direction,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handlePageChange = (page: number) => {
        router.get(route('users.index'), { page }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <PapaLeguasTable
            data={table.data}
            columns={table.columns}
            filters={table.filters}
            actions={table.actions}
            pagination={table.pagination}
            onFilterChange={handleFilterChange}
            onSortChange={handleSortChange}
            onPageChange={handlePageChange}
        />
    );
}
```

## ✨ Recursos

- ✅ **Ordenação**: Clique nos cabeçalhos das colunas
- ✅ **Filtros**: Painel expansível com múltiplos tipos
- ✅ **Paginação**: Navegação inteligente com números
- ✅ **Ações**: Botões e dropdowns com confirmação
- ✅ **Seleção**: Checkboxes para ações em lote
- ✅ **Responsivo**: Layout adaptável para mobile
- ✅ **Loading**: Estados de carregamento
- ✅ **Vazio**: Mensagem quando não há dados
- ✅ **Erro**: Tratamento de erros
- ✅ **Acessibilidade**: ARIA labels e navegação por teclado

## 🎨 Customização

### Estilos
A tabela usa classes Tailwind CSS e pode ser customizada via `className`:

```tsx
<PapaLeguasTable
    className="custom-table"
    // ... outras props
/>
```

### Temas
Compatível com o sistema de temas do shadcn/ui (dark/light mode).

## 🔄 Diferenças da Versão Anterior

| Recurso | Versão Anterior | Nova Versão |
|---------|----------------|-------------|
| **Processamento** | Frontend | Backend |
| **Configuração** | Complexa | Simples |
| **Performance** | Lenta | Rápida |
| **Bundle Size** | Grande | Pequeno |
| **Manutenção** | Difícil | Fácil |
| **Tipos** | Parcial | Completo |

## 🚀 Próximos Passos

1. **Integrar** com o comando `papa-leguas:make-table`
2. **Testar** com dados reais
3. **Otimizar** performance
4. **Adicionar** mais tipos de coluna
5. **Documentar** casos de uso avançados

---

**Criado com ❤️ para o sistema Papa Leguas**