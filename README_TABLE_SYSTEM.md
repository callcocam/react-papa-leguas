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

## 🎯 PLANEJAMENTO ARQUITETURAL - Sistema Universal

### **OBJETIVO PRINCIPAL**
- ⏳ Criar sistema de tabelas que funcione como camada de transformação de dados
- ⏳ Independente do frontend (Vue, React, ou qualquer outro)
- ⏳ Formatação avançada via closures e casts antes de chegar no backend e antes de chegar no frontend
- ⏳ Suporte a múltiplas fontes de dados (API, JSON, Excel, Collections)

## 🏗️ ARQUITETURA ESCOLHIDA

### **📋 DECISÃO: Classes Filhas (Opção 2)**

**Definimos usar classes filhas especializadas para cada tabela:**

```php
// UserTable.php - Classe filha especializada
class UserTable extends Table 
{
    protected $model = User::class;
    
    protected function columns(): array 
    {
        return [
            Column::make('id')->label('ID')->sortable(),
            Column::make('name')->label('Nome')->searchable(),
            Column::make('email')->label('E-mail')->searchable(),
            Column::make('status')->label('Status')->badge(),
        ];
    }
    
    protected function filters(): array
    {
        return [
            Filter::select('status')->options(['active', 'inactive']),
            Filter::text('search')->placeholder('Buscar usuários...'),
        ];
    }
}

// UserController.php - Uso no controller
public function index() 
{
    $table = new UserTable();
    return Inertia::render('crud/index', $table->toArray());
}
```

### **🎯 Justificativas da Escolha:**

1. **Organização**: Cada tabela tem sua própria classe especializada
2. **Reutilização**: UserTable pode ser usada em múltiplos controllers
3. **Configuração Centralizada**: Colunas, filtros e formatação em um só lugar
4. **Manutenção**: Mudanças na tabela ficam isoladas e organizadas
5. **Padrão Consistente**: Segue o padrão já estabelecido no projeto
6. **Tipagem Forte**: Melhor IntelliSense e detecção de erros
7. **Extensibilidade**: Fácil de estender com métodos específicos

### **📂 Estrutura de Arquivos:**
```
app/Tables/
├── UserTable.php
├── ProductTable.php
├── CategoryTable.php
└── ...

packages/callcocam/react-papa-leguas/src/Support/Table/
├── Table.php (classe base)
├── Column.php
├── Filter.php
├── Action.php
└── ...
```

## 🔄 FLUXO DE TRANSFORMAÇÃO

### **PIPELINE DUPLO DE TRANSFORMAÇÃO**
- ⏳ **Etapa 1 - Backend**: Dados Brutos → Casts/Closures → Dados Processados → JSON
- ⏳ **Etapa 2 - Frontend**: JSON Recebido → Formatadores Frontend → Dados Finais → Renderização
- ⏳ **Separação clara**: Lógica de negócio no backend, apresentação no frontend
- ⏳ **Auto-conversão**: Array → Collection automaticamente para facilitar manipulação

### **PROCESSAMENTO INTELIGENTE**
- ⏳ **Detecção de tipo**: Models, Arrays, JSON, API responses
- ⏳ **Contexto da linha**: Acesso aos dados completos durante transformação
- ⏳ **Contexto da tabela**: Acesso a configurações globais
- ⏳ **Lazy processing**: Só processa quando necessário
- ⏳ **Batch processing**: Processa múltiplas linhas de uma vez

> **📝 NOTA IMPORTANTE**: Os dados devem sempre vir de uma fonte única por tabela. Se os dados vêm do banco, a tabela trabalha exclusivamente com Models. Se vêm de uma Collection/Array, trabalha só com essa fonte. Isso garante consistência e performance otimizada.

## 📋 ESTRUTURA DE DESENVOLVIMENTO

### **1. CORE - Processamento de Dados**
- ⏳ Criar classe `Table.php` principal
- ⏳ Implementar `DataProcessor.php` para processar dados de qualquer fonte
- ⏳ Desenvolver `ColumnManager.php` para gerenciar colunas e formatação
- ⏳ Criar `CastManager.php` para sistema de casts
- ⏳ Integrar com `EvaluatesClosures` para execução de callbacks

### **2. SISTEMA DE COLUNAS**
- ⏳ Criar classe base `Column.php`
- ⏳ Implementar `TextColumn.php` para textos
- ⏳ Implementar `NumberColumn.php` para números
- ⏳ Implementar `DateColumn.php` para datas
- ⏳ Implementar `BooleanColumn.php` para booleanos
- ⏳ Criar `CustomColumn.php` para closures personalizados
- ⏳ Adicionar suporte a formatação via closures
- ⏳ Implementar meta-dados para colunas (width, align, sortable, etc.)

### **3. SISTEMA DE CASTS**
- ⏳ Criar interface/classe base `Cast.php`
- ⏳ Implementar `CurrencyCast.php` para formatação monetária
- ⏳ Implementar `DateCast.php` para formatação de datas
- ⏳ Implementar `StatusCast.php` para badges de status
- ⏳ Criar `ClosureCast.php` para closures personalizados
- ⏳ Adicionar sistema de pipeline para múltiplos casts
- ⏳ Implementar cache para casts pesados

