# This is my package react-papa-leguas

[![Latest Version on Packagist](https://img.shields.io/packagist/v/callcocam/react-papa-leguas.svg?style=flat-square)](https://packagist.org/packages/callcocam/react-papa-leguas)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/callcocam/react-papa-leguas/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/callcocam/react-papa-leguas/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/callcocam/react-papa-leguas/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/callcocam/react-papa-leguas/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/callcocam/react-papa-leguas.svg?style=flat-square)](https://packagist.org/packages/callcocam/react-papa-leguas)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## 📋 Progresso do Projeto

### ✅ Concluído
- [x] Estrutura base do pacote Laravel
- [x] Configuração inicial do React + TypeScript + Inertia.js
- [x] Sistema de autenticação e permissões (Spatie)
- [x] Integração com shadcn/ui e TailwindCSS
- [x] Suporte completo ao Dark Mode

### ⏳ Em Desenvolvimento - Sistema de Tabelas Dinâmicas
- [x] **Planejamento Frontend Completo** - Criado TABLE-FRONTEND-PLAN.md com arquitetura de dupla sintaxe
- [x] **TableDetector.tsx** - Sistema inteligente de detecção de sintaxe (props vs children)
- [x] **PapaLeguasTable** - Entry point principal com roteamento automático
- [x] **Componentes Children** - Table, Column, Content, Rows para sintaxe declarativa
- [x] **DynamicTable** - Renderização via props (configuração backend)
- [x] **DeclarativeTable** - Renderização via children JSX com parsing inteligente
- [x] **ColumnParser** - Sistema de parsing de children com validação e relatórios
- [x] **HybridTable** - Sistema híbrido com merge inteligente e resolução de conflitos
- [x] **ColumnMerger** - Sistema de merge automático entre props e children
- [x] **Sistema de Permissões** - usePermissions, PermissionButton, PermissionLink implementados
- [x] **Testes e Validação** - Suíte completa de testes unitários e integração
- [x] **Layout Principal** - AppLayout configurado com sidebar, navegação e permissões
- [x] **Sistema de Testes** - Página de demonstração e validação completa
- [x] **Configuração de Testes** - Jest, Testing Library e scripts automatizados

### 🎯 Status Final
✅ **PROJETO CONCLUÍDO COM SUCESSO!**

**Todas as fases implementadas:**
1. ✅ **Fase 1**: Sistema base de detecção e roteamento
2. ✅ **Fase 2**: Componentes core (Dynamic, Declarative, Hybrid)
3. ✅ **Fase 3**: Sistema de permissões e componentes condicionais
4. ✅ **Fase 4**: Testes e documentação completa
5. ✅ **Fase 5**: Layout principal e sistema de demonstração
6. ✅ **Fase 6**: Configuração de testes automatizados

**Próximos passos opcionais:**
- Testes E2E com Cypress/Playwright
- Documentação de API completa
- Exemplos de integração com Laravel
- Performance benchmarks

### 🚀 Recursos Planejados
- **Dupla Sintaxe**: Props dinâmicas OU Children declarativos OU Ambos
- **Sistema Inteligente**: Detecção automática sem duplicação de renderização
- **Prioridade Clara**: Children sempre sobrescreve Props quando ambos presentes
- **Merge Inteligente**: Combinação sem conflitos no modo híbrido
- **Permissões Integradas**: Controle de acesso em nível de componente
- **TypeScript Completo**: Tipagem para todas as sintaxes suportadas
- **Layout Responsivo**: Sidebar com navegação baseada em permissões
- **Dark Mode**: Suporte completo com toggle automático

### 📁 Arquivos Criados/Modificados

**Sistema de Tabelas:**
- `resources/js/components/table/TABLE-FRONTEND-PLAN.md` - Plano completo focado no frontend
- `resources/js/components/table/index.tsx` - Entry point principal (PapaLeguasTable)
- `resources/js/components/table/core/TableDetector.tsx` - Sistema de detecção inteligente
- `resources/js/components/table/core/DynamicTable.tsx` - Renderização via props
- `resources/js/components/table/core/DeclarativeTable.tsx` - Renderização via children JSX
- `resources/js/components/table/core/HybridTable.tsx` - Renderização híbrida (props + children)

**Componentes Children:**
- `resources/js/components/table/children/Table.tsx` - Wrapper para sintaxe declarativa
- `resources/js/components/table/children/Column.tsx` - Definição de colunas via JSX
- `resources/js/components/table/children/Content.tsx` - Conteúdo customizado das células
- `resources/js/components/table/children/Rows.tsx` - Customização completa das linhas
- `resources/js/components/table/children/ColumnParser.tsx` - Parser inteligente de children
- `resources/js/components/table/children/ColumnMerger.tsx` - Sistema de merge props + children
- `resources/js/components/table/children/index.tsx` - Exports centralizados

**Sistema de Permissões:**
- `resources/js/components/table/hooks/usePermissions.tsx` - Hook principal de permissões
- `resources/js/components/table/components/PermissionButton.tsx` - Botão com validação de permissões
- `resources/js/components/table/components/PermissionLink.tsx` - Link com validação de permissões

**Layout e Interface:**
- `resources/js/layouts/react-app-layout.tsx` - Layout principal com sidebar e navegação
- `resources/js/pages/crud/index.tsx` - Página de demonstração e testes do sistema

**Configuração de Testes:**
- `jest.config.js` - Configuração do Jest para React + TypeScript
- `jest.setup.js` - Setup com mocks do Inertia.js e shadcn/ui
- `package.json` - Scripts de teste (test, test:watch, test:coverage)

**Exemplos de Uso:**
- `resources/js/components/table/examples/TableExample.tsx` - Exemplo de uso dinâmico
- `resources/js/components/table/examples/DeclarativeExample.tsx` - Exemplo de uso declarativo
- `resources/js/components/table/examples/HybridExample.tsx` - Exemplo de uso híbrido
- `resources/js/components/table/examples/PermissionsExample.tsx` - Exemplo completo de permissões

**Testes e Validação:**
- `resources/js/components/table/hooks/__tests__/usePermissions.test.tsx` - Testes do hook de permissões
- `resources/js/components/table/components/__tests__/PermissionButton.test.tsx` - Testes do botão com permissões
- `resources/js/components/table/components/__tests__/PermissionLink.test.tsx` - Testes do link com permissões
- `resources/js/components/table/__tests__/TableSystem.integration.test.tsx` - Testes de integração completos
- `resources/js/components/table/TESTING.md` - Documentação completa de testes

### 🎨 Características do Layout Principal

**AppLayout Features:**
- **Sidebar Responsiva**: Navegação com base em permissões do usuário
- **Breadcrumbs Dinâmicos**: Sistema de navegação hierárquica
- **Dark Mode Toggle**: Alternância automática entre temas
- **User Info**: Informações do usuário autenticado
- **Permission-Based Navigation**: Itens de menu aparecem apenas se o usuário tem permissão
- **Mobile Friendly**: Sidebar colapsível em dispositivos móveis
- **shadcn/ui Integration**: Componentes modernos e acessíveis

**Sistema de Testes Features:**
- **Dashboard de Testes**: Visualização de estatísticas e resultados
- **Demonstração Interativa**: Teste dos 3 modos de tabela (Dynamic, Declarative, Hybrid)
- **Validação de Permissões**: Teste dos componentes PermissionButton e PermissionLink
- **Debug Mode**: Informações detalhadas sobre permissões e sistema
- **Execução Simulada**: Botão para simular execução de testes
- **Cobertura Visual**: Métricas de cobertura e status dos testes
- **Scripts Automatizados**: Comandos npm para executar testes (test, test:watch, test:coverage)
- **Configuração Completa**: Jest + Testing Library + mocks configurados

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/react-papa-leguas.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/react-papa-leguas)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require callcocam/react-papa-leguas
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="react-papa-leguas-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="react-papa-leguas-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="react-papa-leguas-views"
```

## Usage

```php
$reactPapaLeguas = new Callcocam\ReactPapaLeguas();
echo $reactPapaLeguas->echoPhrase('Hello, Callcocam!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Claudio Campos](https://github.com/callcocam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
