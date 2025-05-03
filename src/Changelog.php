<?php

namespace SaeidSharafi\Changelog;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class Changelog
{
    protected $changelogPath;
    protected $changelogFormat; // 'md', 'yaml', 'json'

    public function __construct()
    {
        $lang = App::getLocale();
        if (! $lang) {
            $lang = 'fallback';
        }
        $basePath = base_path();
        $yamlPath = config("changelog.changelog_path.yaml.{$lang}") ?? config("changelog.changelog_path.yaml.fallback");
        $jsonPath = config("changelog.changelog_path.json.{$lang}") ?? config("changelog.changelog_path.json.fallback");
        $mdPath = config("changelog.changelog_path.md.{$lang}") ?? config("changelog.changelog_path.md.fallback");

        if (File::exists($yamlPath)) {
            $this->changelogPath = $yamlPath;
            $this->changelogFormat = 'yaml';
        } elseif (File::exists($jsonPath)) {
            $this->changelogPath = $jsonPath;
            $this->changelogFormat = 'json';
        } elseif ($mdPath && File::exists($mdPath)) {
            $this->changelogPath = $mdPath;
            $this->changelogFormat = 'md';
        } else {
            throw new \Exception("Changelog file not found or configuration is incorrect. Please check 'changelog.changelog_path'.");
        }
    }

    public function getChangelog()
    {
        try {
            $content = File::get($this->changelogPath);
            if ($this->changelogFormat === 'yaml') {
                return Yaml::parse($content);
            } elseif ($this->changelogFormat === 'json') {
                return json_decode($content, true);
            }
            return $content;
        } catch (\Exception $e) {
            throw new \Exception("Unable to read the changelog file: " . $e->getMessage());
        }
    }

    public function getChanges()
    {
        if ($this->changelogFormat === 'md') {
            if (config('changelog.use_html')) {
                return Str::markdown($this->getChangelog());
            }
            return $this->getChangelog();
        }
        // For YAML/JSON, return as array
        return $this->getChangelog();
    }

    /**
     * Parse the Markdown changelog and return an associative array of versions => changes.
     *
     * @return array
     */
    public function parseMarkdownChangelog()
    {
        $content = trim($this->getChangelog());
        // Normalize newlines and remove extra blank lines
        $content = preg_replace('/\r\n|\r|\n/', "\n", $content);
        // Updated regex: non-greedy match until next version heading or end of string
        $pattern = '/^##\s*([\w\.-]+)[^\n]*\n((?:[^#].*?(?=^##|\z)))/ms';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        $changelog = [];
        foreach ($matches as $match) {
            $version = trim($match[1]);
            $changes = trim($match[2]);
            $changelog[$version] = $changes;
        }
        return $changelog;
    }

    /**
     * Get all changes since a given version (exclusive).
     *
     * @param string|null $sinceVersion
     * @return array
     */
    public function getChangesSince($sinceVersion = null)
    {
        if ($this->changelogFormat === 'md') {
            $changelog = $this->parseMarkdownChangelog();
        } else {
            $changelog = $this->getChangelog();
            if (!is_array($changelog)) {
                return [];
            }
            $changelog = array_column($changelog, null, 'version');
        }
        if ($sinceVersion === null) {
            return $changelog;
        }
        $found = false;
        $result = [];
        foreach ($changelog as $version => $changes) {
            if ($version === $sinceVersion) {
                $found = true;
                continue;
            }
            if ($found) {
                return $found ? $result : $changelog;
            }
            $result[$version] = $changes;
        }
        return $found ? $result : $changelog;
    }
}
