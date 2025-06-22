<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Views;

use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToName;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToIcon;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToId;
use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;

class View
{
    use 
    EvaluatesClosures,
    BelongsToName,
        BelongsToIcon,
        BelongsToLabel,
        BelongsToId;

    protected ?string $description = null;
    protected array $config = [];

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
        $this->name($id);
    }


    public static function make(string $id, string $label): static
    {
        return new static($id, $label);
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function config(array $config): self
    {
        $this->config = $config;
        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getConfig(): array
    {
        return $this->config;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'description' => $this->getDescription(),
            'config' => $this->getConfig(),
        ];
    }
}
