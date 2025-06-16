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

## 🔐 Sistema de Permissões (Frontend)

### 🔧 PermissionButton Component

```tsx
interface PermissionButtonProps {
  permission: string | string[]
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  className?: string
  children: React.ReactNode
  
  // Ações
  onClick?: () => void
  route?: string
  method?: 'get' | 'post' | 'put' | 'delete'
  data?: Record<string, any>
  
  // Comportamento quando sem permissão
  fallbackBehavior?: 'hide' | 'disable' | 'show'
  disabledReason?: string
  tooltip?: string
  
  // Confirmação opcional
  requireConfirmation?: boolean
  confirmTitle?: string
  confirmDescription?: string
}

export const PermissionButton: React.FC<PermissionButtonProps> = ({
  permission,
  fallbackBehavior = 'hide',
  children,
  ...props
}) => {
  const { hasPermission } = usePermissions()
  
  const hasAccess = Array.isArray(permission) 
    ? permission.some(p => hasPermission(p))
    : hasPermission(permission)
  
  if (!hasAccess && fallbackBehavior === 'hide') {
    return null
  }
  
  return (
    <Button 
      {...props}
      disabled={!hasAccess && fallbackBehavior === 'disable'}
      title={!hasAccess ? props.tooltip : undefined}
    >
      {children}
    </Button>
  )
}
```

### 🔗 PermissionLink Component

```tsx
interface PermissionLinkProps {
  permission: string | string[]
  href?: string
  route?: string
  method?: 'get' | 'post' | 'put' | 'delete'
  data?: Record<string, any>
  className?: string
  activeClassName?: string
  children: React.ReactNode
  
  fallbackBehavior?: 'hide' | 'disable' | 'show'
  tooltip?: string
}

export const PermissionLink: React.FC<PermissionLinkProps> = ({
  permission,
  fallbackBehavior = 'hide',
  children,
  ...props
}) => {
  const { hasPermission } = usePermissions()
  
  const hasAccess = Array.isArray(permission) 
    ? permission.some(p => hasPermission(p))
    : hasPermission(permission)
  
  if (!hasAccess && fallbackBehavior === 'hide') {
    return null
  }
  
  return (
    <Link 
      {...props}
      className={!hasAccess ? 'opacity-50 cursor-not-allowed' : props.className}
      title={!hasAccess ? props.tooltip : undefined}
    >
      {children}
    </Link>
  )
}
```

### 🪝 usePermissions Hook

```tsx
interface UsePermissionsReturn {
  hasPermission: (permission: string | string[]) => boolean
  hasAnyPermission: (permissions: string[]) => boolean
  hasAllPermissions: (permissions: string[]) => boolean
  userPermissions: string[]
  userRoles: string[]
  isSuperAdmin: boolean
}

export const usePermissions = (): UsePermissionsReturn => {
  const { props } = usePage()
  const permissions = props.permissions as PermissionsData
  
  const hasPermission = (permission: string | string[]): boolean => {
    if (permissions?.is_super_admin) return true
    
    if (Array.isArray(permission)) {
      return permission.some(p => permissions?.user_permissions?.includes(p))
    }
    
    return permissions?.user_permissions?.includes(permission) ?? false
  }
  
  const hasAnyPermission = (perms: string[]): boolean => {
    return perms.some(p => hasPermission(p))
  }
  
  const hasAllPermissions = (perms: string[]): boolean => {
    return perms.every(p => hasPermission(p))
  }
  
  return {
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    userPermissions: permissions?.user_permissions ?? [],
    userRoles: permissions?.user_roles ?? [],
    isSuperAdmin: permissions?.is_super_admin ?? false,
  }
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
- **Configuração Dinâmica**: Filtros configuráveis via props
- **Tipos Suportados**: Text, Select, Date Range, Number Range, Boolean
- **Estado Persistente**: Mantém filtros ativos durante navegação
- **URL State**: Estado dos filtros mantido na URL

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
- **Colunas Editáveis**: Edição inline com text, select, checkbox

### 🔧 Sistema de Ações
- **Ações por Linha**: Dropdown com ações específicas por registro
- **Ações de Header**: Botões globais na parte superior da tabela
- **Modais Integradas**: Abertura de modais para edição/visualização
- **Confirmação Opcional**: Sistema de confirmação configurável
- **Ações Customizáveis**: Injeção de ações personalizadas
- **Controle de Permissões**: Ações aparecem apenas se usuário tem permissão

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
```tsx
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

