<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\DataSources\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DataSourceInterface
{
    /**
     * Obter dados da fonte
     */
    public function getData(): Collection;

    /**
     * Obter dados paginados
     */
    public function getPaginatedData(int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Aplicar filtros aos dados
     */
    public function applyFilters(array $filters): static;

    /**
     * Aplicar busca aos dados
     */
    public function applySearch(string $search, array $searchableColumns = []): static;

    /**
     * Aplicar ordenação aos dados
     */
    public function applySorting(string $column, string $direction = 'asc'): static;

    /**
     * Contar total de registros
     */
    public function count(): int;

    /**
     * Verificar se a fonte suporta paginação
     */
    public function supportsPagination(): bool;

    /**
     * Verificar se a fonte suporta busca
     */
    public function supportsSearch(): bool;

    /**
     * Verificar se a fonte suporta ordenação
     */
    public function supportsSorting(): bool;

    /**
     * Verificar se a fonte suporta filtros
     */
    public function supportsFilters(): bool;

    /**
     * Obter tipo da fonte de dados
     */
    public function getType(): string;

    /**
     * Obter configurações da fonte
     */
    public function getConfig(): array;

    /**
     * Verificar se a fonte está disponível
     */
    public function isAvailable(): bool;

    /**
     * Limpar cache da fonte (se aplicável)
     */
    public function clearCache(): static;

    /**
     * Obter informações de debug da fonte
     */
    public function getDebugInfo(): array;
} 