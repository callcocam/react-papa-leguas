# Dynamic Table System - Papa Leguas Frontend Plan
## 🎯 Sistema Completo Frontend com Dupla Sintaxe

### 📋 Visão Geral
Sistema de tabelas dinâmicas que suporta **simultaneamente** duas sintaxes:
- **Configuração Dinâmica via Props** (padrão - vem do backend)
- **Declarativa via Children** (customização avançada)
- **Sistema Inteligente** que detecta e evita duplicação de renderização

---

## 🚀 1. ARQUITETURA DUAL SYNTAX

### 1.1 Estrutura de Componentes
```
table/
├── index.tsx                    # PapaLeguasTable (entry point)
├── core/
│   ├── DynamicTable.tsx        # Tabela via props (padrão)
│   ├── DeclarativeTable.tsx    # Tabela via children
│   ├── HybridTable.tsx         # Tabela híbrida (ambas)
│   └── TableDetector.tsx       # Detector de sintaxe
├── children/
│   ├── Table.tsx               # <Table> wrapper component
│   ├── Column.tsx              # <Column> component  
│   ├── Content.tsx             # <Content> component
│   ├── Rows.tsx                # <Rows> component
│   ├── TableProvider.tsx       # Context provider
│   ├── ColumnParser.tsx        # Parser de colunas children
│   └── ContentRenderer.tsx     # Renderizador de content
├── dynamic/
│   ├── ConfigTable.tsx         # Tabela baseada em config
│   ├── ColumnBuilder.tsx       # Builder de colunas dinâmicas
│   └── PropsRenderer.tsx       # Renderizador via props
├── shared/
│   ├── TableHeader.tsx         # Cabeçalho compartilhado
│   ├── TableBody.tsx           # Corpo compartilhado
│   ├── TablePagination.tsx     # Paginação compartilhada
│   ├── TableFilters.tsx        # Filtros compartilhados
│   └── TableActions.tsx        # Ações compartilhadas
└── types/
    ├── table.types.ts          # Tipos base
    ├── dynamic.types.ts        # Tipos para props
    ├── declarative.types.ts    # Tipos para children
    └── hybrid.types.ts         # Tipos híbridos
```

### 1.2 Sistema de Detecção Inteligente
```tsx
// core/TableDetector.tsx
interface TableMode {
  hasChildren: boolean
  hasPropsConfig: boolean
  mode: 'dynamic' | 'declarative' | 'hybrid'
  priority: 'children' | 'props' | 'merge'
}

export const detectTableMode = (
  children: React.ReactNode,
  columns?: ColumnConfig[],
  config?: TableConfig
): TableMode => {
  const hasChildren = React.Children.count(children) > 0
  const hasPropsConfig = !!(columns?.length || config)
  
  // Lógica de detecção
  if (hasChildren && hasPropsConfig) {
    return {
      hasChildren: true,
      hasPropsConfig: true,
      mode: 'hybrid',
      priority: 'children' // Children tem prioridade
    }
  }
  
  if (hasChildren) {
    return {
      hasChildren: true,
      hasPropsConfig: false,
      mode: 'declarative',
      priority: 'children'
    }
  }
  
  return {
    hasChildren: false,
    hasPropsConfig: true,
    mode: 'dynamic',
    priority: 'props'
  }
}
```

---

## 🎨 2. SINTAXES SUPORTADAS

### 2.1 Sintaxe Dinâmica (Padrão - Props)
```tsx
// Configuração vem do backend via Inertia props
<PapaLeguasTable
  data={users}
  columns={columnsFromBackend}
  filters={filtersFromBackend}
  actions={actionsFromBackend}
  permissions={permissions}
  pagination={paginationData}
/>
```

### 2.2 Sintaxe Declarativa (Children)
```tsx
<Table data={users} permissions={permissions}>
  <Column key="name" label="Nome" sortable filterable>
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

  <Column key="actions" label="Ações">
    <Content>
      {(user) => (
        <div className="flex gap-2">
          <PermissionButton permission="users.edit" size="sm">
            <Edit className="w-4 h-4" />
          </PermissionButton>
          <PermissionButton permission="users.delete" size="sm" variant="destructive">
            <Trash className="w-4 h-4" />
          </PermissionButton>
        </div>
      )}
    </Content>
  </Column>
</Table>
```

