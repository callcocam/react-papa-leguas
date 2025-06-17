<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Contracts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Closure;

/**
 * Interface que define o contrato para classes Table
 */
interface TableInterface
{
    /**
     * Criar nova instância da tabela
     */
    public static function make(string $id = null): static;

    /**
     * Definir o modelo Eloquent
     */
    public function model(string $model): static;

    /**
     * Definir query personalizada
     */
    public function query(Closure $callback): static;

    /**
     * Obter ID da tabela
     */
    public function getId(): string;

    /**
     * Obter modelo
     */
    public function getModel(): ?string;

    /**
     * Obter query base
     */
    public function getBaseQuery(): Builder;

    /**
     * Processar dados da tabela para o frontend
     */
    public function getData(Request $request = null): array;

    /**
     * Renderizar tabela para Inertia
     */
    public function render(Request $request = null): array;

    /**
     * Converter para array
     */
    public function toArray(): array;
} 