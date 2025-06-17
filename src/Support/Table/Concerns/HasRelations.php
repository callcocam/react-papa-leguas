<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para gerenciar relacionamentos da tabela (RelationManager)
 */
trait HasRelations
{
    /**
     * Se a tabela está em modo RelationManager
     */
    protected bool $isRelationManager = false;

    /**
     * Modelo pai do relacionamento
     */
    protected ?Model $parentRecord = null;

    /**
     * Nome do relacionamento
     */
    protected ?string $relationshipName = null;

    /**
     * Tipo do relacionamento
     */
    protected ?string $relationshipType = null;

    /**
     * Título contextual do RelationManager
     */
    protected ?string $relationTitle = null;

    /**
     * Descrição contextual do RelationManager
     */
    protected ?string $relationDescription = null;

    /**
     * Se permite criar novos registros relacionados
     */
    protected bool $canCreateRelated = true;

    /**
     * Se permite anexar registros existentes
     */
    protected bool $canAttachExisting = true;

    /**
     * Se permite desanexar registros
     */
    protected bool $canDetachRecords = true;

    /**
     * Campos para exibir do modelo pai
     */
    protected array $parentDisplayFields = ['name', 'title', 'id'];

    /**
     * Configurar como RelationManager
     */
    public function forRelation(Model $parentRecord, string $relationshipName): static
    {
        $this->isRelationManager = true;
        $this->parentRecord = $parentRecord;
        $this->relationshipName = $relationshipName;
        
        // Detectar tipo do relacionamento
        $this->detectRelationshipType();
        
        // Configurar título contextual
        $this->setupContextualTitle();
        
        // Configurar query para o relacionamento
        $this->setupRelationQuery();
        
        return $this;
    }

    /**
     * Detectar tipo do relacionamento
     */
    protected function detectRelationshipType(): void
    {
        if (!$this->parentRecord || !$this->relationshipName) {
            return;
        }

        $relation = $this->parentRecord->{$this->relationshipName}();
        
        $this->relationshipType = match (true) {
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany => 'hasMany',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne => 'hasOne',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany => 'belongsToMany',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo => 'belongsTo',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasOneThrough => 'hasOneThrough',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasManyThrough => 'hasManyThrough',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\MorphMany => 'morphMany',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\MorphOne => 'morphOne',
            $relation instanceof \Illuminate\Database\Eloquent\Relations\MorphToMany => 'morphToMany',
            default => 'unknown'
        };
    }

    /**
     * Configurar título contextual
     */
    protected function setupContextualTitle(): void
    {
        if (!$this->parentRecord || !$this->relationshipName) {
            return;
        }

        $parentName = $this->getParentDisplayName();
        $relationLabel = $this->getRelationshipLabel();
        
        $this->relationTitle = "{$relationLabel} de {$parentName}";
        $this->relationDescription = "Gerencie os {$relationLabel} relacionados a {$parentName}";
    }

    /**
     * Obter nome de exibição do modelo pai
     */
    protected function getParentDisplayName(): string
    {
        if (!$this->parentRecord) {
            return 'Registro';
        }

        foreach ($this->parentDisplayFields as $field) {
            if (isset($this->parentRecord->{$field})) {
                return $this->parentRecord->{$field};
            }
        }

        return "#{$this->parentRecord->getKey()}";
    }

