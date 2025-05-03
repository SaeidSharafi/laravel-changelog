<?php
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->testDir = __DIR__ . '/temp';
    (new Filesystem)->ensureDirectoryExists($this->testDir);
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->testDir);
});

it('parses markdown changelog by version', function () {
    $md = "# Changelog\n\n## 1.1.0 - 2025-05-01\n- Added feature A\n- Fixed bug B\n\n## 1.0.0 - 2025-04-01\n- Initial release\n";
    $file = $this->testDir . '/CHANGELOG.md';
    file_put_contents($file, $md);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.md.en', $file);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.md.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->parseMarkdownChangelog();
    expect($parsed)->toHaveKey('1.1.0')
        ->and($parsed['1.1.0'])->toContain('Added feature A')
        ->and($parsed)->toHaveKey('1.0.0');
});

it('parses yaml changelog', function () {
    $yaml = "- version: 1.1.0\n  date: 2025-05-01\n  changes:\n    - Added feature A\n    - Fixed bug B\n- version: 1.0.0\n  date: 2025-04-01\n  changes:\n    - Initial release\n";
    $file = $this->testDir . '/changelog.yaml';
    file_put_contents($file, $yaml);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.yaml.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->getChangelog();
    expect($parsed[0]['version'])->toBe('1.1.0')
        ->and($parsed[0]['changes'])->toContain('Added feature A');
});

it('parses json changelog', function () {
    $json = '[{"version":"1.1.0","date":"2025-05-01","changes":["Added feature A","Fixed bug B"]},{"version":"1.0.0","date":"2025-04-01","changes":["Initial release"]}]';
    $file = $this->testDir . '/changelog.json';
    file_put_contents($file, $json);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.json.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->getChangelog();
    expect($parsed[0]['version'])->toBe('1.1.0')
        ->and($parsed[0]['changes'])->toContain('Fixed bug B');
});
