<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SaeidSharafi\Changelog\Middleware\CheckVersionMiddleware;

beforeEach(function () {
    $this->testDir = __DIR__ . '/temp_mw';
    (new Filesystem)->ensureDirectoryExists($this->testDir);
    \Illuminate\Support\Facades\Config::set('changelog.current_version', '2.0.0');
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->testDir);
    \Illuminate\Support\Facades\Config::set('changelog.current_version', null);
});

it('shares null changelog prop for guests', function () {
    $request = Request::create('/');
    $request->setUserResolver(fn () => null);
    $middleware = new CheckVersionMiddleware();
    $middleware->handle($request, fn ($req) => response('ok'));
    expect(Inertia::getShared('changelog'))->toBeNull();
});

it('shares unreadCount and newReleases for a user behind the current version', function () {
    $yaml = "- version: 2.0.0\n"
        . "  date: 1404-05-21\n"
        . "  title: 'Release v2'\n"
        . "  changes:\n"
        . "    - title: 'Added feature X'\n"
        . "      type: feature\n"
        . "- version: 1.5.0\n"
        . "  date: 1404-04-01\n"
        . "  title: 'Release v1.5'\n"
        . "  changes:\n"
        . "    - title: 'Added feature Y'\n"
        . "      type: feature\n";
    $file = $this->testDir . '/changelog.yaml';
    file_put_contents($file, $yaml);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.yaml.fallback', $file);

    $request = Request::create('/');
    $request->setUserResolver(fn () => (object) ['version' => '1.5.0']);
    $middleware = new CheckVersionMiddleware();
    $middleware->handle($request, fn ($req) => response('ok'));

    $shared = Inertia::getShared('changelog');
    expect($shared)->not->toBeNull()
        ->and($shared['unreadCount'])->toBe(1)
        ->and($shared['newReleases'][0]['version'])->toBe('2.0.0');
});

it('shares null changelog prop when the user is up to date', function () {
    $request = Request::create('/');
    $request->setUserResolver(fn () => (object) ['version' => '2.0.0']);
    $middleware = new CheckVersionMiddleware();
    $middleware->handle($request, fn ($req) => response('ok'));
    expect(Inertia::getShared('changelog'))->toBeNull();
});