## 🧩 Integração com shadcn/ui

O componente utiliza exclusivamente componentes do shadcn/ui com adaptação total ao dark mode:

- **Table**: Estrutura base da tabela com classes dark adaptativas
- **Button**: Botões de ação e filtros com variantes dark
- **Checkbox**: Seleção múltipla com estados dark/light
- **Badge**: Status e tags com cores adaptativas
- **Dialog**: Modais de confirmação com suporte a dark mode
- **DropdownMenu**: Menus de ação com tema adaptativo
- **Input**: Campos de filtro com styling dark/light
- **Select**: Filtros de seleção com tema consistente
- **Tooltip**: Tooltips explicativos para elementos desabilitados

## 📝 Exemplo Completo de Uso

```tsx
// pages/Users/Index.tsx
import { Head } from '@inertiajs/react'
import { Table, Column, Content, Rows } from '@rpl/components/table'
import { PermissionButton } from '@rpl/components/permissions'
import AuthenticatedLayout from '@/layouts/authenticated-layout'

interface Props {
  users: PaginatedData<User>
  permissions: PermissionsData
  filters: FilterConfig[]
}

export default function UsersIndex({ users, permissions, filters }: Props) {
  return (
    <AuthenticatedLayout>
      <Head title="Usuários" />
      
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-semibold">Usuários</h1>
            <p className="text-muted-foreground">Gerenciar usuários do sistema</p>
          </div>
          
          <PermissionButton
            permission="users.create"
            route="users.create"
          >
            <Plus className="w-4 h-4 mr-2" />
            Novo Usuário
          </PermissionButton>
        </div>

        <Table 
          data={users.data} 
          permissions={permissions}
          pagination={users}
          filters={filters}
        >
          <Column key="name" label="Nome" sortable searchable>
            <Content>
              {(user) => (
                <div className="flex items-center gap-3">
                  <Avatar src={user.avatar} fallback={user.name.charAt(0)} />
                  <div>
                    <div className="font-medium">{user.name}</div>
                    <div className="text-sm text-muted-foreground">ID: {user.id}</div>
                  </div>
                </div>
              )}
            </Content>
          </Column>

          <Column key="email" label="Email" sortable searchable>
            <Content>
              {(user) => (
                <a href={`mailto:${user.email}`} className="text-blue-600 hover:text-blue-800">
                  {user.email}
                </a>
              )}
            </Content>
          </Column>

          <Column key="status" label="Status" filterable>
            <Content>
              {(user) => (
                <Badge variant={user.status === 'active' ? 'success' : 'secondary'}>
                  {user.status_label}
                </Badge>
              )}
            </Content>
          </Column>

          <Column key="actions" label="Ações" width="120px">
            <Content>
              {(user) => (
                <div className="flex gap-1">
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
                    route="users.destroy"
                    method="delete"
                    data={{ id: user.id }}
                    requireConfirmation
                    confirmTitle="Excluir Usuário"
                    confirmDescription={`Tem certeza que deseja excluir ${user.name}?`}
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
                className="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer"
                onClick={() => router.visit(`/users/${user.id}`)}
              />
            )}
          </Rows>
        </Table>
      </div>
    </AuthenticatedLayout>
  )
}
```

## 📋 Checklist de Implementação Frontend

### Core Components
- [ ] **Table.tsx** - Componente wrapper principal
- [ ] **Column.tsx** - Definição de colunas via children
- [ ] **Content.tsx** - Renderizador de conteúdo customizado
- [ ] **Rows.tsx** - Customização de linhas
- [ ] **TableProvider.tsx** - Context provider
- [ ] **parseChildrenColumns()** - Parser de colunas children
- [ ] **parseChildrenRows()** - Parser de rows customizadas

