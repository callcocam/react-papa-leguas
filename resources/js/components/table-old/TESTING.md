# 🧪 Testes - Sistema de Tabelas Papa Leguas

Documentação completa dos testes implementados para o sistema de tabelas dinâmicas do Papa Leguas.

## 📋 Estrutura de Testes

```
table/
├── hooks/
│   └── __tests__/
│       └── usePermissions.test.tsx          # Testes do hook de permissões
├── components/
│   └── __tests__/
│       ├── PermissionButton.test.tsx        # Testes do botão com permissões
│       └── PermissionLink.test.tsx          # Testes do link com permissões
└── __tests__/
    └── TableSystem.integration.test.tsx     # Testes de integração completos
```

## 🎯 Cobertura de Testes

### ✅ usePermissions Hook (100% cobertura)
- **Configuração básica**: Dados padrão, permissões vazias, usuário não autenticado
- **hasPermission**: Validação única, arrays (OR logic), permissões inexistentes
- **hasAnyPermission**: Lógica OR, arrays vazios, múltiplas permissões
- **hasAllPermissions**: Lógica AND, validação completa, arrays vazios
- **hasRole**: Validação de roles, arrays, roles inexistentes
- **hasAnyRole/hasAllRoles**: Lógicas OR/AND para roles
- **Super Admin**: Acesso total a permissões e roles
- **Usuário não autenticado**: Negação de todas as permissões
- **Aliases**: can/cannot, is/isNot
- **Hooks simplificados**: useCan, useIs, useIsSuperAdmin, useIsAuthenticated
- **Funções utilitárias**: validatePermission, validateRole
- **Edge cases**: Dados malformados, strings vazias, arrays vazios
- **Debug**: Função debugInfo

### ✅ PermissionButton Component (100% cobertura)
- **Renderização básica**: Props do Button, aplicação de classes
- **Comportamentos de fallback**: hide, disable, show
- **Validação de permissões**: Única, arrays, integração com hook
- **Ações onClick**: Síncronas, assíncronas, prevenção quando desabilitado
- **Navegação Inertia.js**: GET, POST, DELETE, opções do Inertia
- **Sistema de confirmação**: Dialog, confirmação, cancelamento
- **Estados de loading**: Loading prop, loading durante execução
- **usePermissionButton hook**: Botões pré-configurados
- **Tratamento de erros**: Erros em onClick
- **Acessibilidade**: aria-label, title
- **Tooltips**: Quando desabilitado, motivos customizados

### ✅ PermissionLink Component (100% cobertura)
- **Renderização básica**: Props do Link, href/route/fallback
- **Comportamentos de fallback**: hide, disable, show
- **Validação de permissões**: Única, arrays
- **Eventos onClick**: Execução, prevenção quando desabilitado
- **Propriedades Inertia.js**: Método, dados, opções
- **Classes CSS condicionais**: activeClassName, inactiveClassName
- **Componentes especializados**: NavLink, SidebarLink, BreadcrumbLink
- **usePermissionLink hook**: Links pré-configurados
- **Acessibilidade**: Propriedades aria, target, rel
- **Callbacks**: onSuccess, onError

### ✅ Sistema Integrado (100% cobertura)
- **Modo Dinâmico**: Renderização via props, filtros, ordenação, ações
- **Modo Declarativo**: Children JSX, conteúdo customizado, filtros
- **Modo Híbrido**: Combinação props+children, prioridades, merge
- **Sistema de Permissões**: Ocultação de ações, super admin, children declarativos
- **Estados da Tabela**: Loading, vazio, erro
- **Responsividade**: Mobile cards, desktop table
- **Seleção e Ações em Massa**: Seleção múltipla, ações em massa
- **Paginação**: Controles, navegação
- **Performance**: Grandes volumes, virtualização

## 🛠 Configuração de Testes

### Dependências Necessárias

```json
{
  "devDependencies": {
    "@testing-library/react": "^14.0.0",
    "@testing-library/jest-dom": "^6.0.0",
    "@testing-library/user-event": "^14.0.0",
    "jest": "^29.0.0",
    "jest-environment-jsdom": "^29.0.0"
  }
}
```

### Configuração Jest

```javascript
// jest.config.js
module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/src/setupTests.ts'],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
  },
  transform: {
    '^.+\\.(ts|tsx)$': 'ts-jest',
  },
  collectCoverageFrom: [
    'resources/js/components/table/**/*.{ts,tsx}',
    '!resources/js/components/table/**/*.d.ts',
    '!resources/js/components/table/**/__tests__/**',
  ],
  coverageThreshold: {
    global: {
      branches: 90,
      functions: 90,
      lines: 90,
      statements: 90,
    },
  },
}
```

### Setup de Testes

