<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\DataSources;

use Callcocam\ReactPapaLeguas\Support\Table\DataSources\DataSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Closure;

class ApiSource extends DataSource
{
    protected string $baseUrl;
    protected array $headers = [];
    protected array $queryParams = [];
    protected ?Closure $responseTransformer = null;
    protected ?Closure $requestTransformer = null;

    public function __construct(string $baseUrl, array $config = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        parent::__construct($config);
    }

    /**
     * Definir headers para as requisições
     */
    public function headers(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->clearCache();
        return $this;
    }

    /**
     * Definir parâmetros de query padrão
     */
    public function queryParams(array $params): static
    {
        $this->queryParams = array_merge($this->queryParams, $params);
        $this->clearCache();
        return $this;
    }

    /**
     * Definir transformador de resposta
     */
    public function transformResponse(Closure $callback): static
    {
        $this->responseTransformer = $callback;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir transformador de requisição
     */
    public function transformRequest(Closure $callback): static
    {
        $this->requestTransformer = $callback;
        $this->clearCache();
        return $this;
    }

    /**
     * Obter dados da fonte
     */
    public function getData(): Collection
    {
        return $this->getCachedData('fetchDataFromApi');
    }

    /**
     * Obter dados paginados
     */
    public function getPaginatedData(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        // Para APIs, tentamos usar paginação nativa se suportada
        if ($this->config['supports_native_pagination'] ?? false) {
            return $this->fetchPaginatedDataFromApi($page, $perPage);
        }

        // Caso contrário, paginamos os dados em memória
        $data = $this->getData();
        return $this->paginateCollection($data, $page, $perPage);
    }

    /**
     * Contar total de registros
     */
    public function count(): int
    {
        // Se a API suporta contagem nativa, usar endpoint específico
        if ($this->config['count_endpoint']) {
            return $this->fetchCountFromApi();
        }

        return $this->getData()->count();
    }

    /**
     * Verificar se a fonte suporta paginação
     */
    public function supportsPagination(): bool
    {
        return true;
    }

    /**
     * Verificar se a fonte suporta busca
     */
    public function supportsSearch(): bool
    {
        return $this->config['supports_search'] ?? false;
    }

    /**
     * Verificar se a fonte suporta ordenação
     */
    public function supportsSorting(): bool
    {
        return $this->config['supports_sorting'] ?? false;
    }

    /**
     * Verificar se a fonte suporta filtros
     */
    public function supportsFilters(): bool
    {
        return $this->config['supports_filters'] ?? false;
    }

    /**
     * Obter tipo da fonte de dados
     */
    public function getType(): string
    {
        return 'api';
    }

    /**
     * Verificar se a fonte está disponível
     */
    public function isAvailable(): bool
    {
        try {
            $healthEndpoint = $this->config['health_endpoint'] ?? '/health';
            $response = Http::timeout($this->config['timeout'] ?? 5)
                ->withHeaders($this->headers)
                ->get($this->baseUrl . $healthEndpoint);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter configuração padrão da fonte
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'cache_enabled' => true,
            'cache_ttl' => 300, // 5 minutos para APIs
            'timeout' => 30,
            'retry_times' => 3,
            'retry_delay' => 1000, // ms
            'data_key' => 'data', // Chave onde estão os dados na resposta
            'meta_key' => 'meta', // Chave onde estão os metadados
            'supports_native_pagination' => false,
            'supports_search' => false,
            'supports_sorting' => false,
            'supports_filters' => false,
            'pagination_params' => [
                'page' => 'page',
                'per_page' => 'per_page',
            ],
            'search_param' => 'search',
            'sort_param' => 'sort',
            'filter_prefix' => 'filter_',
            'health_endpoint' => '/health',
            'count_endpoint' => null,
        ]);
    }

    /**
     * Buscar dados da API
     */
    protected function fetchDataFromApi(): Collection
    {
        $params = $this->buildRequestParams();
        
        $response = $this->makeRequest('GET', '', $params);
        
        return $this->transformApiResponse($response);
    }