### Permission Components
- [ ] **PermissionButton.tsx** - Botão com validação de permissão
- [ ] **PermissionLink.tsx** - Link com validação de permissão
- [ ] **usePermissions.tsx** - Hook para validação de permissões

### Table Components
- [ ] **TableHeader.tsx** - Cabeçalho com filtros
- [ ] **TableBody.tsx** - Corpo da tabela
- [ ] **TablePagination.tsx** - Componente de paginação
- [ ] **BulkActions.tsx** - Ações em massa
- [ ] **ColumnVisibility.tsx** - Controle de visibilidade
- [ ] **ConfirmDialog.tsx** - Modal de confirmação

### Specialized Columns
- [ ] **MoneyColumn.tsx** - Formatação monetária
- [ ] **DateColumn.tsx** - Formatação de datas
- [ ] **StatusColumn.tsx** - Badges de status
- [ ] **ImageColumn.tsx** - Thumbnails e avatars
- [ ] **ActionColumn.tsx** - Coluna de ações
- [ ] **EditableTextColumn.tsx** - Coluna de texto editável
- [ ] **EditableSelectColumn.tsx** - Coluna de select editável
- [ ] **EditableCheckboxColumn.tsx** - Coluna de checkbox editável

### Filters
- [ ] **TextFilter.tsx** - Filtro de texto
- [ ] **SelectFilter.tsx** - Filtro de seleção
- [ ] **DateRangeFilter.tsx** - Filtro de intervalo de datas
- [ ] **NumberRangeFilter.tsx** - Filtro de intervalo numérico

### Hooks & Utils
- [ ] **useTableData.tsx** - Hook para gerenciamento de dados
- [ ] **useTableFilters.tsx** - Hook para filtros
- [ ] **useTableSelection.tsx** - Hook para seleção
- [ ] **table.types.ts** - Tipos TypeScript
- [ ] **column.types.ts** - Tipos das colunas
- [ ] **permissions.types.ts** - Tipos de permissões

### Testing & Polish
- [ ] **Testes unitários** - Jest + React Testing Library
- [ ] **Testes de permissões** - Validação de controle de acesso
- [ ] **Dark mode testing** - Verificar adaptação de temas
- [ ] **Responsividade** - Testes em diferentes breakpoints
- [ ] **Performance** - Otimização para grandes datasets
- [ ] **Documentação** - Exemplos e guias de uso

---

## 🚀 Conclusão

Este README serve como guia completo de implementação para o componente **Dynamic Table do Papa Léguas** focado exclusivamente no **frontend**, uma solução robusta e flexível que combina:

### ✨ **Destaques Frontend**:
- **Sintaxe Flexível**: Props OU Children declarativa
- **Sistema de Permissões**: Controle de acesso em nível de componente
- **Dark Mode Nativo**: Suporte completo com TailwindCSS
- **shadcn/ui**: Componentes modernos e acessíveis
- **TypeScript**: Tipagem completa para maior segurança
- **Responsivo**: Adaptação perfeita para todos os dispositivos

### 🎯 **Filosofia Frontend-First**:
- **Componentes Reutilizáveis**: Máxima modularidade
- **Flexibilidade de Uso**: Props ou Children syntax
- **Performance**: Otimizado para grandes volumes de dados
- **UX Moderna**: Interface intuitiva e responsiva
- **Controle de Acesso**: Sistema de permissões granular no frontend
- **Acessibilidade**: Componentes shadcn/ui com suporte completo

### 🔧 **Próximos Passos Frontend**:
1. Implementar core components (Table, Column, Content, Rows)
2. Desenvolver sistema de permissões (PermissionButton, PermissionLink)
3. Criar componentes especializados (colunas, filtros)
4. Implementar hooks de gerenciamento de estado
5. Adicionar testes unitários completos
6. Otimizar performance e responsividade

**Este componente será a base sólida para exibição de dados tabulares em todo o ecossistema Papa Léguas! 🚀🦘**
