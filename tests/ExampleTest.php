<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Core\Table\Columns\TextColumn;

it('can test', function () {
    expect(true)->toBeTrue();
});

it('can create text column', function () {
    $column = TextColumn::make('name', 'Nome');
    
    expect($column)->toBeInstanceOf(TextColumn::class);
    expect($column->getKey())->toBe('name');
    expect($column->getLabel())->toBe('Nome');
});
