<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Tabs;

class UserTab extends Tab
{
    protected ?int $userId = null;
    protected string $userField = 'user_id';

    public function __construct(string $id, string $label, ?int $userId = null)
    {
        parent::__construct($id, $label);
        $this->userId = $userId;
        $this->icon('user')
             ->secondary()
             ->content(['type' => 'table'])
             ->tableConfig([
                 'searchable' => true,
                 'sortable' => true,
                 'paginated' => true,
             ]);
    }

    /**
     * Define o ID do usuário para filtrar
     */
    public function userId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Define o campo de usuário para filtrar
     */
    public function userField(string $field): self
    {
        $this->userField = $field;
        return $this;
    }

    /**
     * Atalhos para tabs de usuário comuns
     */
    public static function my(string $label = 'Meus Itens'): self
    {
        return (new self('meus_itens', $label))
            ->icon('user')
            ->secondary()
            ->order(100);
    }

    public static function assigned(string $label = 'Atribuídos a Mim'): self
    {
        return (new self('atribuidos', $label))
            ->icon('user-check')
            ->secondary()
            ->userField('assigned_to')
            ->order(101);
    }

    public static function created(string $label = 'Criados por Mim'): self
    {
        return (new self('criados', $label))
            ->icon('user-plus')
            ->secondary()
            ->userField('created_by')
            ->order(102);
    }

    /**
     * Obtém o ID do usuário
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Obtém o campo de usuário
     */
    public function getUserField(): string
    {
        return $this->userField;
    }

    /**
     * Serializa para array incluindo configurações de usuário
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'userId' => $this->getUserId(),
            'userField' => $this->getUserField(),
        ]);
    }
} 