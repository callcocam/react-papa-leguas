<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table;

use Callcocam\ReactPapaLeguas\Core;

class Table
{
    use Core\Concerns\BelongsToId;
    use Core\Concerns\EvaluatesClosures;

    /**
     * The component name.
     *
     * @var string
     */
    protected string $component = 'table';

    /**
     * The component view.
     *
     * @var string
     */
    protected string $view = 'react-papa-leguas::core.table.table';
}
