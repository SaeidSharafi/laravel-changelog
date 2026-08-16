<?php

namespace SaeidSharafi\Changelog\Http\Controllers;

use Inertia\Inertia;
use SaeidSharafi\Changelog\Changelog;

class ChangelogController
{
    public function __invoke(Changelog $changelog)
    {
        return Inertia::render('Changelog', [
            'changelog' => $changelog->getChangelog(),
        ]);
    }
}