    /**
     * Obter label do relacionamento
     */
    protected function getRelationshipLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->relationshipName ?? 'registros'));
    }

    /**
     * Configurar query para o relacionamento
     */
    protected function setupRelationQuery(): void
    {
        if (!$this->parentRecord || !$this->relationshipName) {
            return;
        }

        $this->query(function () {
            return $this->parentRecord->{$this->relationshipName}();
        });
    }

    /**
     * Definir título personalizado
     */
    public function relationTitle(string $title): static
    {
        $this->relationTitle = $title;
        return $this;
    }

    /**
     * Definir descrição personalizada
     */
    public function relationDescription(string $description): static
    {
        $this->relationDescription = $description;
        return $this;
    }

    /**
     * Permitir/impedir criação de registros relacionados
     */
    public function canCreate(bool $canCreate = true): static
    {
        $this->canCreateRelated = $canCreate;
        return $this;
    }

    /**
     * Permitir/impedir anexar registros existentes
     */
    public function canAttach(bool $canAttach = true): static
    {
        $this->canAttachExisting = $canAttach;
        return $this;
    }

    /**
     * Permitir/impedir desanexar registros
     */
    public function canDetach(bool $canDetach = true): static
    {
        $this->canDetachRecords = $canDetach;
        return $this;
    }

    /**
     * Definir campos de exibição do modelo pai
     */
    public function parentDisplayFields(array $fields): static
    {
        $this->parentDisplayFields = $fields;
        return $this;
    }

    /**
     * Verificar se está em modo RelationManager
     */
    public function isRelationManager(): bool
    {
        return $this->isRelationManager;
    }

    /**
     * Obter modelo pai
     */
    public function getParentRecord(): ?Model
    {
        return $this->parentRecord;
    }

    /**
     * Obter nome do relacionamento
     */
    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    /**
     * Obter tipo do relacionamento
     */
    public function getRelationshipType(): ?string
    {
        return $this->relationshipType;
    }

    /**
     * Obter título contextual
     */
    public function getRelationTitle(): ?string
    {
        return $this->relationTitle;
    }

    /**
     * Obter descrição contextual
     */
    public function getRelationDescription(): ?string
    {
        return $this->relationDescription;
    }

    /**
     * Verificar se pode criar registros relacionados
     */
    public function canCreateRelated(): bool
    {
        return $this->canCreateRelated;
    }

    /**
     * Verificar se pode anexar registros existentes
     */
    public function canAttachExisting(): bool
    {
        return $this->canAttachExisting;
    }

    /**
     * Verificar se pode desanexar registros
     */
    public function canDetachRecords(): bool
    {
        return $this->canDetachRecords;
    }

    /**
     * Obter contexto do relacionamento para o frontend React
     */
    public function getRelationContext(): array
    {
        if (!$this->isRelationManager) {
            return [];
        }

        return [
            'isRelationManager' => true,
            'parentRecord' => [
                'id' => $this->parentRecord?->getKey(),
                'model' => $this->parentRecord ? get_class($this->parentRecord) : null,
                'displayName' => $this->getParentDisplayName(),
                'data' => $this->getParentDataForFrontend(),
            ],
            'relationship' => [
                'name' => $this->relationshipName,
                'type' => $this->relationshipType,
                'title' => $this->relationTitle,
                'description' => $this->relationDescription,
                'label' => $this->getRelationshipLabel(),
            ],
            'permissions' => [
                'canCreate' => $this->canCreateRelated,
                'canAttach' => $this->canAttachExisting,
                'canDetach' => $this->canDetachRecords,
            ],
            'ui' => [
                'breadcrumbs' => $this->getBreadcrumbsForFrontend(),
                'emptyState' => $this->getEmptyStateForFrontend(),
                'header' => $this->getHeaderConfigForFrontend(),
            ],
            'frontend' => [
                'component' => 'RelationManager',
                'parentComponent' => 'RelationManagerHeader',
                'emptyComponent' => 'RelationEmptyState',
            ],
        ];
    }

    /**
     * Obter dados do parent para React
     */
    protected function getParentDataForFrontend(): array
    {
        if (!$this->parentRecord) {
            return [];
        }

        $data = [];
        foreach ($this->parentDisplayFields as $field) {
            if (isset($this->parentRecord->{$field})) {
                $data[$field] = $this->parentRecord->{$field};
            }
        }

        return $data;
    }

    /**
     * Obter breadcrumbs para React
     */
    protected function getBreadcrumbsForFrontend(): array
    {
        if (!$this->parentRecord || !$this->relationshipName) {
            return [];
        }

        $parentName = $this->getParentDisplayName();
        $relationLabel = $this->getRelationshipLabel();

        return [
            [
                'label' => class_basename($this->parentRecord),
                'href' => null,
                'icon' => 'Folder',
            ],
            [
                'label' => $parentName,
                'href' => null,
                'icon' => 'File',
            ],
            [
                'label' => $relationLabel,
                'href' => null,
                'icon' => 'Link',
                'current' => true,
            ],
        ];
    }

    /**
     * Obter estado vazio para React
     */
    protected function getEmptyStateForFrontend(): array
    {
        $relationLabel = $this->getRelationshipLabel();
        $parentName = $this->getParentDisplayName();

        $emptyStates = [
            'hasMany' => [
                'title' => "Nenhum {$relationLabel} encontrado",
                'description' => "Ainda não há {$relationLabel} relacionados a {$parentName}",
                'icon' => 'Database',
                'action' => $this->canCreateRelated ? 'Criar primeiro registro' : null,
            ],
            'belongsToMany' => [
                'title' => 'Nenhum relacionamento encontrado',
                'description' => "Ainda não há relacionamentos configurados para {$parentName}",
                'icon' => 'Link',
                'action' => $this->canAttachExisting ? 'Anexar registros existentes' : ($this->canCreateRelated ? 'Criar novo registro' : null),
            ],
            'morphMany' => [
                'title' => "Nenhum {$relationLabel} encontrado",
                'description' => "Ainda não há {$relationLabel} relacionados a {$parentName}",
                'icon' => 'Layers',
                'action' => $this->canCreateRelated ? 'Criar primeiro registro' : null,
            ],
        ];

        return $emptyStates[$this->relationshipType] ?? [
            'title' => "Nenhum {$relationLabel} encontrado",
            'description' => "Não há registros para exibir",
            'icon' => 'Database',
            'action' => null,
        ];
    }

    /**
     * Obter configuração do header para React
     */
    protected function getHeaderConfigForFrontend(): array
    {
        return [
            'title' => $this->relationTitle,
            'description' => $this->relationDescription,
            'showParentInfo' => true,
            'showRelationshipType' => true,
            'showRecordCount' => true,
            'parentInfo' => [
                'name' => $this->getParentDisplayName(),
                'model' => $this->parentRecord ? class_basename($this->parentRecord) : null,
                'id' => $this->parentRecord?->getKey(),
            ],
        ];
    }

    /**
     * Métodos de conveniência para relacionamentos comuns
     */

    /**
     * RelationManager para Posts de uma Categoria
     */
    public static function postsForCategory(Model $category): static
    {
        return static::make()
            ->forRelation($category, 'posts')
            ->relationTitle("Posts da Categoria: {$category->name}")
            ->relationDescription("Gerencie os posts desta categoria");
    }

    /**
     * RelationManager para Pedidos de um Cliente
     */
    public static function ordersForCustomer(Model $customer): static
    {
        return static::make()
            ->forRelation($customer, 'orders')
            ->relationTitle("Pedidos do Cliente: {$customer->name}")
            ->relationDescription("Histórico de pedidos do cliente");
    }

    /**
     * RelationManager para Produtos de uma Categoria
     */
    public static function productsForCategory(Model $category): static
    {
        return static::make()
            ->forRelation($category, 'products')
            ->relationTitle("Produtos da Categoria: {$category->name}")
            ->relationDescription("Produtos pertencentes a esta categoria");
    }

    /**
     * RelationManager para Usuários de uma Role
     */
    public static function usersForRole(Model $role): static
    {
        return static::make()
            ->forRelation($role, 'users')
            ->relationTitle("Usuários com Role: {$role->name}")
            ->relationDescription("Usuários que possuem esta permissão")
            ->canCreate(false); // Normalmente não se cria usuários diretamente em roles
    }

    /**
     * RelationManager para Comentários de um Post
     */
    public static function commentsForPost(Model $post): static
    {
        return static::make()
            ->forRelation($post, 'comments')
            ->relationTitle("Comentários do Post: {$post->title}")
            ->relationDescription("Comentários deixados neste post")
            ->canAttach(false); // Comentários normalmente são criados, não anexados
    }

    /**
     * RelationManager genérico
     */
    public static function forGenericRelation(Model $parent, string $relation, string $title = null): static
    {
        $instance = static::make()->forRelation($parent, $relation);
        
        if ($title) {
            $instance->relationTitle($title);
        }
        
        return $instance;
    }
} 