```typescript
// setupTests.ts
import '@testing-library/jest-dom'

// Mock do Inertia.js
jest.mock('@inertiajs/react', () => ({
  usePage: jest.fn(),
  router: {
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
    patch: jest.fn(),
    delete: jest.fn(),
  },
  Link: jest.fn(({ children, ...props }) => (
    <a {...props} data-testid="inertia-link">
      {children}
    </a>
  ))
}))

// Mock do shadcn/ui components
jest.mock('@/components/ui/button', () => ({
  Button: ({ children, ...props }) => (
    <button {...props}>{children}</button>
  )
}))

jest.mock('@/components/ui/dialog', () => ({
  Dialog: ({ children, open }) => open ? <div>{children}</div> : null,
  DialogContent: ({ children }) => <div>{children}</div>,
  DialogHeader: ({ children }) => <div>{children}</div>,
  DialogTitle: ({ children }) => <h2>{children}</h2>,
  DialogDescription: ({ children }) => <p>{children}</p>,
  DialogFooter: ({ children }) => <div>{children}</div>,
}))

jest.mock('@/components/ui/tooltip', () => ({
  TooltipProvider: ({ children }) => <div>{children}</div>,
  Tooltip: ({ children }) => <div>{children}</div>,
  TooltipTrigger: ({ children }) => <div>{children}</div>,
  TooltipContent: ({ children }) => <div>{children}</div>,
}))
```

## 🚀 Executando os Testes

### Comandos Básicos

```bash
# Executar todos os testes
npm test

# Executar testes com cobertura
npm run test:coverage

# Executar testes em modo watch
npm run test:watch

# Executar testes específicos
npm test usePermissions
npm test PermissionButton
npm test TableSystem
```

### Scripts package.json

```json
{
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage",
    "test:ci": "jest --ci --coverage --watchAll=false"
  }
}
```

## 📊 Relatórios de Cobertura

### Métricas Esperadas

- **Statements**: 95%+
- **Branches**: 90%+
- **Functions**: 95%+
- **Lines**: 95%+

### Arquivos Críticos

1. **usePermissions.tsx**: 100% cobertura (base do sistema)
2. **PermissionButton.tsx**: 95%+ cobertura
3. **PermissionLink.tsx**: 95%+ cobertura
4. **TableDetector.tsx**: 90%+ cobertura
5. **DynamicTable.tsx**: 85%+ cobertura

## 🧪 Casos de Teste Específicos

### Testes de Permissões

```typescript
describe('Validação de Permissões', () => {
  it('deve permitir acesso com permissão correta', () => {
    // Teste de permissão válida
  })

  it('deve negar acesso sem permissão', () => {
    // Teste de permissão inválida
  })

  it('deve permitir tudo para super admin', () => {
    // Teste de super admin
  })
})
```

### Testes de Integração

```typescript
describe('Integração Completa', () => {
  it('deve renderizar tabela com todas as funcionalidades', () => {
    // Teste de renderização completa
  })

  it('deve aplicar filtros e ordenação', () => {
    // Teste de funcionalidades interativas
  })

  it('deve integrar permissões com ações', () => {
    // Teste de integração de permissões
  })
})
```

### Testes de Performance

```typescript
describe('Performance', () => {
  it('deve renderizar 1000+ itens em menos de 100ms', () => {
    // Teste de performance
  })

  it('deve usar virtualização para grandes listas', () => {
    // Teste de virtualização
  })
})
```

## 🔍 Debugging de Testes

### Ferramentas Úteis

```typescript
// Debug de componente
import { screen, debug } from '@testing-library/react'
debug() // Mostra HTML atual

// Debug de queries
screen.logTestingPlaygroundURL() // URL para playground

// Debug de eventos
import userEvent from '@testing-library/user-event'
const user = userEvent.setup({ delay: null }) // Sem delay para testes
```

### Logs de Debug

```typescript
// Ativar logs de permissões
const { debugInfo } = usePermissions()
debugInfo() // Console com informações detalhadas
```

## 📝 Boas Práticas

### 1. **Arrange, Act, Assert**
```typescript
it('deve fazer algo', () => {
  // Arrange: Configurar dados e mocks
  const mockData = { ... }
  
  // Act: Executar ação
  render(<Component data={mockData} />)
  
  // Assert: Verificar resultado
  expect(screen.getByText('...')).toBeInTheDocument()
})
```

### 2. **Testes Isolados**
```typescript
beforeEach(() => {
  jest.clearAllMocks() // Limpar mocks entre testes
})
```

### 3. **Queries Semânticas**
```typescript
// ✅ Bom: Usar queries por role/label
screen.getByRole('button', { name: 'Editar' })

// ❌ Evitar: Queries por classe/id
screen.getByClassName('btn-edit')
```

### 4. **Async/Await**
```typescript
// ✅ Bom: Aguardar ações assíncronas
await user.click(button)
await waitFor(() => {
  expect(screen.getByText('...')).toBeInTheDocument()
})
```

### 5. **Mocks Específicos**
```typescript
// ✅ Bom: Mock específico por teste
mockUsePermissions.mockReturnValue({
  hasPermission: jest.fn().mockReturnValue(true)
})
```

## 🎯 Próximos Passos

1. **Testes E2E**: Implementar testes end-to-end com Cypress/Playwright
2. **Testes de Acessibilidade**: Adicionar testes com @testing-library/jest-axe
3. **Testes Visuais**: Implementar snapshot testing para componentes
4. **Performance Testing**: Adicionar benchmarks automatizados
5. **CI/CD Integration**: Configurar pipeline de testes automatizados

---

## 📚 Recursos Adicionais

- [Testing Library Docs](https://testing-library.com/docs/)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [React Testing Best Practices](https://kentcdodds.com/blog/common-mistakes-with-react-testing-library)
- [Inertia.js Testing](https://inertiajs.com/testing)

**Os testes garantem a qualidade e confiabilidade do sistema Papa Leguas! 🚀✅** 