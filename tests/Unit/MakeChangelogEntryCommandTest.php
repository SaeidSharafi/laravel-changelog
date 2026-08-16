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

it('prepends new yaml release entry (newest first)', function () {
    $yamlPath = $this->testDir . '/changelog.yaml';
    file_put_contents($yamlPath, "- version: 1.0.0\n  date: 1403-01-01\n  title: 'Old release'\n  changes: []\n");
    $this->artisan('changelog:entry', [
        '--app-version' => '2.0.0',
        '--date' => '2025-05-03',
        '--file' => $yamlPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $parsed = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($yamlPath));
    expect($parsed[0]['version'])->toBe('2.0.0')
        ->and($parsed[1]['version'])->toBe('1.0.0');
});

it('writes highlight flag into yaml entry when --highlight is passed', function () {
    $yamlPath = $this->testDir . '/changelog.yaml';
    $this->artisan('changelog:entry', [
        '--app-version' => '2.1.0',
        '--date' => '2025-05-03',
        '--file' => $yamlPath,
        '--silent' => true,
        '--highlight' => true,
    ])->assertExitCode(0);
    $parsed = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($yamlPath));
    expect($parsed[0]['highlight'])->toBeTrue()
        ->and($parsed[0]['version'])->toBe('2.1.0');
});

it('does not write highlight flag when --highlight is omitted', function () {
    $yamlPath = $this->testDir . '/changelog.yaml';
    $this->artisan('changelog:entry', [
        '--app-version' => '2.2.0',
        '--date' => '2025-05-03',
        '--file' => $yamlPath,
        '--silent' => true,
    ])->assertExitCode(0);
    $parsed = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($yamlPath));
    expect($parsed[0])->not->toHaveKey('highlight');
});

it('prints an AI prompt and writes no file with --ai', function () {
    $command = $this->app->make(\SaeidSharafi\Changelog\Console\MakeChangelogEntryCommand::class);
    $command->setLaravel($this->app);
    $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
    $exitCode = $tester->execute(['--ai' => true, '--app-version' => '2.3.0']);
    $output = $tester->getDisplay();
    expect($exitCode)->toBe(0)
        ->and($output)->toContain('changelog')
        ->and($output)->toContain('schema')
        ->and($output)->toContain('2.3.0');
    expect($this->testDir . '/changelog.yaml')->not->toBeFile();
    expect($this->testDir . '/CHANGELOG.md')->not->toBeFile();
});
