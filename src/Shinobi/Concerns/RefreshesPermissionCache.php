<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Shinobi\Concerns;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        // static::saved(function() {
        //     cache()->tags(config('shinobi.cache.tag'))->flush();
        // });

        // static::deleted(function() {
        //     cache()->tags(config('shinobi.cache.tag'))->flush();
        // });
    }
}