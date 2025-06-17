# Dynamic Table - Papa Léguas React Components

Um componente de tabela dinâmica e altamente configurável para o pacote `callcocam/react-papa-leguas`, construído com React, TypeScript, shadcn/ui e TailwindCSS. Suporta tanto configuração via props quanto via children components com sintaxe declarativa.

## 📍 Localização

```
packages/callcocam/react-papa-leguas/resources/js/components/table/
```

## 🛠 Stack Tecnológico

- **Frontend**: React + TypeScript + Inertia.js
- **Styling**: TailwindCSS + shadcn/ui
- **Dark Mode**: Suporte nativo com detecção automática
- **Sintaxe**: Props ou Children declarativa
- **Permissões**: Sistema integrado de controle de acesso

## 🎨 Sintaxe de Uso

### 📝 Via Props (Configuração)
```tsx
<PapaLeguasTable
  data={users}
  columns={columnsConfig}
  filters={filtersConfig}
  actions={actionsConfig}
  permissions={permissions}
/>
```

### 🧩 Via Children (Declarativa)
```tsx
<Table data={users} permissions={permissions}>
  <Column 
    key="name" 
    label="Nome" 
    sortable
    filterable
  >
    <Content>
      {(user) => (
        <div className="flex items-center gap-2">
          <Avatar src={user.avatar} />
          <span>{user.name}</span>
        </div>
      )}
    </Content>
  </Column>

  <Column key="email" label="Email" filterable>
    <Content>
      {(user) => <a href={`mailto:${user.email}`}>{user.email}</a>}
    </Content>
  </Column>

  <Column key="status" label="Status">
    <Content>
      {(user) => <Badge variant={user.status}>{user.status_label}</Badge>}
    </Content>
  </Column>

  <Column key="actions" label="Ações" width="120px">
    <Content>
      {(user) => (
        <div className="flex gap-2">
          <PermissionButton 
            permission="users.edit"
            size="sm"
            variant="outline"
            route="users.edit"
            data={{ id: user.id }}
          >
            <Edit className="w-4 h-4" />
          </PermissionButton>
          
          <PermissionButton
            permission="users.delete"
            size="sm" 
            variant="destructive"
            onClick={() => handleDelete(user.id)}
            requireConfirmation
          >
            <Trash className="w-4 h-4" />
          </PermissionButton>
        </div>
      )}
    </Content>
  </Column>

  <Rows>
    {(user) => (
      <tr 
        key={user.id}
        className="hover:bg-gray-50 dark:hover:bg-gray-800"
        onClick={() => router.visit(`/users/${user.id}`)}
      >
        {/* Auto-render columns based on Column definitions */}
      </tr>
    )}
  </Rows>
</Table>
```

### 🔀 Sintaxe Híbrida (Misturada)
```tsx
<Table data={users} permissions={permissions}>
  {/* Colunas via children com conteúdo customizado */}
  <Column key="name" label="Nome">
    <Content>
      {(user) => <UserAvatar user={user} />}
    </Content>
  </Column>

  {/* Colunas via configuração simples */}
  <Column 
    key="email" 
    label="Email"
    type="email"
    filterable
  />

  {/* Custom content complexo via children */}
  <Column key="custom" label="Custom">
    <Content>
      {(user) => (
        <CustomComponent 
          data={user} 
          permissions={permissions}
        />
      )}
    </Content>
  </Column>
</Table>
```

## 🔐 Sistema de Permissões e Componentes Condicionais

### 🔧 Componentes com Validação de Permissão

O Papa Léguas inclui componentes wrapper que automaticamente verificam permissões do usuário, ocultando ou desabilitando elementos conforme necessário.

#### PermissionLink Component

Wrapper para links com validação de permissão integrada:

```typescript
interface PermissionLinkProps {
  permission: string | string[]     // Permissão(ões) necessária(s)
  href?: string                     // URL de destino
  route?: string                    // Nome da rota Laravel
  method?: 'get' | 'post' | 'put' | 'delete'
  data?: Record<string, any>        // Dados para envio (POST/PUT)
  preserveScroll?: boolean          // Manter scroll do Inertia
  preserveState?: boolean           // Preservar estado do Inertia
  onlyActiveOnIndex?: boolean       // Ativo apenas na rota exata
  className?: string                // Classes Tailwind customizadas
  activeClassName?: string          // Classes quando link ativo
  inactiveClassName?: string        // Classes quando link inativo
  children: React.ReactNode         // Conteúdo do link
  
  // Comportamento quando sem permissão
  fallbackBehavior?: 'hide' | 'disable' | 'show'  // Default: 'hide'
  disabledClassName?: string        // Classes quando desabilitado
  tooltip?: string                  // Tooltip explicativo quando desabilitado
}

// Exemplo de uso
<PermissionLink
  permission="users.edit"
  route="users.edit"
  data={{ id: user.id }}
  className="text-blue-600 hover:text-blue-800 dark:text-blue-400"
  fallbackBehavior="disable"
  tooltip="Você não tem permissão para editar usuários"
>
  <Edit className="w-4 h-4" />
  Editar Usuário
</PermissionLink>
```

