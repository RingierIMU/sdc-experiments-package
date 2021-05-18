<?php

namespace Ringierimu\Experiments\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

class SdcExperiments extends BaseFacade
{
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor(): string
    {
        return 'experiments';
    }
}
