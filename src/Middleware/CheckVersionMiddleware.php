<?php

namespace SaeidSharafi\Changelog\Middleware;

use App\Models\Auth\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use SaeidSharafi\Changelog\Changelog;
use SaeidSharafi\Changelog\ChangelogFacade;

class CheckVersionMiddleware
{
    protected $changelog;

    public function __construct(Changelog $changelog)
    {
        $this->changelog = $changelog;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $currentVersion = config('changelog.current_version');

        if ($user && version_compare($user->version, $currentVersion, '<')) {
            try {
                $newChanges = $this->changelog->getChanges();
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Error fetching changelog: ' . $e->getMessage()], 500);
                }
                throw $e;
            }

            // Attach changelog depending on request type
            if ($request->expectsJson()) {
                // API: Attach to response in after-middleware
                $request->attributes->set('newChanges', $newChanges);
            } elseif ($request->hasHeader('X-Inertia')) {
                // Inertia.js: Share via Inertia::share
                Inertia::share('newChanges', $newChanges);
            } else {
                // Blade: Flash to session
                session()->flash('newChanges', $newChanges);
            }

            // Do NOT update user version here. Should be done after user acknowledges changelog.
            // User::where('id', $user->id)->update(['version' => $currentVersion]);
        }

        return $next($request);
    }
}