### 2.3 Sintaxe Híbrida (Ambas)
```tsx
<Table 
  data={users} 
  permissions={permissions}
  columns={baseColumnsFromBackend} // Props do backend
  filters={filtersFromBackend}
>
  {/* Children customizados têm prioridade */}
  <Column key="name" label="Nome Customizado">
    <Content>
      {(user) => <CustomUserDisplay user={user} />}
    </Content>
  </Column>

  {/* Colunas não definidas via children usam config do backend */}
  {/* email, status, created_at virão da config automática */}

  {/* Override de ações via children */}
  <Column key="actions" label="Ações Customizadas">
    <Content>
      {(user) => <CustomActionsMenu user={user} />}
    </Content>
  </Column>
</Table>
```

---

## 🔧 3. IMPLEMENTAÇÃO DOS COMPONENTES CORE

### 3.1 PapaLeguasTable (Entry Point)
```tsx
// index.tsx
interface PapaLeguasTableProps {
  // Props dinâmicas (vem do backend)
  data: any[]
  columns?: ColumnConfig[]
  filters?: FilterConfig[]
  actions?: ActionConfig[]
  bulkActions?: BulkActionConfig[]
  permissions?: PermissionsData
  pagination?: PaginationData
  
  // Props de UI
  loading?: boolean
  className?: string
  onRowClick?: (row: any) => void
  
  // Children declarativos
  children?: React.ReactNode
}

export const PapaLeguasTable: React.FC<PapaLeguasTableProps> = ({
  children,
  columns,
  data,
  permissions,
  ...props
}) => {
  // Detectar modo da tabela
  const tableMode = detectTableMode(children, columns, props)
  
  // Renderizar baseado no modo detectado
  switch (tableMode.mode) {
    case 'declarative':
      return (
        <DeclarativeTable data={data} permissions={permissions}>
          {children}
        </DeclarativeTable>
      )
    
    case 'hybrid':
      return (
        <HybridTable 
          data={data}
          permissions={permissions}
          columns={columns}
          {...props}
        >
          {children}
        </HybridTable>
      )
    
    case 'dynamic':
    default:
      return (
        <DynamicTable
          data={data}
          columns={columns}
          permissions={permissions}
          {...props}
        />
      )
  }
}
```

### 3.2 HybridTable (Sistema Inteligente)
```tsx
// core/HybridTable.tsx
interface HybridTableProps {
  data: any[]
  permissions?: PermissionsData
  columns?: ColumnConfig[]
  children: React.ReactNode
}

export const HybridTable: React.FC<HybridTableProps> = ({
  data,
  permissions,
  columns = [],
  children
}) => {
  // Parse children columns
  const childrenColumns = parseChildrenColumns(children)
  const customRows = parseChildrenRows(children)
  
  // Merge logic: children override props
  const finalColumns = useMemo(() => {
    const merged: ColumnConfig[] = []
    const childrenKeys = childrenColumns.map(col => col.key)
    
    // 1. Adicionar colunas children (prioridade)
    merged.push(...childrenColumns)
    
    // 2. Adicionar colunas props que não foram definidas via children
    const propsOnlyColumns = columns.filter(col => !childrenKeys.includes(col.key))
    merged.push(...propsOnlyColumns)
    
    return merged
  }, [childrenColumns, columns])
  
  return (
    <TableProvider value={{ data, permissions, columns: finalColumns }}>
      <div className="table-container">
        <TableFilters />
        <TableHeader columns={finalColumns} />
        <TableBody 
          data={data}
          columns={finalColumns}
          customRows={customRows}
        />
        <TablePagination />
      </div>
    </TableProvider>
  )
}
```

### 3.3 Children Parser (Inteligente)
```tsx
// children/ColumnParser.tsx
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
        source: 'children', // Marcar origem
        render: contentChild ? (contentChild as any).props.children : undefined,
        hasCustomContent: !!contentChild
      }
      
      columns.push(column)
    }
  })
  
  return columns
}

export const parseChildrenRows = (children: React.ReactNode): CustomRowRenderer | undefined => {
  const rowsChild = React.Children.toArray(children)
    .find(child => React.isValidElement(child) && child.type === Rows)
  
  return rowsChild ? (rowsChild as any).props.children : undefined
}
```

---

## 🎯 4. SISTEMA DE PRIORIDADES

