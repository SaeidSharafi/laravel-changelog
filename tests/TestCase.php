<?php

namespace SaeidSharafi\Changelog\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use SaeidSharafi\Changelog\ChangelogServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function getPackageProviders($app)
    {
        return [
            ChangelogServiceProvider::class,
        ];
    }
}
