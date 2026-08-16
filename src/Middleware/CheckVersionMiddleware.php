<?php

namespace SaeidSharafi\Changelog\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SaeidSharafi\Changelog\Changelog;

class CheckVersionMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $currentVersion = config('changelog.current_version');

        $newReleases = null;

        if ($user && version_compare($user->version, $currentVersion, '<')) {
            try {
                $newReleases = app(Changelog::class)->getChangesSince($user->version);
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Error fetching changelog: '.$e->getMessage()], 500);
                }
                throw $e;
            }
        }

        if ($newReleases) {
            Inertia::share('changelog', [
                'unreadCount' => count($newReleases),
                'newReleases' => array_values($newReleases),
            ]);
        } else {
            Inertia::share('changelog', null);
        }

        return $next($request);
    }
}
