<?php

namespace SaeidSharafi\Changelog\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class MakeChangelogEntryCommand extends Command
{
    protected $signature
        = 'changelog:entry'
        .' {--app-version= : The version number (e.g., 1.2.0)}'
        .' {--date= : The release date (YYYY-MM-DD)}'
        .' {--changes= : The changes (comma-separated or multiline)}'
        .' {--file= : Custom changelog file path (overrides autodetect)}';

    protected $description = 'Scaffold a new changelog entry in Markdown, YAML, or JSON format.';


    public function handle()
    {
        $basePath = base_path();
        $yamlPath = $basePath.'/changelog.yaml';
        $jsonPath = $basePath.'/changelog.json';
        $mdPath = $basePath.'/CHANGELOG.md';
        $format = null;
        $path = null;

        $customFile = $this->option('file');
        if ($customFile) {
            // Use the file path exactly as given, do not prepend base_path()
            $path = $customFile;
            $ext = pathinfo($customFile, PATHINFO_EXTENSION);
            if ($ext === 'yaml' || $ext === 'yml') {
                $format = 'yaml';
            } elseif ($ext === 'json') {
                $format = 'json';
            } else {
                $format = 'md';
            }

            // Ensure directory exists for custom file
            $dir = dirname($path);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true);
            }
        } else {
            if (File::exists($yamlPath)) {
                $format = 'yaml';
                $path = $yamlPath;
            } elseif (File::exists($jsonPath)) {
                $format = 'json';
                $path = $jsonPath;
            } elseif (File::exists($mdPath)) {
                $format = 'md';
                $path = $mdPath;
            } else {
                $this->error('No changelog file found. Please create changelog.yaml, changelog.json, or CHANGELOG.md.');
                return 1;
            }
        }

        $version = $this->option('app-version') ?: $this->ask('Version (e.g., 1.2.0)');
        $date = $this->option('date') ?: $this->ask('Release date (YYYY-MM-DD)', date('Y-m-d'));
        $changes = $this->option('changes')
            ?: $this->ask('List changes (comma-separated or multiline, end with a blank line)');
        if (strpos($changes, "\n") !== false) {
            $changesArr = array_filter(array_map('trim', explode("\n", $changes)));
        } else {
            $changesArr = array_filter(array_map('trim', explode(',', $changes)));
        }

        if ($format === 'yaml') {
            $data = [];
            if (File::exists($path)) {
                $raw = File::get($path);
                $data = $raw ? Yaml::parse($raw) : [];
                if (!is_array($data)) {
                    $data = [];
                }
            }
            // If file is empty, Yaml::parse returns null, so $data is []
            $data[] = [
                'version' => $version,
                'date'    => $date,
                'changes' => $changesArr,
            ];
            File::put($path, Yaml::dump($data, 2, 2));
            $this->info("[DEBUG] Wrote YAML to: $path");
        } elseif ($format === 'json') {
            $data = [];
            if (File::exists($path)) {
                $raw = File::get($path);
                $data = $raw ? json_decode($raw, true) : [];
                if (!is_array($data)) {
                    $data = [];
                }
            }
            // If file is empty, json_decode returns null, so $data is []
            $data[] = [
                'version' => $version,
                'date'    => $date,
                'changes' => $changesArr,
            ];
            File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("[DEBUG] Wrote JSON to: $path");
        } else {
            // Markdown
            $entry = "\n## $version - $date\n\n";
            foreach ($changesArr as $change) {
                $entry .= "- $change\n";
            }
            $content = File::exists($path) ? File::get($path) : '';
            $content = rtrim($content, "\n"); // Remove trailing newlines
            if (preg_match('/^#.+$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                $content = substr($content, 0, $pos)."\n".$entry.substr($content, $pos);
            } else {
                $content .= "\n".$entry;
            }

            File::put($path, ltrim($content, "\n"));
            $this->info("[DEBUG] Wrote Markdown to: $path");
        }
        $this->info("Changelog entry for version $version added to $path.");
        return 0;
    }
}