#### PermissionButton Component

Wrapper para botões com validação de permissão:

```typescript
interface PermissionButtonProps {
  permission: string | string[]     // Permissão(ões) necessária(s)
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  className?: string                // Classes Tailwind customizadas
  children: React.ReactNode         // Conteúdo do botão
  
  // Ações
  onClick?: () => void              // Ação ao clicar
  route?: string                    // Rota Inertia para navegar
  method?: 'get' | 'post' | 'put' | 'delete'
  data?: Record<string, any>        // Dados para envio
  
  // Comportamento quando sem permissão
  fallbackBehavior?: 'hide' | 'disable' | 'show'  // Default: 'hide'
  disabledReason?: string           // Motivo da desabilitação
  showTooltip?: boolean             // Mostrar tooltip explicativo
  
  // Confirmação opcional
  requireConfirmation?: boolean
  confirmTitle?: string
  confirmDescription?: string
  confirmButtonText?: string
  cancelButtonText?: string
}

// Exemplo de uso
<PermissionButton
  permission={["users.delete", "admin.access"]} // Múltiplas permissões (OR)
  variant="destructive"
  onClick={() => handleDelete(user.id)}
  requireConfirmation={true}
  confirmTitle="Excluir Usuário"
  confirmDescription="Esta ação não pode ser desfeita."
  fallbackBehavior="disable"
  showTooltip={true}
  disabledReason="Você não tem permissão para excluir usuários"
>
  <Trash2 className="w-4 h-4 mr-2" />
  Excluir
</PermissionButton>
```

### 🔒 Hook de Permissões

Hook para verificação manual de permissões:

```typescript
interface UsePermissionsReturn {
  hasPermission: (permission: string | string[]) => boolean
  hasAnyPermission: (permissions: string[]) => boolean
  hasAllPermissions: (permissions: string[]) => boolean
  userPermissions: string[]
  userRoles: string[]
  isSuperAdmin: boolean
}

const usePermissions = (): UsePermissionsReturn => {
  // Implementação que acessa as permissões do usuário via Inertia
  // props vindas do Laravel
}

// Exemplo de uso
const MyComponent = () => {
  const { hasPermission, hasAnyPermission } = usePermissions();
  
  return (
    <div>
      {hasPermission('users.create') && (
        <button>Criar Usuário</button>
      )}
      
      {hasAnyPermission(['users.edit', 'users.delete']) && (
        <div>Ações de Usuário</div>
      )}
    </div>
  );
};
```

### 🛡️ Configuração de Permissões no Backend

```php
// Laravel Controller - Passando permissões via Inertia
public function index()
{
    return Inertia::render('Users/Index', [
        'users' => UserResource::collection($users),
        'permissions' => [
            'user_permissions' => auth()->user()->getAllPermissions()->pluck('name'),
            'user_roles' => auth()->user()->getRoleNames(),
            'is_super_admin' => auth()->user()->hasRole('super-admin'),
        ],
        'table_config' => [
            'columns' => $this->getColumnsConfig(),
            'actions' => $this->getActionsConfig(),
            'bulk_actions' => $this->getBulkActionsConfig(),
        ]
    ]);
}

// Configuração de ações com permissões
private function getActionsConfig()
{
    return [
        [
            'key' => 'edit',
            'label' => 'Editar',
            'icon' => 'Edit',
            'permission' => 'users.edit',
            'route' => 'users.edit',
            'method' => 'get'
        ],
        [
            'key' => 'delete',
            'label' => 'Excluir',
            'icon' => 'Trash2',
            'variant' => 'destructive',
            'permission' => 'users.delete',
            'requireConfirmation' => true,
            'confirmTitle' => 'Excluir Usuário',
            'route' => 'users.destroy',
            'method' => 'delete'
        ]
    ];
}
```

## 🎨 Recursos Visuais

### 🌙 Dark Mode
- **Detecção Automática**: Respeita preferência do sistema
- **Toggle Manual**: Alternância via interface
- **Persistência**: Estado mantido entre sessões
- **Componentes Adaptativos**: Todos os elementos se adaptam automaticamente

