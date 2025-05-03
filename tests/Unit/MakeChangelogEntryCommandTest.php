<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->testDir = __DIR__ . '/temp_cmd';
    (new Filesystem)->ensureDirectoryExists($this->testDir);
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->testDir);
});

it('writes markdown changelog entry to file', function () {
    $mdPath = $this->testDir . '/CHANGELOG.md';
    $this->artisan('changelog:entry', [
        '--app-version' => '2.0.0',
        '--date' => '2025-05-03',
        '--changes' => 'Tested new feature,Fixed bug',
        '--file' => $mdPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $content = file_get_contents($mdPath);
    expect($content)->toContain('2.0.0')
        ->toContain('...');
});

it('writes yaml changelog entry to file', function () {
    $yamlPath = $this->testDir . '/changelog.yaml';
    $this->artisan('changelog:entry', [
        '--app-version' => '2.1.0',
        '--date' => '2025-05-03',
        '--changes' => 'YAML feature',
        '--file' => $yamlPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $content = file_get_contents($yamlPath);
    expect($content)->toContain('2.1.0')
        ->toContain('...');
});

it('writes json changelog entry to file', function () {
    $jsonPath = $this->testDir . '/changelog.json';
    $this->artisan('changelog:entry', [
        '--app-version' => '2.2.0',
        '--date' => '2025-05-03',
        '--changes' => 'JSON feature',
        '--file' => $jsonPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $content = file_get_contents($jsonPath);
    expect($content)->toContain('2.2.0')
        ->toContain('...');
});

it('writes custom yaml changelog entry to file', function () {
    $customPath = $this->testDir . '/custom_changelog.yaml';
    $this->artisan('changelog:entry', [
        '--app-version' => '3.0.0',
        '--date' => '2025-05-03',
        '--changes' => 'Custom path feature',
        '--file' => $customPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $content = file_get_contents($customPath);
    expect($content)->toContain('3.0.0')
        ->toContain('...');
});
