<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Examples;

use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\RelationColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\HeaderAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RowAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RelationAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RelationBulkAction;

/**
 * Exemplo completo de RelationManager para React Frontend
 */
class RelationManagerExample
{
    /**
     * Tabela de Posts para uma Categoria (HasMany)
     */
    public static function postsForCategory($category): Table
    {
        return Table::make()
            // Configurar como RelationManager
            ->asRelationManager($category, 'posts')
            ->relationTitle('Posts da Categoria')
            ->relationDescription('Gerencie os posts desta categoria')
            ->canCreateRelated()
            ->canDetachRecords()
            
            // Colunas com contexto de relacionamento
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->copyable()
                    ->limit(50),
                
                RelationColumn::relationData('author', 'name')
                    ->label('Autor')
                    ->showRelationBadge()
                    ->linkToRelated(),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'draft' => 'secondary',
                        'published' => 'success',
                        'archived' => 'warning',
                    ])
                    ->icons([
                        'draft' => 'Edit',
                        'published' => 'CheckCircle',
                        'archived' => 'Archive',
                    ]),
                
                RelationColumn::relationCount('comments')
                    ->label('Comentários'),
                
                DateColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateFormat('d/m/Y H:i')
                    ->since()
                    ->tooltip(),
            ])
            
            // Actions de cabeçalho específicas para relacionamento
            ->headerActions([
                RelationAction::createRelated('posts.create')
                    ->label('Criar Post')
                    ->icon('Plus')
                    ->color('primary')
                    ->modal([
                        'title' => 'Criar Novo Post',
                        'size' => 'xl',
                    ]),
                
                RelationAction::attachExisting('posts.attach')
                    ->label('Anexar Posts Existentes')
                    ->icon('Link')
                    ->color('secondary')
                    ->modal([
                        'title' => 'Anexar Posts à Categoria',
                        'size' => 'lg',
                        'searchable' => true,
                        'selectable' => 'multiple',
                    ]),
            ])
            
            // Actions de linha específicas para relacionamento
            ->rowActions([
                RelationAction::viewRelated('posts.show')
                    ->label('Ver Post')
                    ->icon('Eye')
                    ->color('secondary'),
                
                RelationAction::editRelated('posts.edit')
                    ->label('Editar Post')
                    ->icon('Edit')
                    ->color('primary'),
                
                RelationAction::detachRecord('posts.detach')
                    ->label('Remover da Categoria')
                    ->icon('Unlink')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->confirmationTitle('Remover Post da Categoria')
                    ->confirmationMessage('O post não será excluído, apenas removido desta categoria.'),
            ])
            
            // Bulk Actions específicas para relacionamento
            ->withHasManyBulkActions('posts')
            ->relationBulkActions([
                RelationBulkAction::moveSelectedToRelation('posts.bulk-move-category')
                    ->label('Mover para Outra Categoria'),
                
                RelationBulkAction::duplicateSelectedRelations('posts.bulk-duplicate')
                    ->label('Duplicar Posts'),
            ])
            
            // Configurações de interface React
            ->emptyState(
                'Nenhum post encontrado',
                'Esta categoria ainda não possui posts. Crie o primeiro post ou anexe posts existentes.',
                'FileText'
            )
            ->notifications('top-right', 4000, true);
    }

    /**
     * Tabela de Tags para um Post (BelongsToMany com Pivot)
     */
    public static function tagsForPost($post): Table
    {
        return Table::make()
            // Configurar como RelationManager
            ->asRelationManager($post, 'tags')
            ->relationTitle('Tags do Post')
            ->relationDescription('Gerencie as tags associadas a este post')
            ->canAttachExisting()
            ->canDetachRecords()
            
            // Colunas incluindo dados do pivot
            ->columns([
                TextColumn::make('name')
                    ->label('Nome da Tag')
                    ->searchable()
                    ->copyable(),
                
                BadgeColumn::make('color')
                    ->label('Cor')
                    ->colors([
                        'blue' => 'primary',
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'secondary',
                    ]),
                
                RelationColumn::pivotField('importance')
                    ->label('Importância')
                    ->showPivotData(),
                
                RelationColumn::relationTimestamp('tags', 'created_at')
                    ->label('Anexado em'),
                
                RelationColumn::relationStatus('tags', 'status')
                    ->label('Status da Relação'),
            ])
            
            // Actions específicas para BelongsToMany
            ->headerActions([
                RelationAction::attachExisting('tags.attach')
                    ->label('Anexar Tags')
                    ->icon('Tag')
                    ->color('primary')
                    ->modal([
                        'title' => 'Anexar Tags ao Post',
                        'size' => 'lg',
                        'searchable' => true,
                        'selectable' => 'multiple',
                        'showPivotFields' => true,
                        'pivotFields' => [
                            'importance' => ['type' => 'select', 'options' => ['low', 'medium', 'high']],
                            'status' => ['type' => 'select', 'options' => ['active', 'inactive']],
                        ],
                    ]),
            ])
            
            ->rowActions([
                RelationAction::editPivot('tags.edit-pivot')
                    ->label('Editar Relação')
                    ->icon('Settings')
                    ->color('secondary')
                    ->modal([
                        'title' => 'Editar Dados da Relação',
                        'hasInput' => true,
                        'inputFields' => ['importance', 'status'],
                    ]),
                
                RelationAction::detachRecord('tags.detach')
                    ->label('Desanexar Tag')
                    ->icon('Unlink')
                    ->color('warning'),
            ])
            
            // Bulk Actions para BelongsToMany
            ->withBelongsToManyBulkActions('tags')
            ->withPivotFieldBulkActions(['importance', 'status'], 'tags')
            ->relationBulkActions([
                RelationBulkAction::syncSelected('tags.bulk-sync')
                    ->label('Sincronizar Tags Selecionadas'),
            ]);
    }

    /**
     * Tabela de Comentários para um Post (MorphMany)
     */
    public static function commentsForPost($post): Table
    {
        return Table::make()
            // Configurar como RelationManager
            ->asRelationManager($post, 'comments')
            ->relationTitle('Comentários do Post')
            ->relationDescription('Gerencie os comentários deste post')
            ->canCreateRelated()
            
            // Colunas específicas para comentários
            ->columns([
                RelationColumn::relationData('user', 'name')
                    ->label('Usuário')
                    ->showRelationBadge()
                    ->linkToRelated(),
                
                TextColumn::make('content')
                    ->label('Comentário')
                    ->limit(100)
                    ->tooltip()
                    ->wrap(),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'spam' => 'secondary',
                    ])
                    ->icons([
                        'pending' => 'Clock',
                        'approved' => 'CheckCircle',
                        'rejected' => 'XCircle',
                        'spam' => 'Shield',
                    ]),
                
                DateColumn::make('created_at')
                    ->label('Data do Comentário')
                    ->since()
                    ->tooltip(),
                
                RelationColumn::relationActions('comments')
                    ->label('Ações'),
            ])
            
            // Actions específicas para comentários
            ->headerActions([
                RelationAction::createRelated('comments.create')
                    ->label('Adicionar Comentário')
                    ->icon('MessageSquare')
                    ->color('primary'),
            ])
            
            ->rowActions([
                RelationAction::approveRecord('comments.approve')
                    ->label('Aprovar')
                    ->icon('CheckCircle')
                    ->color('success')
                    ->visibleWhen(fn($record) => $record->status === 'pending'),
                
                RelationAction::rejectRecord('comments.reject')
                    ->label('Rejeitar')
                    ->icon('XCircle')
                    ->color('danger')
                    ->visibleWhen(fn($record) => $record->status === 'pending'),
                
                RelationAction::markAsSpam('comments.spam')
                    ->label('Marcar como Spam')
                    ->icon('Shield')
                    ->color('warning'),
            ])
            
            // Bulk Actions para comentários
            ->withMorphManyBulkActions('comments')
            ->relationBulkActions([
                RelationBulkAction::make('bulk-approve')
                    ->label('Aprovar Selecionados')
                    ->icon('CheckCircle')
                    ->color('success')
                    ->route('comments.bulk-approve')
                    ->method('PUT')
                    ->toast('Comentários aprovados com sucesso!'),
                
                RelationBulkAction::make('bulk-reject')
                    ->label('Rejeitar Selecionados')
                    ->icon('XCircle')
                    ->color('danger')
                    ->route('comments.bulk-reject')
                    ->method('PUT')
                    ->requiresConfirmation(),
            ]);
    }

    /**
     * Exemplo de dados que serão enviados para React
     */
    public static function exampleReactData()
    {
        return [
            'table' => [
                'columns' => [
                    [
                        'id' => 'title',
                        'label' => 'Título',
                        'type' => 'text',
                        'searchable' => true,
                        'copyable' => true,
                        'frontend' => [
                            'component' => 'TextColumn',
                        ],
                    ],
                    [
                        'id' => 'author.name',
                        'label' => 'Autor',
                        'type' => 'relation',
                        'relationType' => 'data',
                        'relationshipName' => 'author',
                        'frontend' => [
                            'component' => 'RelationColumn',
                            'config' => [
                                'showRelationBadge' => true,
                                'linkToRelated' => true,
                            ],
                        ],
                    ],
                ],
                'headerActions' => [
                    [
                        'id' => 'create-post',
                        'label' => 'Criar Post',
                        'icon' => 'Plus',
                        'color' => 'primary',
                        'relationType' => 'create',
                        'frontend' => [
                            'component' => 'RelationAction',
                            'modal' => [
                                'title' => 'Criar Novo Post',
                                'size' => 'xl',
                            ],
                        ],
                    ],
                ],
                'relationBulkActions' => [
                    'enabled' => true,
                    'actions' => [
                        [
                            'id' => 'bulk-detach',
                            'label' => 'Desanexar Selecionados',
                            'relationType' => 'detach',
                            'frontend' => [
                                'component' => 'RelationBulkAction',
                                'modal' => [
                                    'title' => 'Confirmar Desanexação em Massa',
                                ],
                            ],
                        ],
                    ],
                    'frontend' => [
                        'component' => 'RelationBulkActionBar',
                    ],
                ],
                'relationContext' => [
                    'isRelationManager' => true,
                    'parentRecord' => [
                        'id' => 1,
                        'model' => 'App\\Models\\Category',
                        'displayName' => 'Tecnologia',
                    ],
                    'relationship' => [
                        'name' => 'posts',
                        'type' => 'hasMany',
                        'title' => 'Posts da Categoria',
                        'label' => 'Posts',
                    ],
                    'ui' => [
                        'breadcrumbs' => [
                            ['label' => 'Category', 'icon' => 'Folder'],
                            ['label' => 'Tecnologia', 'icon' => 'File'],
                            ['label' => 'Posts', 'icon' => 'Link', 'current' => true],
                        ],
                        'emptyState' => [
                            'title' => 'Nenhum post encontrado',
                            'description' => 'Ainda não há posts relacionados a Tecnologia',
                            'icon' => 'Database',
                            'action' => 'Criar primeiro registro',
                        ],
                    ],
                    'frontend' => [
                        'component' => 'RelationManager',
                    ],
                ],
            ],
        ];
    }
} 