### 🎨 TailwindCSS Integration
- **Utility Classes**: Máximo aproveitamento das classes do Tailwind
- **Design System**: Consistência visual em todos os componentes
- **Responsividade**: Grid e breakpoints nativos do Tailwind
- **Customização**: Fácil personalização via Tailwind config

### 🔍 Sistema de Filtros
- **Configuração Backend**: Filtros totalmente configuráveis via Laravel
- **Tipos Suportados**: Text, Select, Date Range, Number Range, Boolean
- **Aplicação Dinâmica**: Filtros aplicados via Inertia.js visits
- **Estado Persistente**: Mantém filtros ativos durante navegação
- **Laravel Integration**: Query scopes e filtros automáticos no Eloquent
- **URL State**: Estado dos filtros mantido na URL via Inertia

### 📊 Gerenciamento de Colunas
- **Header Inteligente**: Controle de visibilidade por coluna
- **Ordenação Flexível**: Suporte a ordenação ascendente/descendente
- **Validação**: Sistema de validação integrado para cada coluna
- **Responsividade**: Adaptação automática em diferentes telas

### ✅ Seleção e Ações em Massa
- **Checkbox Master**: Seleção/deseleção de todos os registros
- **Seleção Individual**: Controle granular por linha
- **Bulk Actions**: Ações aplicáveis a múltiplos registros selecionados
- **Feedback Visual**: Indicadores claros de itens selecionados
- **Validação de Permissão**: Ações em massa respeitam permissões do usuário

### 🎨 Colunas Especializadas
- **Formatação Monetária**: Suporte a diferentes moedas (BRL, USD, EUR)
- **Datas Personalizadas**: Formatação flexível de datas e timestamps
- **Status Badges**: Componentes visuais para estados
- **Links Dinâmicos**: Colunas clicáveis com roteamento e validação de permissão
- **Imagens**: Suporte a thumbnails e avatars
- **Colunas Editáveis**: Edição inline com text, select, radio, checkbox

### 🔧 Sistema de Ações
- **Ações por Linha**: Dropdown com ações específicas por registro
- **Ações de Header**: Botões globais na parte superior da tabela
- **Modais Integradas**: Abertura de modais para edição/visualização
- **Confirmação Opcional**: Sistema de confirmação configurável via `requireConfirmation`
- **Ações Customizáveis**: Injeção de ações personalizadas
- **Controle de Permissões**: Ações aparecem apenas se usuário tem permissão

## 🏗 Estrutura Laravel + Inertia.js

### Controller Laravel

O controller passa toda configuração via props do Inertia.js, eliminando necessidade de APIs REST separadas.

```
table/
├── index.tsx                    # Componente principal PapaLeguasTable
├── components/
│   ├── TableHeader.tsx         # Cabeçalho com filtros e ações
│   ├── TableBody.tsx           # Corpo da tabela com dados
│   ├── TablePagination.tsx     # Componente de paginação
│   ├── BulkActions.tsx         # Ações em massa
│   ├── ColumnVisibility.tsx    # Controle de visibilidade
│   ├── ConfirmDialog.tsx       # Modal de confirmação
│   ├── PermissionLink.tsx      # Link com validação de permissão
│   └── PermissionButton.tsx    # Botão com validação de permissão
├── columns/
│   ├── MoneyColumn.tsx         # Formatação monetária
│   ├── DateColumn.tsx          # Formatação de datas
│   ├── StatusColumn.tsx        # Badges de status
│   ├── ImageColumn.tsx         # Thumbnails e avatars
│   ├── ActionColumn.tsx        # Coluna de ações com permissões
│   ├── EditableTextColumn.tsx  # Coluna de texto editável
│   ├── EditableSelectColumn.tsx # Coluna de select editável
│   ├── EditableRadioColumn.tsx # Coluna de radio editável
│   └── EditableCheckboxColumn.tsx # Coluna de checkbox editável
├── filters/
│   ├── TextFilter.tsx          # Filtro de texto
│   ├── SelectFilter.tsx        # Filtro de seleção
│   ├── DateRangeFilter.tsx     # Filtro de intervalo de datas
│   └── NumberRangeFilter.tsx   # Filtro de intervalo numérico
├── hooks/
│   ├── useTableData.tsx        # Hook para gerenciamento de dados
│   ├── useTableFilters.tsx     # Hook para filtros
│   ├── useTableSelection.tsx   # Hook para seleção
│   └── usePermissions.tsx      # Hook para validação de permissões
└── types/
    ├── table.types.ts          # Tipos TypeScript
    ├── column.types.ts         # Tipos das colunas
    └── permissions.types.ts    # Tipos de permissões
```