### **4. FONTES DE DADOS**
- ⏳ Criar interface `DataSource.php`
- ⏳ Implementar `CollectionSource.php` para Laravel Collections
- ⏳ Implementar `ApiSource.php` para APIs externas
- ⏳ Implementar `JsonSource.php` para arquivos JSON
- ⏳ Implementar `ExcelSource.php` para arquivos Excel
- ⏳ Adicionar suporte a paginação por fonte
- ⏳ Implementar filtros e busca por fonte
- ⏳ Criar cache para fontes externas

### **5. SISTEMA DE FORMATADORES**
- ⏳ Criar interface `Formatter.php`
- ⏳ Implementar `CurrencyFormatter.php`
- ⏳ Implementar `DateFormatter.php`
- ⏳ Implementar `CustomFormatter.php` para closures
- ⏳ Adicionar formatadores condicionais
- ⏳ Implementar formatadores compostos
- ⏳ Criar sistema de formatação por contexto

### **6. PROCESSAMENTO DE DADOS**
- ⏳ Implementar pipeline de transformação de dados
- ⏳ Aplicar casts antes da formatação
- ⏳ Aplicar formatadores depois dos casts
- ⏳ Suporte a transformação de dados aninhados
- ⏳ Implementar lazy loading para dados pesados
- ⏳ Adicionar validação de dados transformados

### **7. SISTEMA DE FILTROS**
- ⏳ Criar filtros tipados por coluna
- ⏳ Implementar filtros compostos
- ⏳ Adicionar filtros por relacionamentos
- ⏳ Suporte a filtros customizados via closures
- ⏳ Implementar filtros por range de dados
- ⏳ Criar filtros salvos e reutilizáveis

### **8. SISTEMA DE AÇÕES**
- ⏳ Implementar Header Actions (criar, exportar, etc.)
- ⏳ Implementar Row Actions (editar, excluir, visualizar)
- ⏳ Implementar Bulk Actions (excluir em lote, etc.)
- ⏳ Adicionar ações condicionais
- ⏳ Suporte a ações customizadas via closures
- ⏳ Implementar confirmações e validações

### **9. EXPORTAÇÃO E IMPORTAÇÃO**
- ⏳ Suporte a exportação CSV
- ⏳ Suporte a exportação Excel
- ⏳ Suporte a exportação PDF
- ⏳ Aplicar formatação na exportação
- ⏳ Implementar importação de dados
- ⏳ Validação de dados importados

### **10. FRONTEND AGNÓSTICO**
- ⏳ Gerar estrutura JSON para qualquer frontend
- ⏳ Incluir meta-dados de colunas
- ⏳ Incluir configurações de filtros
- ⏳ Incluir ações disponíveis
- ⏳ Suporte a temas e estilos
- ⏳ Implementar API REST para tabelas

### **11. PERFORMANCE E CACHE**
- ⏳ Implementar cache de dados processados
- ⏳ Cache de casts e formatadores
- ⏳ Lazy loading de relacionamentos
- ⏳ Otimização de queries
- ⏳ Implementar paginação eficiente
- ⏳ Cache de resultados de filtros
- ⏳ Processamento assíncrono para transformações pesadas
- ⏳ Streaming de dados para grandes volumes

### **12. INTEGRAÇÃO COM TRAITS EXISTENTES**
- ⏳ Integrar com `ResolvesModel` para auto-detecção
- ⏳ Integrar com `ModelQueries` para operações CRUD
- ⏳ Integrar com `BelongsToModel` para relacionamentos
- ⏳ Usar `EvaluatesClosures` para callbacks
- ⏳ Manter compatibilidade com controllers existentes

### **13. CONFIGURAÇÃO E CUSTOMIZAÇÃO**
- ⏳ Sistema de configuração via config files
- ⏳ Mapeamentos de casts personalizados
- ⏳ Temas e estilos configuráveis
- ⏳ Formatadores globais
- ⏳ Configuração de fontes de dados
- ⏳ Configuração de cache e performance

### **14. FLEXIBILIDADE E DEBUGGING**
- ⏳ Data enrichment: Adiciona dados relacionados (mesma fonte)
- ⏳ Data validation: Valida dados durante transformação
- ⏳ Data normalization: Padroniza formatos diferentes
- ⏳ Log de transformações: Rastreia cada etapa do pipeline
- ⏳ Métricas de performance: Tempo de cada transformação
- ⏳ Debug mode: Mostra dados antes/depois de cada etapa
- ⏳ Profiling: Identifica gargalos de performance

### **15. DOCUMENTAÇÃO E TESTES**
- ⏳ Documentação completa da API
- ⏳ Guias de uso para diferentes cenários
- ⏳ Testes unitários para todos os componentes
- ⏳ Testes de integração
- ⏳ Benchmarks de performance
- ⏳ Exemplos práticos de implementação

---

**Status**: 🟡 **Arquitetura Definida** - Classes filhas escolhidas, próximo passo: implementar Table.php base e UserTable como exemplo
**Próximo passo**: Criar implementação simples da Table.php e UserTable para teste no frontend
 
 