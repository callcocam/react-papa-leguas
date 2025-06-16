<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas;

class ReactPapaLeguas {

    protected $id ="admin";

    protected $prefix = "admin";

    protected $middelwares = ['web'];

    public function getId()
    {
        return $this->id;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getMiddlewares()
    {
        return $this->middelwares;
    }
}