    /**
     * Buscar dados paginados da API
     */
    protected function fetchPaginatedDataFromApi(int $page, int $perPage): LengthAwarePaginator
    {
        $params = $this->buildRequestParams();
        $params[$this->config['pagination_params']['page']] = $page;
        $params[$this->config['pagination_params']['per_page']] = $perPage;
        
        $response = $this->makeRequest('GET', '', $params);
        $data = $this->transformApiResponse($response);
        
        // Extrair informações de paginação da resposta
        $responseData = $response->json();
        $meta = $responseData[$this->config['meta_key']] ?? [];
        
        $total = $meta['total'] ?? $data->count();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $data,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Buscar contagem da API
     */
    protected function fetchCountFromApi(): int
    {
        $response = $this->makeRequest('GET', $this->config['count_endpoint']);
        $data = $response->json();
        
        return $data['count'] ?? $data['total'] ?? 0;
    }

    /**
     * Construir parâmetros da requisição
     */
    protected function buildRequestParams(): array
    {
        $params = $this->queryParams;
        
        // Adicionar busca se suportada
        if ($this->search && $this->supportsSearch()) {
            $params[$this->config['search_param']] = $this->search;
        }
        
        // Adicionar ordenação se suportada
        if ($this->sortColumn && $this->supportsSorting()) {
            $params[$this->config['sort_param']] = $this->sortColumn . ',' . $this->sortDirection;
        }
        
        // Adicionar filtros se suportados
        if (!empty($this->filters) && $this->supportsFilters()) {
            foreach ($this->filters as $column => $value) {
                $params[$this->config['filter_prefix'] . $column] = $value;
            }
        }
        
        // Aplicar transformador de requisição se definido
        if ($this->requestTransformer) {
            $params = $this->evaluate($this->requestTransformer, ['params' => $params]);
        }
        
        return $params;
    }

    /**
     * Fazer requisição HTTP
     */
    protected function makeRequest(string $method, string $endpoint = '', array $params = []): Response
    {
        $url = $this->baseUrl . $endpoint;
        
        $request = Http::timeout($this->config['timeout'])
            ->retry($this->config['retry_times'], $this->config['retry_delay'])
            ->withHeaders($this->headers);
        
        $response = match (strtoupper($method)) {
            'GET' => $request->get($url, $params),
            'POST' => $request->post($url, $params),
            'PUT' => $request->put($url, $params),
            'DELETE' => $request->delete($url, $params),
            default => throw new \InvalidArgumentException("Método HTTP {$method} não suportado"),
        };
        
        if (!$response->successful()) {
            throw new \RuntimeException(
                "Erro na requisição API: {$response->status()} - {$response->body()}"
            );
        }
        
        return $response;
    }

    /**
     * Transformar resposta da API
     */
    protected function transformApiResponse(Response $response): Collection
    {
        $data = $response->json();
        
        // Extrair dados usando a chave configurada
        $items = $data[$this->config['data_key']] ?? $data;
        
        if (!is_array($items)) {
            throw new \RuntimeException('Resposta da API não contém array de dados');
        }
        
        $collection = collect($items);
        
        // Aplicar transformador de resposta se definido
        if ($this->responseTransformer) {
            $collection = $this->evaluate($this->responseTransformer, [
                'data' => $collection,
                'response' => $response,
                'meta' => $data[$this->config['meta_key']] ?? [],
            ]);
        }
        
        return $collection;
    }

    /**
     * Configurar autenticação Bearer
     */
    public function bearerToken(string $token): static
    {
        return $this->headers(['Authorization' => 'Bearer ' . $token]);
    }

    /**
     * Configurar autenticação básica
     */
    public function basicAuth(string $username, string $password): static
    {
        return $this->headers(['Authorization' => 'Basic ' . base64_encode($username . ':' . $password)]);
    }

    /**
     * Obter informações de debug específicas da API
     */
    public function getDebugInfo(): array
    {
        $baseInfo = parent::getDebugInfo();
        
        return array_merge($baseInfo, [
            'base_url' => $this->baseUrl,
            'headers' => $this->headers,
            'query_params' => $this->queryParams,
            'has_response_transformer' => $this->responseTransformer !== null,
            'has_request_transformer' => $this->requestTransformer !== null,
            'api_supports' => [
                'native_pagination' => $this->config['supports_native_pagination'] ?? false,
                'search' => $this->config['supports_search'] ?? false,
                'sorting' => $this->config['supports_sorting'] ?? false,
                'filters' => $this->config['supports_filters'] ?? false,
            ],
        ]);
    }
} 