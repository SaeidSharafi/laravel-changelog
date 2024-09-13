<?php

namespace SaeidSharafi\Changelog;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Changelog
{
    protected $changelogPath;

    public function __construct()
    {
        $lang = App::getLocale();
        if ( ! $lang) {
            $lang = 'fallback';
        }
        $this->changelogPath = config("changelog.changelog_path.{$lang}");
        if ( ! $this->changelogPath) {
            $this->changelogPath = config("changelog.changelog_path.fallback");
        }

        // Handle the case where the configuration is missing or incorrect
        if ( ! $this->changelogPath || ! File::exists($this->changelogPath)) {
            throw new \Exception("Changelog file not found or configuration is incorrect. Please check 'changelog.changelog_path'.");
        }

    }

    public function getChangelog()
    {
        try {
            return File::get($this->changelogPath);
        } catch (Exception $e) {
            // Handle the case where the file could not be read
            throw new \Exception("Unable to read the changelog file: ".$e->getMessage());
        }
    }

    public function getChanges()
    {
        if (config('changelog.use_html')) {
            return Str::markdown($this->getChangelog());
        }

        return $this->getChangelog();
    }
}
