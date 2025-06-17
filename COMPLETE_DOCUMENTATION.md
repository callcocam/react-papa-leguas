# Papa Leguas - Sistema de Tabelas Avançado

## 📋 Visão Geral

O Papa Leguas é um sistema completo de tabelas para Laravel com React/TypeScript que oferece funcionalidades avançadas para criação de interfaces de dados robustas e performáticas.

## 🚀 Funcionalidades Principais

### ✅ Sistema Base de Tabelas
- **Colunas Especializadas**: TextColumn, BadgeColumn, DateColumn, BooleanColumn, ImageColumn
- **Sistema de Actions**: HeaderAction, RowAction, BulkAction
- **Filtros Avançados**: TextFilter, SelectFilter, DateFilter, NumericFilter, BooleanFilter
- **Sintaxe Fluente**: Interface intuitiva e encadeável

### ✅ Sistema de Cache Avançado
- **Cache Inteligente**: TTL adaptativo baseado no tamanho dos dados
- **Cache com Tags**: Invalidação seletiva por grupos
- **Múltiplos Drivers**: Redis, Memcached, File, Database
- **Configurações Pré-definidas**: Dashboard, Relatórios, API
- **Cache por Usuário**: Chaves baseadas em permissões e contexto

### ✅ Sistema de Permissões Robusto
- **Múltiplos Níveis**: Tabela, Coluna, Linha, Action
- **Integração Laravel**: Policies, Gates, Middleware
- **Suporte Spatie**: Integração com spatie/laravel-permission
- **Row-level Security**: Permissões granulares por registro
- **Métodos de Conveniência**: adminOnly(), readOnly(), ownerOnly()

### ✅ Transformação de Dados
- **Transformadores Automáticos**: Data, Moeda, Número, Texto
- **Transformadores Customizados**: Closures globais e por coluna
- **Sistema de Agregação**: Soma, Média, Contagem
- **Cache de Transformação**: Performance otimizada
- **Normalização**: Diferentes fontes de dados

### ✅ Validação e Sanitização
- **Validação por Coluna**: Regras Laravel Validator
- **Validadores Customizados**: Closures e classes
- **Três Modos**: Strict, Lenient, Skip
- **Sanitização Automática**: HTML, Telefone, CPF, Email
- **Correção Automática**: Dados inválidos corrigidos
- **Relatórios Detalhados**: Erros de validação

### ✅ Sistema de Query Avançado
- **Eager Loading**: Relacionamentos otimizados
- **Joins Otimizados**: Inner, Left, Right joins
- **Scopes Customizados**: Reutilização de lógica
- **Select Raw**: Consultas complexas
- **Group By e Having**: Agregações avançadas
- **Busca em Relacionamentos**: Pesquisa profunda
- **Sugestões de Índices**: Otimização automática

### ✅ Paginação Múltipla
- **Paginação Padrão**: Com contagem total
- **Paginação Simples**: Performance otimizada
- **Paginação por Cursor**: Infinite scroll
- **Cache de Contagem**: Grandes datasets
- **Opções Configuráveis**: Itens por página
- **Estatísticas Detalhadas**: Informações de paginação

## 🎯 Exemplo de Uso Completo

```php
use Callcocam\ReactPapaLeguas\Support\Table\Table;

$table = Table::make('users-table')
    ->model(User::class)
    
    // Query e Paginação
    ->querySystem(true)
    ->with(['profile', 'roles'])
    ->searchableColumns(['name', 'email'])
    ->defaultSort('created_at', 'desc')
    ->pagination(true)
    ->perPage(25)
    
    // Cache e Permissões
    ->cache(true, 900)
    ->cacheTags(['users', 'dashboard'])
    ->permissions(true)
    ->permissionGuard('web')
    
    // Transformação e Validação
    ->dataTransformation(true)
    ->transformDate('created_at', 'd/m/Y H:i')
    ->transformBoolean('active')
    ->validation(true)
    ->validateEmail('email')
    ->validateRequired('name')
    
    // Colunas com Sintaxe Fluente
    ->textColumn('name', 'Nome')
        ->searchable()
        ->sortable()
        ->copyable()
    
    ->badgeColumn('status', 'Status')
        ->colors(['active' => 'success'])
    
    ->dateColumn('created_at', 'Criado em')
        ->sortable()
        ->dateFormat('d/m/Y H:i')
    
    // Filtros
    ->textFilter('name', 'Nome')
        ->placeholder('Buscar por nome...')
    
    ->selectFilter('status', 'Status')
        ->options(['active' => 'Ativo'])
    
    // Configurações finais
    ->searchable()
    ->sortable()
    ->filterable()
    ->responsive();
```

## 📚 Documentação Completa

Para documentação detalhada de cada funcionalidade, consulte os arquivos específicos:

- **Colunas**: [COLUMNS.md](COLUMNS.md)
- **Filtros**: [FILTERS.md](FILTERS.md)
- **Actions**: [ACTIONS.md](ACTIONS.md)
- **Cache**: [CACHE.md](CACHE.md)
- **Permissões**: [PERMISSIONS.md](PERMISSIONS.md)
- **Validação**: [VALIDATION.md](VALIDATION.md)
- **Query**: [QUERY.md](QUERY.md)
- **Paginação**: [PAGINATION.md](PAGINATION.md)

## 🔧 Instalação e Configuração

### Requisitos
- PHP 8.1+
- Laravel 10+
- React 18+
- TypeScript 4.5+

### Instalação
```bash
composer require callcocam/react-papa-leguas
php artisan vendor:publish --provider="Callcocam\ReactPapaLeguas\ReactPapaLeguasServiceProvider"
```

## 🧪 Testes

```bash
cd packages/callcocam/react-papa-leguas
composer test
```

## 📞 Suporte

- **Email**: contato@sigasmart.com.br
- **Website**: https://www.sigasmart.com.br
- **Autor**: Claudio Campos (callcocam@gmail.com)

---

**Papa Leguas** - Sistema de Tabelas Avançado para Laravel + React
*Desenvolvido com ❤️ pela equipe SigaSmart* 