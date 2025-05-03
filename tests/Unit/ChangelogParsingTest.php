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
    $md = "# Changelog\n\n## 1.1.0 - 2025-05-01\n**Feature Release**\n\n> [!NOTE] Please fill in the following structure manually.\n```yaml\nversion: 1.1.0\ndate: 2025-05-01\ntitle: Feature Release\nchanges:\n  - title: Added feature A\n    subtitles:\n      - Subfeature 1\n      - Subfeature 2\n  - title: Fixed bug B\n    subtitles:\n      - Bugfix details\nnotes:\n  - Note 1\n```\n\n## 1.0.0 - 2025-04-01\n**Initial Release**\n\n> [!NOTE] Please fill in the following structure manually.\n```yaml\nversion: 1.0.0\ndate: 2025-04-01\ntitle: Initial Release\nchanges:\n  - title: Initial release\n    subtitles:\n      - First version\nnotes:\n  - Welcome!\n```\n";
    $file = $this->testDir . '/CHANGELOG.md';
    file_put_contents($file, $md);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.md.en', $file);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.md.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->parseMarkdownChangelog();
    expect($parsed)->toHaveKey('1.1.0')
        ->and($parsed['1.1.0'])->toContain('Feature Release')
        ->and($parsed)->toHaveKey('1.0.0');
});

it('parses yaml changelog', function () {
    $yaml = "- version: 1.1.0\n  date: 2025-05-01\n  title: Feature Release\n  changes:\n    - title: Added feature A\n      subtitles:\n        - Subfeature 1\n        - Subfeature 2\n    - title: Fixed bug B\n      subtitles:\n        - Bugfix details\n  notes:\n    - Note 1\n- version: 1.0.0\n  date: 2025-04-01\n  title: Initial Release\n  changes:\n    - title: Initial release\n      subtitles:\n        - First version\n  notes:\n    - Welcome!\n";
    $file = $this->testDir . '/changelog.yaml';
    file_put_contents($file, $yaml);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.yaml.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->getChangelog();
    expect($parsed[0]['version'])->toBe('1.1.0')
        ->and($parsed[0]['title'])->toBe('Feature Release')
        ->and($parsed[0]['changes'][0]['title'])->toBe('Added feature A')
        ->and($parsed[0]['changes'][0]['subtitles'])->toContain('Subfeature 1');
});

it('parses json changelog', function () {
    $json = '[{"version":"1.1.0","date":"2025-05-01","title":"Feature Release","changes":[{"title":"Added feature A","subtitles":["Subfeature 1","Subfeature 2"]},{"title":"Fixed bug B","subtitles":["Bugfix details"]}],"notes":["Note 1"]},{"version":"1.0.0","date":"2025-04-01","title":"Initial Release","changes":[{"title":"Initial release","subtitles":["First version"]}],"notes":["Welcome!"]}]';
    $file = $this->testDir . '/changelog.json';
    file_put_contents($file, $json);
    \Illuminate\Support\Facades\Config::set('changelog.changelog_path.json.fallback', $file);
    $changelog = new \SaeidSharafi\Changelog\Changelog();
    $parsed = $changelog->getChangelog();
    expect($parsed[0]['version'])->toBe('1.1.0')
        ->and($parsed[0]['title'])->toBe('Feature Release')
        ->and($parsed[0]['changes'][0]['title'])->toBe('Added feature A')
        ->and($parsed[0]['changes'][0]['subtitles'])->toContain('Subfeature 1');
});
