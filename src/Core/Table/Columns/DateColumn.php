<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

class DateColumn extends Column
{
    protected string $format = 'd/m/Y';
    protected ?string $timezone = null;
    protected bool $relative = false;
    protected bool $showTime = false;
    protected string $timeFormat = 'H:i';

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'date';
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function relative(bool $relative = true): static
    {
        $this->relative = $relative;
        return $this;
    }

    public function showTime(bool $showTime = true): static
    {
        $this->showTime = $showTime;
        if ($showTime && !str_contains($this->format, 'H') && !str_contains($this->format, 'h')) {
            $this->format .= ' H:i';
        }
        return $this;
    }

    public function timeFormat(string $format): static
    {
        $this->timeFormat = $format;
        return $this;
    }

    // Shortcuts for common formats
    public function dateOnly(): static
    {
        return $this->format('d/m/Y')->showTime(false);
    }

    public function datetime(): static
    {
        return $this->format('d/m/Y H:i')->showTime(true);
    }

    public function time(): static
    {
        return $this->format('H:i')->showTime(true);
    }

    public function iso(): static
    {
        return $this->format('Y-m-d');
    }

    public function isoDatetime(): static
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function isRelative(): bool
    {
        return $this->relative;
    }

    public function shouldShowTime(): bool
    {
        return $this->showTime;
    }

    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->getFormat(),
            'timezone' => $this->getTimezone(),
            'relative' => $this->isRelative(),
            'showTime' => $this->shouldShowTime(),
            'timeFormat' => $this->getTimeFormat(),
        ]);
    }
}
