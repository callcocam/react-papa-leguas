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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Closure;

class JsonSource extends DataSource
{
    protected string $source;
    protected bool $isUrl = false;
    protected bool $isStoragePath = false;
    protected ?Closure $dataTransformer = null;
    protected ?string $dataKey = null;

    public function __construct(string $source, array $config = [])
    {
        $this->source = $source;
        $this->detectSourceType();
        parent::__construct($config);
    }

    /**
     * Definir chave onde estão os dados no JSON
     */
    public function dataKey(string $key): static
    {
        $this->dataKey = $key;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir transformador de dados
     */
    public function transform(Closure $callback): static
    {
        $this->dataTransformer = $callback;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir como arquivo do Storage
     */
    public function asStorage(string $disk = 'local'): static
    {
        $this->isStoragePath = true;
        $this->isUrl = false;
        $this->config['storage_disk'] = $disk;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir como URL
     */
    public function asUrl(array $headers = []): static
    {
        $this->isUrl = true;
        $this->isStoragePath = false;
        $this->config['headers'] = array_merge($this->config['headers'] ?? [], $headers);
        $this->clearCache();
        return $this;
    }

    /**
     * Obter dados da fonte
     */
    public function getData(): Collection
    {
        return $this->getCachedData('getProcessedData');
    }

    /**
     * Obter dados paginados
     */
    public function getPaginatedData(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $data = $this->getProcessedData();
        return $this->paginateCollection($data, $page, $perPage);
    }

    /**
     * Contar total de registros
     */
    public function count(): int
    {
        return $this->getProcessedData()->count();
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
        return true;
    }

    /**
     * Verificar se a fonte suporta ordenação
     */
    public function supportsSorting(): bool
    {
        return true;
    }

    /**
     * Verificar se a fonte suporta filtros
     */
    public function supportsFilters(): bool
    {
        return true;
    }

    /**
     * Obter tipo da fonte de dados
     */
    public function getType(): string
    {
        return 'json';
    }

    /**
     * Verificar se a fonte está disponível
     */
    public function isAvailable(): bool
    {
        try {
            if ($this->isUrl) {
                $response = Http::timeout($this->config['timeout'] ?? 5)
                    ->withHeaders($this->config['headers'] ?? [])
                    ->head($this->source);
                return $response->successful();
            }

            if ($this->isStoragePath) {
                $disk = Storage::disk($this->config['storage_disk'] ?? 'local');
                return $disk->exists($this->source);
            }

            // Arquivo local
            return file_exists($this->source) && is_readable($this->source);
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
            'cache_ttl' => 600, // 10 minutos para JSON
            'timeout' => 30, // Para URLs
            'retry_times' => 3,
            'retry_delay' => 1000, // ms
            'headers' => [],
            'storage_disk' => 'local',
            'encoding' => 'UTF-8',
            'validate_json' => true,
        ]);
    }

    /**
     * Detectar tipo da fonte (URL, storage, arquivo local)
     */
    protected function detectSourceType(): void
    {
        if (filter_var($this->source, FILTER_VALIDATE_URL)) {
            $this->isUrl = true;
            $this->isStoragePath = false;
        } elseif (str_starts_with($this->source, 'storage/') || !str_contains($this->source, '/')) {
            $this->isStoragePath = true;
            $this->isUrl = false;
        } else {
            $this->isUrl = false;
            $this->isStoragePath = false;
        }
    }

    /**
     * Obter dados brutos do JSON
     */
    protected function getRawJsonData(): array
    {
        if ($this->isUrl) {
            return $this->getJsonFromUrl();
        }

        if ($this->isStoragePath) {
            return $this->getJsonFromStorage();
        }

        return $this->getJsonFromFile();
    }

    /**
     * Obter JSON de URL
     */
    protected function getJsonFromUrl(): array
    {
        $response = Http::timeout($this->config['timeout'])
            ->retry($this->config['retry_times'], $this->config['retry_delay'])
            ->withHeaders($this->config['headers'])
            ->get($this->source);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Erro ao buscar JSON da URL: {$response->status()} - {$response->body()}"
            );
        }

        $data = $response->json();

        if ($this->config['validate_json'] && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON inválido recebido da URL: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Obter JSON do Storage
     */
    protected function getJsonFromStorage(): array
    {
        $disk = Storage::disk($this->config['storage_disk']);

        if (!$disk->exists($this->source)) {
            throw new \RuntimeException("Arquivo JSON não encontrado no storage: {$this->source}");
        }

        $content = $disk->get($this->source);
        $data = json_decode($content, true);

        if ($this->config['validate_json'] && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON inválido no arquivo: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Obter JSON de arquivo local
     */
    protected function getJsonFromFile(): array
    {
        if (!file_exists($this->source)) {
            throw new \RuntimeException("Arquivo JSON não encontrado: {$this->source}");
        }

        if (!is_readable($this->source)) {
            throw new \RuntimeException("Arquivo JSON não pode ser lido: {$this->source}");
        }

        $content = file_get_contents($this->source);

        if ($content === false) {
            throw new \RuntimeException("Erro ao ler arquivo JSON: {$this->source}");
        }

        $data = json_decode($content, true);

        if ($this->config['validate_json'] && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON inválido no arquivo: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Obter dados processados (com filtros, busca e ordenação)
     */
    protected function getProcessedData(): Collection
    {
        $rawData = $this->getRawJsonData();

        // Extrair dados da chave especificada se configurada
        if ($this->dataKey) {
            $items = data_get($rawData, $this->dataKey);
            if (!is_array($items)) {
                throw new \RuntimeException("Chave '{$this->dataKey}' não contém array de dados");
            }
        } else {
            $items = is_array($rawData) && isset($rawData[0]) ? $rawData : [$rawData];
        }

        $collection = collect($items);

        // Aplicar transformador de dados se definido
        if ($this->dataTransformer) {
            $collection = $this->evaluate($this->dataTransformer, [
                'data' => $collection,
                'raw' => $rawData,
            ]);

            if (!$collection instanceof Collection) {
                $collection = collect($collection);
            }
        }

        // Aplicar filtros
        $collection = $this->applyFiltersToCollection($collection);

        // Aplicar busca
        $collection = $this->applySearchToCollection($collection);

        // Aplicar ordenação
        $collection = $this->applySortingToCollection($collection);

        return $collection;
    }

    /**
     * Recarregar dados do JSON
     */
    public function reload(): static
    {
        $this->clearCache();
        return $this;
    }

    /**
     * Salvar dados no JSON (apenas para arquivos locais e storage)
     */
    public function save(Collection $data): static
    {
        if ($this->isUrl) {
            throw new \RuntimeException('Não é possível salvar dados em URLs');
        }

        $jsonData = $data->toArray();

        // Se há chave de dados, encapsular
        if ($this->dataKey) {
            $jsonData = [$this->dataKey => $jsonData];
        }

        $content = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($this->isStoragePath) {
            $disk = Storage::disk($this->config['storage_disk']);
            $disk->put($this->source, $content);
        } else {
            file_put_contents($this->source, $content);
        }

        $this->clearCache();
        return $this;
    }

    /**
     * Obter informações de debug específicas do JSON
     */
    public function getDebugInfo(): array
    {
        $baseInfo = parent::getDebugInfo();

        return array_merge($baseInfo, [
            'source' => $this->source,
            'source_type' => $this->isUrl ? 'url' : ($this->isStoragePath ? 'storage' : 'file'),
            'data_key' => $this->dataKey,
            'has_transformer' => $this->dataTransformer !== null,
            'encoding' => $this->config['encoding'] ?? 'UTF-8',
            'validate_json' => $this->config['validate_json'] ?? true,
            'file_size' => $this->getSourceSize(),
            'last_modified' => $this->getLastModified(),
        ]);
    }

    /**
     * Obter tamanho da fonte
     */
    protected function getSourceSize(): ?int
    {
        try {
            if ($this->isUrl) {
                return null; // Não aplicável para URLs
            }

            if ($this->isStoragePath) {
                $disk = Storage::disk($this->config['storage_disk']);
                return $disk->size($this->source);
            }

            return filesize($this->source);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obter data de última modificação
     */
    protected function getLastModified(): ?string
    {
        try {
            if ($this->isUrl) {
                return null; // Não aplicável para URLs
            }

            if ($this->isStoragePath) {
                $disk = Storage::disk($this->config['storage_disk']);
                return date('Y-m-d H:i:s', $disk->lastModified($this->source));
            }

            return date('Y-m-d H:i:s', filemtime($this->source));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Métodos estáticos para criação rápida
     */
    public static function fromFile(string $path, array $config = []): static
    {
        return new static($path, $config);
    }

    public static function fromUrl(string $url, array $config = []): static
    {
        return (new static($url, $config))->asUrl();
    }

    public static function fromStorage(string $path, string $disk = 'local', array $config = []): static
    {
        return (new static($path, $config))->asStorage($disk);
    }
} 