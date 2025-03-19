<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace VendorName\Skeleton\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VendorName\Skeleton\Skeleton
 */
class Skeleton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VendorName\Skeleton\Skeleton::class;
    }
}