## 🏗️ Estrutura de Componentes Frontend

```
table/
├── index.tsx                    # PapaLeguasTable (componente principal)
├── core/
│   ├── Table.tsx               # <Table> wrapper component
│   ├── Column.tsx              # <Column> component  
│   ├── Content.tsx             # <Content> component
│   └── Rows.tsx                # <Rows> component
├── children/
│   ├── TableProvider.tsx       # Context provider para children
│   ├── ColumnRenderer.tsx      # Renderizador de colunas children
│   └── ContentParser.tsx       # Parser de content children
├── components/
│   ├── TableHeader.tsx         # Cabeçalho com filtros
│   ├── TableBody.tsx           # Corpo da tabela
│   ├── TablePagination.tsx     # Componente de paginação
│   ├── BulkActions.tsx         # Ações em massa
│   ├── ColumnVisibility.tsx    # Controle de visibilidade
│   └── ConfirmDialog.tsx       # Modal de confirmação
├── columns/
│   ├── MoneyColumn.tsx         # Formatação monetária
│   ├── DateColumn.tsx          # Formatação de datas
│   ├── StatusColumn.tsx        # Badges de status
│   ├── ImageColumn.tsx         # Thumbnails e avatars
│   ├── ActionColumn.tsx        # Coluna de ações
│   ├── EditableTextColumn.tsx  # Coluna de texto editável
│   ├── EditableSelectColumn.tsx # Coluna de select editável
│   └── EditableCheckboxColumn.tsx # Coluna de checkbox editável
├── filters/
│   ├── TextFilter.tsx          # Filtro de texto
│   ├── SelectFilter.tsx        # Filtro de seleção
│   ├── DateRangeFilter.tsx     # Filtro de intervalo de datas
│   └── NumberRangeFilter.tsx   # Filtro de intervalo numérico
├── hooks/
│   ├── useTableData.tsx        # Hook para gerenciamento de dados
│   ├── useTableFilters.tsx     # Hook para filtros
│   ├── useTableSelection.tsx   # Hook para seleção
│   └── usePermissions.tsx      # Hook para validação de permissões
└── types/
    ├── table.types.ts          # Tipos TypeScript
    ├── column.types.ts         # Tipos das colunas
    └── permissions.types.ts    # Tipos de permissões
```

## 🧩 Core Components API

### Table Component
```tsx
interface TableProps {
  data: any[]
  permissions?: PermissionsData
  loading?: boolean
  pagination?: PaginationData
  filters?: FilterConfig[]
  onRowClick?: (row: any) => void
  className?: string
  children: React.ReactNode
}

export const Table: React.FC<TableProps> = ({
  data,
  permissions,
  children,
  ...props
}) => {
  const columns = parseChildrenColumns(children)
  const customRows = parseChildrenRows(children)
  
  return (
    <TableProvider value={{ data, permissions, columns }}>
      <div className="table-container">
        <TableFilters />
        <TableHeader columns={columns} />
        <TableBody 
          data={data}
          columns={columns}
          customRows={customRows}
        />
        <TablePagination />
    </div>
    </TableProvider>
  )
}
```

### Column Component
```tsx
interface ColumnProps {
  key: string
      label: string
  sortable?: boolean
  filterable?: boolean
  width?: string
  type?: 'text' | 'email' | 'money' | 'date' | 'status' | 'actions'
  permission?: string
  className?: string
  children?: React.ReactNode
}

export const Column: React.FC<ColumnProps> = ({ children, ...config }) => {
  // Este componente é parseado pelo pai, não renderiza diretamente
  return null
}
```

### Content Component
```tsx
interface ContentProps {
  children: (row: any, index: number) => React.ReactNode
}

export const Content: React.FC<ContentProps> = ({ children }) => {
  // Este componente é parseado pelo pai
  return null
}
```

### Rows Component
```tsx
interface RowsProps {
  children: (row: any, index: number) => React.ReactNode
}

export const Rows: React.FC<RowsProps> = ({ children }) => {
  // Este componente é parseado pelo pai
  return null
}
```

## 📝 Children Parser Implementation

```tsx
// children/ContentParser.tsx
export const parseChildrenColumns = (children: React.ReactNode): ColumnConfig[] => {
  const columns: ColumnConfig[] = []
  
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child) && child.type === Column) {
      const { children: columnChildren, ...columnProps } = child.props
      
      // Parse Content child
      const contentChild = React.Children.toArray(columnChildren)
        .find(c => React.isValidElement(c) && c.type === Content)
      
      const column: ColumnConfig = {
        ...columnProps,
        render: contentChild ? (contentChild as any).props.children : undefined
      }
      
      columns.push(column)
    }
  })
  
  return columns
}

export const parseChildrenRows = (children: React.ReactNode): ((row: any) => React.ReactNode) | undefined => {
  const rowsChild = React.Children.toArray(children)
    .find(child => React.isValidElement(child) && child.type === Rows)
  
  return rowsChild ? (rowsChild as any).props.children : undefined
}
```

