<?php

namespace SaeidSharafi\Changelog;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SaeidSharafi\Changelog\Skeleton\SkeletonClass
 */
class ChangelogFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'changelog';
    }
}
