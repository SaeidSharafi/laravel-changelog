<?php

namespace SaeidSharafi\Changelog\Http\Controllers;

use Illuminate\Http\Request;

class AcknowledgeChangelogController
{
    public function __invoke(Request $request)
    {
        $request->user()->update(['version' => config('changelog.current_version')]);

        return response()->noContent();
    }
}
