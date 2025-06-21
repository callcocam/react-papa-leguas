<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Coluna que permite exibir uma sub-tabela aninhada/hierárquica.
 * 
 * Funcionalidades:
 * - Expandir/recolher sub-tabela
 * - Configuração de colunas da sub-tabela
 * - Lazy loading de dados
 * - Relacionamentos Eloquent
 * - Paginação interna
 * - Busca e filtros internos
 */
class NestedTableColumn extends Column
{
    /**
     * Classe da sub-tabela que será renderizada
     */
    protected ?string $nestedTableClass = null;

    /**
     * Se a coluna é expansível (pode ser expandida/recolhida)
     */
    protected bool $expandable = true;

    /**
     * Se deve carregar dados apenas quando expandir (lazy loading)
     */
    protected bool $loadOnExpand = true;

    /**
     * Nome do relacionamento Eloquent a ser usado
     */
    protected ?string $relationship = null;

    /**
     * Closure para customizar a query do relacionamento
     */
    protected ?Closure $relationshipQuery = null;

    /**
     * Configurações da sub-tabela
     */
    protected array $nestedConfig = [
        'per_page' => 5,
        'show_header' => true,
        'show_pagination' => true,
        'searchable' => false,
        'sortable' => false,
        'compact' => true,
    ];

    /**
     * Texto a ser exibido quando a sub-tabela estiver recolhida
     */
    protected ?Closure $summaryUsing = null;

    /**
     * Ícones para estados expandido/recolhido
     */
    protected array $icons = [
        'expanded' => 'chevron-down',
        'collapsed' => 'chevron-right',
        'loading' => 'loader-2',
    ];

    /**
     * Define a classe da sub-tabela
     */
    public function nestedTable(string $class): static
    {
        $this->nestedTableClass = $class;
        return $this;
    }

    /**
     * Define o relacionamento Eloquent a ser usado
     */
    public function relationship(string $relation, ?Closure $query = null): static
    {
        $this->relationship = $relation;
        $this->relationshipQuery = $query;
        return $this;
    }

    /**
     * Define se deve carregar dados apenas quando expandir
     */
    public function loadOnExpand(bool $load = true): static
    {
        $this->loadOnExpand = $load;
        return $this;
    }

    /**
     * Define se a coluna é expansível
     */
    public function expandable(bool $expandable = true): static
    {
        $this->expandable = $expandable;
        return $this;
    }

    /**
     * Configura a sub-tabela
     */
    public function configureNested(array $config): static
    {
        $this->nestedConfig = array_merge($this->nestedConfig, $config);
        return $this;
    }

    /**
     * Define quantos itens por página na sub-tabela
     */
    public function perPage(int $perPage): static
    {
        $this->nestedConfig['per_page'] = $perPage;
        return $this;
    }

    /**
     * Define se deve mostrar cabeçalho na sub-tabela
     */
    public function showHeader(bool $show = true): static
    {
        $this->nestedConfig['show_header'] = $show;
        return $this;
    }

    /**
     * Define se deve mostrar paginação na sub-tabela
     */
    public function showPagination(bool $show = true): static
    {
        $this->nestedConfig['show_pagination'] = $show;
        return $this;
    }

    /**
     * Define se a sub-tabela é pesquisável
     */
    public function searchable(bool $searchable = true): static
    {
        $this->nestedConfig['searchable'] = $searchable;
        return $this;
    }

    /**
     * Define se a sub-tabela é ordenável
     */
    public function sortable(bool $sortable = true): static
    {
        $this->nestedConfig['sortable'] = $sortable;
        return $this;
    }

    /**
     * Define se a sub-tabela deve ser compacta
     */
    public function compact(bool $compact = true): static
    {
        $this->nestedConfig['compact'] = $compact;
        return $this;
    }

    /**
     * Define como exibir o resumo quando recolhida
     */
    public function summaryUsing(Closure $callback): static
    {
        $this->summaryUsing = $callback;
        return $this;
    }

    /**
     * Define ícones personalizados
     */
    public function icons(array $icons): static
    {
        $this->icons = array_merge($this->icons, $icons);
        return $this;
    }

    /**
     * Obtém o tipo da coluna
     */
    public function getType(): string
    {
        return 'nested_table';
    }

    /**
     * Formatar o valor da coluna (implementação do método abstrato)
     * Para NestedTableColumn, retorna o valor sem formatação adicional
     * pois a formatação é feita pela sub-tabela
     */
    protected function format(mixed $value, $row): mixed
    {
        // Para colunas aninhadas, não aplicamos formatação no valor principal
        // A formatação será feita pelos componentes da sub-tabela
        return $value;
    }

    /**
     * Obtém a classe da sub-tabela
     */
    public function getNestedTableClass(): ?string
    {
        return $this->nestedTableClass;
    }

    /**
     * Obtém o relacionamento
     */
    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    /**
     * Obtém a query do relacionamento
     */
    public function getRelationshipQuery(): ?Closure
    {
        return $this->relationshipQuery;
    }

    /**
     * Verifica se é expansível
     */
    public function isExpandable(): bool
    {
        return $this->expandable;
    }

    /**
     * Verifica se deve carregar apenas quando expandir
     */
    public function shouldLoadOnExpand(): bool
    {
        return $this->loadOnExpand;
    }

    /**
     * Obtém as configurações da sub-tabela
     */
    public function getNestedConfig(): array
    {
        return $this->nestedConfig;
    }

    /**
     * Obtém os ícones
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * Gera o resumo para exibição quando recolhida
     */
    public function getSummary($value, $item): string
    {
        if ($this->summaryUsing) {
            $result = $this->evaluate($this->summaryUsing, [
                'value' => $value,
                'item' => $item,
                'column' => $this,
            ]);
            return is_string($result) ? $result : (string) $result;
        }

        // Resumo padrão
        if (is_countable($value)) {
            $count = count($value);
            return $count === 0 ? 'Nenhum item' : "{$count} " . ($count === 1 ? 'item' : 'itens');
        }

        return 'Expandir';
    }

    /**
     * Serializa para array incluindo configurações da sub-tabela
     */
    public function toArray($item = null, array $context = []): array
    {
        $array = parent::toArray($item, $context);

        if (empty($array)) {
            return $array;
        }

        $array['nested_table_class'] = $this->nestedTableClass;
        $array['relationship'] = $this->relationship;
        $array['expandable'] = $this->expandable;
        $array['load_on_expand'] = $this->loadOnExpand;
        $array['nested_config'] = $this->nestedConfig;
        $array['icons'] = $this->icons;

        // Se não é lazy loading, incluir dados da sub-tabela
        if (!$this->loadOnExpand && $item && $this->relationship) {
            $relationData = data_get($item, $this->relationship);
            $array['nested_data'] = $relationData;
        }

        // Incluir resumo
        if ($item) {
            $value = $this->getValue($item, $context);
            $array['summary'] = $this->getSummary($value, $item);
        }

        return $array;
    }
} 