### 4.1 Regras de Precedência
```tsx
// Ordem de prioridade (maior para menor):
// 1. Children Content > Props render function
// 2. Children Column config > Props column config  
// 3. Children Rows > Props row renderer
// 4. Props como fallback quando children não definido

interface ColumnMergeStrategy {
  key: string
  source: 'children' | 'props' | 'merged'
  priority: number
  hasCustomContent: boolean
  config: ColumnConfig
}

const mergeColumnConfigs = (
  childrenCols: ColumnConfig[],
  propsCols: ColumnConfig[]
): ColumnConfig[] => {
  const merged = new Map<string, ColumnMergeStrategy>()
  
  // 1. Processar children (prioridade alta)
  childrenCols.forEach(col => {
    merged.set(col.key, {
      key: col.key,
      source: 'children',
      priority: 1,
      hasCustomContent: !!col.render,
      config: col
    })
  })
  
  // 2. Processar props (prioridade baixa, só se não existir)
  propsCols.forEach(col => {
    if (!merged.has(col.key)) {
      merged.set(col.key, {
        key: col.key,
        source: 'props',
        priority: 2,
        hasCustomContent: false,
        config: col
      })
    }
  })
  
  // 3. Retornar ordenado por prioridade
  return Array.from(merged.values())
    .sort((a, b) => a.priority - b.priority)
    .map(item => item.config)
}
```

### 4.2 Sistema de Override Inteligente
```tsx
// Exemplo de uso prático:
<Table data={users} columns={backendColumns}>
  {/* Esta coluna VAI SOBRESCREVER a config do backend */}
  <Column key="name" label="Nome Customizado">
    <Content>
      {(user) => <CustomNameDisplay user={user} />}
    </Content>
  </Column>
  
  {/* Estas colunas VÃO SER RENDERIZADAS automaticamente via backend config: */}
  {/* - email (se existir em backendColumns) */}
  {/* - status (se existir em backendColumns) */}
  {/* - created_at (se existir em backendColumns) */}
  
  {/* Esta coluna VAI SOBRESCREVER as ações do backend */}
  <Column key="actions" label="Ações">
    <Content>
      {(user) => <CustomActions user={user} />}
    </Content>
  </Column>
</Table>
```

---

## 📋 7. CHECKLIST DE IMPLEMENTAÇÃO

### 7.1 Core System
- [ ] **TableDetector.tsx** - Sistema de detecção de sintaxe
- [ ] **PapaLeguasTable** - Entry point com roteamento inteligente
- [ ] **DynamicTable** - Renderização via props
- [ ] **DeclarativeTable** - Renderização via children
- [ ] **HybridTable** - Sistema híbrido com merge inteligente
- [ ] **ColumnMerger** - Algoritmo de merge com prioridades

### 7.2 Children System
- [ ] **Table.tsx** - Wrapper component para children
- [ ] **Column.tsx** - Definição de colunas declarativas
- [ ] **Content.tsx** - Renderizador de conteúdo customizado
- [ ] **Rows.tsx** - Customização de linhas
- [ ] **ColumnParser.tsx** - Parser inteligente de children
- [ ] **ContentRenderer.tsx** - Renderizador de content

### 7.3 Permission System
- [ ] **PermissionButton.tsx** - Botão com validação
- [ ] **PermissionLink.tsx** - Link com validação
- [ ] **usePermissions.tsx** - Hook de permissões
- [ ] **PermissionProvider.tsx** - Context de permissões

---

## 🚀 8. PRÓXIMOS PASSOS

### Fase 1: Foundation (Sistema Base)
1. Implementar TableDetector e sistema de detecção
2. Criar PapaLeguasTable como entry point
3. Desenvolver DynamicTable para props
4. Implementar DeclarativeTable para children

### Fase 2: Hybrid System (Sistema Híbrido)
1. Criar HybridTable com merge inteligente
2. Implementar ColumnMerger com prioridades
3. Desenvolver sistema de override
4. Testes de conflito e resolução

---

## 🎯 Conclusão

Este plano oferece:

✅ **Flexibilidade Total**: Props OU Children OU Ambas  
✅ **Sistema Inteligente**: Detecção automática sem duplicação  
✅ **Prioridade Clara**: Children override Props  
✅ **Merge Inteligente**: Combinação sem conflitos  
✅ **TypeScript Completo**: Tipagem para todas as sintaxes  
✅ **Performance**: Renderização otimizada  
✅ **Permissões**: Sistema integrado de controle de acesso  

**O Papa Leguas Table System será a solução mais flexível e inteligente para tabelas dinâmicas! 🚀** 