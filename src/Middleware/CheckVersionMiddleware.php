<?php

namespace SaeidSharafi\Changelog\Middleware;

use App\Models\Auth\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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
            } catch (Exception $e) {
                // Log the error or handle it as needed
                if ($request->expectsJson()) {
                    // API response
                    return response()->json(['error' => 'Error fetching changelog: ' . $e->getMessage()], 500);
                }
                throw $e;
            }

            // Store changes in session or attach to request
            if ($request->expectsJson()) {
                // API response

                $request->merge(['newChanges' => $newChanges]);
            } else {
;                // Web application
                session()->flash('newChanges', $newChanges);
                $request->merge(['newChanges' => $newChanges]);
            }

            User::where('id', $user->id)->update(['version' => $currentVersion]);
        }

        return $next($request);
    }
}
