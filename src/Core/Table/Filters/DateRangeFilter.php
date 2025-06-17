<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

class DateRangeFilter extends Filter
{
    protected string $startField;
    protected string $endField;
    protected string $format = 'Y-m-d';
    protected bool $includeTime = false;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'daterange';
        $this->startField = $key . '_start';
        $this->endField = $key . '_end';
    }

    public function field(string $field): static
    {
        $this->key = $field;
        $this->startField = $field . '_start';
        $this->endField = $field . '_end';
        return $this;
    }

    public function startField(string $field): static
    {
        $this->startField = $field;
        return $this;
    }

    public function endField(string $field): static
    {
        $this->endField = $field;
        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function includeTime(bool $includeTime = true): static
    {
        $this->includeTime = $includeTime;
        if ($includeTime) {
            $this->type = 'datetimerange';
            $this->format = 'Y-m-d H:i:s';
        }
        return $this;
    }

    public function getStartField(): string
    {
        return $this->startField;
    }

    public function getEndField(): string
    {
        return $this->endField;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function shouldIncludeTime(): bool
    {
        return $this->includeTime;
    }

    protected function defaultApply($query, $value): void
    {
        if (!is_array($value) || count($value) < 2) {
            return;
        }

        $start = $value[0] ?? null;
        $end = $value[1] ?? null;

        if ($start && $end) {
            if ($this->includeTime) {
                $query->whereBetween($this->getKey(), [$start, $end]);
            } else {
                $query->whereDate($this->getKey(), '>=', $start)
                      ->whereDate($this->getKey(), '<=', $end);
            }
        } elseif ($start) {
            if ($this->includeTime) {
                $query->where($this->getKey(), '>=', $start);
            } else {
                $query->whereDate($this->getKey(), '>=', $start);
            }
        } elseif ($end) {
            if ($this->includeTime) {
                $query->where($this->getKey(), '<=', $end);
            } else {
                $query->whereDate($this->getKey(), '<=', $end);
            }
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'startField' => $this->getStartField(),
            'endField' => $this->getEndField(),
            'format' => $this->getFormat(),
            'includeTime' => $this->shouldIncludeTime(),
        ]);
    }
}
