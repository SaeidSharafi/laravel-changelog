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
        .' {--file= : Custom changelog file path (overrides autodetect)}'
        .' {--silent : Silently create a placeholder entry without prompts}';

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

        $silent = $this->option('silent');
        if ($silent) {
            $version = $this->option('app-version') ?: 'x.y.z';
            $date = $this->option('date') ?: date('Y-m-d');
            $title = '...';
            $changes = [
                [
                    'title' => '...',
                    'subtitles' => ['...'],
                ]
            ];
            $notes = ['...'];
        } else {
            $version = $this->option('app-version') ?: $this->ask('Version (e.g., 1.2.0)');
            $date = $this->option('date') ?: $this->ask('Release date (YYYY-MM-DD)', date('Y-m-d'));
            $title = $this->ask('Entry title (e.g., "ثبت نام سازمانی")');

            // Gather changes interactively
            $changes = [];
            $this->info('Enter changes for this version.');
            do {
                $changeTitle = $this->ask('Change title (leave blank to finish)');
                if (!$changeTitle) break;
                $subtitles = [];
                $this->info('Enter subtitles for this change. Enter each subtitle and press Enter. Leave blank to finish.');
                while (true) {
                    $subtitle = $this->ask('Subtitle (leave blank to finish)');
                    if (!$subtitle) break;
                    $subtitles[] = $subtitle;
                }
                $changes[] = [
                    'title' => $changeTitle,
                    'subtitles' => $subtitles,
                ];
            } while (true);

            // Optionally gather notes
            $notes = [];
            if ($this->confirm('Add notes?')) {
                $this->info('Enter notes. Enter each note and press Enter. Leave blank to finish.');
                while (true) {
                    $note = $this->ask('Note (leave blank to finish)');
                    if (!$note) break;
                    $notes[] = $note;
                }
            }
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
            $entry = [
                'version' => $version,
                'date'    => $date,
                'title'   => $title,
                'changes' => $changes,
            ];
            if ($notes) {
                $entry['notes'] = $notes;
            }
            $data[] = $entry;
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
            $entry = [
                'version' => $version,
                'date'    => $date,
                'title'   => $title,
                'changes' => $changes,
            ];
            if ($notes) {
                $entry['notes'] = $notes;
            }
            $data[] = $entry;
            File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("[DEBUG] Wrote JSON to: $path");
        } else {
            // Markdown: insert a placeholder for manual editing
            $entry = "\n## $version - $date\n\n";
            $entry .= "**$title**\n\n";
            $entry .= "> [!NOTE] Please fill in the following structure manually.\n";
            $entry .= "```yaml\n";
            $entry .= Yaml::dump([
                'version' => $version,
                'date' => $date,
                'title' => $title,
                'changes' => [['title' => '...', 'subtitles' => ['...']]],
                'notes' => ['...'],
            ], 2, 2);
            $entry .= "```\n\n";
            $content = File::exists($path) ? File::get($path) : '';
            $content = rtrim($content, "\n");
            if (preg_match('/^#.+$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                $content = substr($content, 0, $pos)."\n".$entry.substr($content, $pos);
            } else {
                $content .= "\n".$entry;
            }
            File::put($path, ltrim($content, "\n"));
            $this->info("[DEBUG] Wrote Markdown placeholder to: $path");
        }
        $this->info("Changelog entry for version $version added to $path.");
        return 0;
    }
}
