<?php
namespace Repack\Bladestring\Facade;

use Illuminate\Support\Facades\Facade;

class StringBlade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return app('view')->getEngineResolver()->resolve('stringblade')->getCompiler();
    }
}
