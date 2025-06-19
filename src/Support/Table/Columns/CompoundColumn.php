<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

class CompoundColumn extends TextColumn
{
    protected string $view = 'compound';

    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->setUp();
    }

    protected function setUp(): void
    {
        $this->renderAs($this->view);
    }
    
    public function avatar(string $field): static
    {
        $this->rendererOptions(array_merge(
            $this->attributes['rendererOptions'] ?? [],
            ['avatarField' => $field]
        ));

        return $this;
    }

    public function icon(string $iconName, ?string $iconColor = null): static
    {
        $this->rendererOptions(array_merge(
            $this->attributes['rendererOptions'] ?? [],
            ['icon' => $iconName, 'iconColor' => $iconColor]
        ));

        return $this;
    }
    
    protected function addTextField(string $field, string $className): void
    {
        $textFields = $this->attributes['rendererOptions']['textFields'] ?? [];
        $textFields[] = ['field' => $field, 'className' => $className];

        $this->rendererOptions(array_merge(
            $this->attributes['rendererOptions'] ?? [],
            ['textFields' => $textFields]
        ));
    }

    public function title(string $field): static
    {
        $this->addTextField($field, 'font-medium text-foreground truncate');

        return $this;
    }

    public function description(string $field): static
    {
        $this->addTextField($field, 'text-sm text-muted-foreground truncate');

        return $this;
    }

    public function line(string $field, string $className): static
    {
        $this->addTextField($field, $className);

        return $this;
    }

    public function getRequiredFields(): array
    {
        $fields = parent::getRequiredFields();

        if ($avatarField = $this->attributes['rendererOptions']['avatarField'] ?? null) {
            $fields[] = $avatarField;
        }

        if ($textFields = $this->attributes['rendererOptions']['textFields'] ?? []) {
            foreach ($textFields as $textField) {
                if (isset($textField['field'])) {
                    $fields[] = $textField['field'];
                }
            }
        }

        return array_unique($fields);
    }
} 