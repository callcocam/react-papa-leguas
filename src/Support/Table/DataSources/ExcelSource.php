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
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Closure;

class ExcelSource extends DataSource
{
    protected string $source;
    protected bool $isUrl = false;
    protected bool $isStoragePath = false;
    protected ?Closure $dataTransformer = null;
    protected ?string $worksheet = null;
    protected ?array $headerMap = null;
    protected bool $hasHeaders = true;

    public function __construct(string $source, array $config = [])
    {
        $this->source = $source;
        $this->detectSourceType();
        parent::__construct($config);
    }

    /**
     * Definir planilha específica para ler
     */
    public function worksheet(string $name): static
    {
        $this->worksheet = $name;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir mapeamento de cabeçalhos
     */
    public function mapHeaders(array $map): static
    {
        $this->headerMap = $map;
        $this->clearCache();
        return $this;
    }

    /**
     * Definir se o arquivo tem cabeçalhos
     */
    public function hasHeaders(bool $hasHeaders = true): static
    {
        $this->hasHeaders = $hasHeaders;
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
     * Obter tipo da fonte de dados
     */
    public function getType(): string
    {
        return 'excel';
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
            'cache_ttl' => 1800, // 30 minutos para Excel
            'timeout' => 60, // Para URLs (arquivos podem ser grandes)
            'retry_times' => 2,
            'retry_delay' => 2000, // ms
            'headers' => [],
            'storage_disk' => 'local',
            'encoding' => 'UTF-8',
            'delimiter' => ',', // Para CSV
            'enclosure' => '"',
            'escape' => '\\',
            'skip_empty_rows' => true,
            'chunk_size' => 1000, // Para arquivos grandes
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
     * Obter dados brutos do Excel/CSV
     */
    protected function getRawExcelData(): Collection
    {
        if ($this->isUrl) {
            return $this->getExcelFromUrl();
        }

        if ($this->isStoragePath) {
            return $this->getExcelFromStorage();
        }

        return $this->getExcelFromFile();
    }

    /**
     * Obter Excel de URL
     */
    protected function getExcelFromUrl(): Collection
    {
        // Baixar arquivo temporariamente
        $response = Http::timeout($this->config['timeout'])
            ->retry($this->config['retry_times'], $this->config['retry_delay'])
            ->withHeaders($this->config['headers'])
            ->get($this->source);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Erro ao buscar Excel da URL: {$response->status()} - {$response->body()}"
            );
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_source_');
        file_put_contents($tempFile, $response->body());

        try {
            $data = $this->readExcelFile($tempFile);
        } finally {
            unlink($tempFile);
        }

        return $data;
    }

    /**
     * Obter Excel do Storage
     */
    protected function getExcelFromStorage(): Collection
    {
        $disk = Storage::disk($this->config['storage_disk']);

        if (!$disk->exists($this->source)) {
            throw new \RuntimeException("Arquivo Excel não encontrado no storage: {$this->source}");
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_source_');
        file_put_contents($tempFile, $disk->get($this->source));

        try {
            $data = $this->readExcelFile($tempFile);
        } finally {
            unlink($tempFile);
        }

        return $data;
    }

    /**
     * Obter Excel de arquivo local
     */
    protected function getExcelFromFile(): Collection
    {
        if (!file_exists($this->source)) {
            throw new \RuntimeException("Arquivo Excel não encontrado: {$this->source}");
        }

        if (!is_readable($this->source)) {
            throw new \RuntimeException("Arquivo Excel não pode ser lido: {$this->source}");
        }

        return $this->readExcelFile($this->source);
    }

    /**
     * Ler arquivo Excel/CSV
     */
    protected function readExcelFile(string $filePath): Collection
    {
        try {
            // Detectar tipo de arquivo
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if ($extension === 'csv') {
                return $this->readCsvFile($filePath);
            }

            // Para arquivos Excel (.xlsx, .xls)
            $data = Excel::toCollection(null, $filePath);

            // Se especificou planilha específica
            if ($this->worksheet) {
                $worksheetData = $data->firstWhere('title', $this->worksheet);
                if (!$worksheetData) {
                    throw new \RuntimeException("Planilha '{$this->worksheet}' não encontrada");
                }
                $rows = $worksheetData;
            } else {
                // Usar primeira planilha
                $rows = $data->first();
            }

            return $this->processRows($rows);
        } catch (\Exception $e) {
            throw new \RuntimeException("Erro ao ler arquivo Excel: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Ler arquivo CSV
     */
    protected function readCsvFile(string $filePath): Collection
    {
        $rows = collect();
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir arquivo CSV: {$filePath}");
        }

        try {
            while (($row = fgetcsv($handle, 0, $this->config['delimiter'], $this->config['enclosure'], $this->config['escape'])) !== false) {
                if ($this->config['skip_empty_rows'] && empty(array_filter($row))) {
                    continue;
                }
                $rows->push($row);
            }
        } finally {
            fclose($handle);
        }

        return $this->processRows($rows);
    }

    /**
     * Processar linhas do arquivo
     */
    protected function processRows(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $headers = null;
        $dataRows = $rows;

        // Se tem cabeçalhos, extrair primeira linha
        if ($this->hasHeaders) {
            $headers = $rows->first();
            $dataRows = $rows->skip(1);

            // Aplicar mapeamento de cabeçalhos se definido
            if ($this->headerMap) {
                $headers = collect($headers)->map(function ($header) {
                    return $this->headerMap[$header] ?? $header;
                });
            }
        }

        // Converter linhas em arrays associativos
        return $dataRows->map(function ($row, $index) use ($headers) {
            if ($headers) {
                // Combinar cabeçalhos com valores
                $data = collect($headers)->combine($row)->toArray();
            } else {
                // Usar índices numéricos se não há cabeçalhos
                $data = array_combine(range(0, count($row) - 1), $row);
            }

            // Adicionar metadados
            $data['_row_number'] = $index + ($this->hasHeaders ? 2 : 1);
            $data['_original_row'] = $row;

            return $data;
        });
    }

    /**
     * Obter dados processados (com filtros, busca e ordenação)
     */
    protected function getProcessedData(): Collection
    {
        $collection = $this->getRawExcelData();

        // Aplicar transformador de dados se definido
        if ($this->dataTransformer) {
            $collection = $this->evaluate($this->dataTransformer, [
                'data' => $collection,
                'source' => $this->source,
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
     * Recarregar dados do Excel
     */
    public function reload(): static
    {
        $this->clearCache();
        return $this;
    }

    /**
     * Salvar dados no Excel (apenas para arquivos locais e storage)
     */
    public function save(Collection $data, string $format = 'xlsx'): static
    {
        if ($this->isUrl) {
            throw new \RuntimeException('Não é possível salvar dados em URLs');
        }

        $export = new class($data) implements FromCollection, WithHeadings {
            private Collection $data;

            public function __construct(Collection $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    // Remover metadados internos
                    return collect($item)->except(['_row_number', '_original_row'])->toArray();
                });
            }

            public function headings(): array
            {
                if ($this->data->isEmpty()) {
                    return [];
                }

                $first = $this->data->first();
                return collect($first)->except(['_row_number', '_original_row'])->keys()->toArray();
            }
        };

        $fileName = $this->source;
        
        // Ajustar extensão se necessário
        $currentExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($currentExtension !== $format) {
            $fileName = pathinfo($fileName, PATHINFO_DIRNAME) . '/' . 
                       pathinfo($fileName, PATHINFO_FILENAME) . '.' . $format;
        }

        if ($this->isStoragePath) {
            Excel::store($export, $fileName, $this->config['storage_disk']);
        } else {
            Excel::store($export, basename($fileName), 'local', \Maatwebsite\Excel\Excel::XLSX);
            
            // Mover para local correto se necessário
            $tempPath = storage_path('app/' . basename($fileName));
            if ($tempPath !== $fileName) {
                rename($tempPath, $fileName);
            }
        }

        $this->clearCache();
        return $this;
    }

    /**
     * Obter informações de debug específicas do Excel
     */
    public function getDebugInfo(): array
    {
        $baseInfo = parent::getDebugInfo();

        return array_merge($baseInfo, [
            'source' => $this->source,
            'source_type' => $this->isUrl ? 'url' : ($this->isStoragePath ? 'storage' : 'file'),
            'worksheet' => $this->worksheet,
            'has_headers' => $this->hasHeaders,
            'header_map' => $this->headerMap,
            'has_transformer' => $this->dataTransformer !== null,
            'file_extension' => pathinfo($this->source, PATHINFO_EXTENSION),
            'file_size' => $this->getSourceSize(),
            'last_modified' => $this->getLastModified(),
            'estimated_rows' => $this->getEstimatedRows(),
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
     * Estimar número de linhas (para arquivos grandes)
     */
    protected function getEstimatedRows(): ?int
    {
        try {
            $size = $this->getSourceSize();
            if (!$size) {
                return null;
            }

            // Estimativa grosseira baseada no tamanho do arquivo
            // CSV: ~100 bytes por linha
            // Excel: ~50 bytes por linha (comprimido)
            $extension = strtolower(pathinfo($this->source, PATHINFO_EXTENSION));
            $bytesPerRow = $extension === 'csv' ? 100 : 50;

            return intval($size / $bytesPerRow);
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

    public static function csv(string $source, array $config = []): static
    {
        $config = array_merge($config, [
            'delimiter' => $config['delimiter'] ?? ',',
            'enclosure' => $config['enclosure'] ?? '"',
            'escape' => $config['escape'] ?? '\\',
        ]);

        return new static($source, $config);
    }

    public static function excel(string $source, ?string $worksheet = null, array $config = []): static
    {
        $instance = new static($source, $config);
        
        if ($worksheet) {
            $instance->worksheet($worksheet);
        }

        return $instance;
    }
} 