## 🔄 Fluxo de Dados Backend-First com Inertia.js

1. **Inicialização**: Laravel controller prepara dados, configuração e permissões
2. **Inertia Props**: Toda configuração passada via props (sem APIs)
3. **Renderização**: Componente React recebe dados e permissões do backend
4. **Validação de Permissões**: Componentes verificam automaticamente permissões
5. **Filtros**: Aplicação via Inertia.js router com preservação de estado
6. **Ordenação**: Processamento server-side com refresh automático
7. **Seleção**: Gerenciamento de estado local no React
8. **Ações**: Execução via Inertia.js router com validação de permissão
9. **Permissões**: Verificação server-side em cada request
10. **Navegação**: SPA experience com Inertia.js e controle de acesso

### Arquitetura de Comunicação

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│                 │    │                  │    │                 │
│  Inertia React  │◄──►│  Laravel + Iner  │◄──►│    Database     │
│                 │    │                  │    │                 │
│  - PapaLeguas   │    │  - Controllers   │    │  - Models       │
│  - TailwindCSS  │    │  - Resources     │    │  - Migrations   │
│  - shadcn/ui    │    │  - Policies      │    │  - Seeders      │
│  - Dark Mode    │    │  - Validation    │    │  - Permissions  │
│  - Permissions  │    │  - Props passing │    │  - Roles        │
│  - No API calls │    │  - Auth & Perms  │    │                 │
│                 │    │                  │    │                 │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

## 📱 Responsividade e Adaptação

### Breakpoints TailwindCSS
- **Mobile (sm)**: Cards verticais com informações essenciais
- **Tablet (md)**: Tabela compacta com colunas prioritárias
- **Desktop (lg+)**: Tabela completa com todas as funcionalidades

### Adaptação Dark Mode
- **Detecção Automática**: Segue preferência do sistema operacional
- **Persistência**: Estado salvo no localStorage
- **Transições**: Animações suaves entre temas
- **Componentes**: Todos os elementos se adaptam automaticamente

### Classes Responsivas Exemplo
```typescript
const responsiveClasses = {
  table: 'hidden md:table', // Oculta em mobile
  cards: 'block md:hidden', // Mostra apenas em mobile
  columns: {
    priority1: 'table-cell', // Sempre visível
    priority2: 'hidden lg:table-cell', // Desktop apenas
    priority3: 'hidden xl:table-cell'  // Telas grandes apenas
  },
  // Classes para elementos sem permissão
  disabled: 'opacity-50 cursor-not-allowed',
  hidden: 'hidden',
  tooltip: 'relative group'
}
```

---

## 🚀 Conclusão

Este README serve como guia completo de implementação para o componente **Dynamic Table do Papa Léguas**, uma solução robusta e flexível que combina:

### ✨ **Destaques Técnicos**:
- **Backend-First**: Toda configuração gerenciada pelo Laravel
- **Sistema de Permissões Integrado**: Controle de acesso em nível de componente
- **Dark Mode Nativo**: Suporte completo com TailwindCSS
- **shadcn/ui**: Componentes modernos e acessíveis
- **TypeScript**: Tipagem completa para maior segurança
- **Responsivo**: Adaptação perfeita para todos os dispositivos

### 🎯 **Filosofia do Projeto**:
- **Configuração Zero no Frontend**: Tudo vem do backend
- **Segurança First**: Validação de permissões em todos os níveis
- **Flexibilidade Máxima**: Extensível e customizável
- **Performance**: Otimizado para grandes volumes de dados
- **UX Moderna**: Interface intuitiva e responsiva
- **Controle de Acesso**: Sistema de permissões granular

### 🔧 **Próximos Passos**:
1. Implementar a estrutura Laravel backend com sistema de permissões
2. Configurar Inertia.js no projeto
3. Criar os componentes React frontend
4. Desenvolver componentes PermissionLink e PermissionButton
5. Integrar hook usePermissions
6. Implementar dark mode completo
7. Adicionar sistema de tooltips para elementos desabilitados
8. Criar testes para validação de permissões
9. Documentar controllers e components com exemplos de segurança

**Este componente será a base sólida e segura para exibição de dados tabulares em todo o ecossistema Papa Léguas com Inertia.js! 🚀🔐**