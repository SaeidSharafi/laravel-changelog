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
        .' {--highlight : Mark this release as a highlighted release}'
        .' {--ai : Print a ready-to-paste AI prompt instead of writing an entry}'
        .' {--silent : Silently create a placeholder entry without prompts}';

    protected $description = 'Scaffold a new changelog entry in Markdown, YAML, or JSON format.';

    public function handle()
    {
        if ($this->option('ai')) {
            $this->printAiPrompt();

            return 0;
        }

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

                $change = [
                    'title' => $changeTitle,
                    'subtitles' => $subtitles,
                ];

                $type = $this->choice('Change type', ['feature', 'improvement', 'fix'], 'feature');
                $change['type'] = $type;

                $url = $this->ask('Optional URL (route name, leave blank to skip)');
                if ($url) {
                    $change['url'] = $url;
                }

                $image = $this->ask('Optional image path (e.g., public/changelog/1.2.0/foo.png, leave blank to skip)');
                if ($image) {
                    $change['image'] = $image;
                }

                $videoSource = $this->choice('Video source (aparat/mp4/none)', ['aparat', 'mp4', 'none'], 'none');
                if ($videoSource !== 'none') {
                    if ($videoSource === 'aparat') {
                        $videoId = $this->ask('Aparat video ID');
                        $change['video'] = ['source' => 'aparat', 'id' => $videoId];
                    } else {
                        $videoSrc = $this->ask('Video file path/src (e.g., /storage/videos/foo.mp4)');
                        $change['video'] = ['source' => 'mp4', 'src' => $videoSrc];
                    }
                }

                $changes[] = $change;
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

        $entry = [
            'version' => $version,
            'date'    => $date,
            'title'   => $title,
            'changes' => $changes,
        ];
        if ($this->option('highlight')) {
            $entry['highlight'] = true;
        }
        if ($notes) {
            $entry['notes'] = $notes;
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
            // The YAML file lists releases newest-first: prepend the new entry.
            array_unshift($data, $entry);
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
            array_unshift($data, $entry);
            File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("[DEBUG] Wrote JSON to: $path");
        } else {
            // Markdown: insert a placeholder for manual editing
            $mdEntry = "\n## $version - $date\n\n";
            $mdEntry .= "**$title**\n\n";
            $mdEntry .= "> [!NOTE] Please fill in the following structure manually.\n";
            $mdEntry .= "```yaml\n";
            $mdEntry .= Yaml::dump($entry, 2, 2);
            $mdEntry .= "```\n\n";
            $content = File::exists($path) ? File::get($path) : '';
            $content = rtrim($content, "\n");
            if (preg_match('/^#.+$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                $content = substr($content, 0, $pos)."\n".$mdEntry.substr($content, $pos);
            } else {
                $content .= "\n".$mdEntry;
            }
            File::put($path, ltrim($content, "\n"));
            $this->info("[DEBUG] Wrote Markdown placeholder to: $path");
        }
        $this->info("Changelog entry for version $version added to $path.");
        return 0;
    }

    /**
     * Print a ready-to-paste prompt for authoring the changelog entry with AI.
     */
    protected function printAiPrompt()
    {
        $version = $this->option('app-version');
        $versionLine = $version
            ? "The new app version is **{$version}**."
            : 'The new app version is not specified yet — ask me for it or keep it as a placeholder.';

        $this->line(<<<PROMPT
You are helping me write a release-notes entry for the changelog of my application. {$versionLine}

Gather candidate entries from the git commits since the last released version (the newest release already present in the changelog file). Read the commit messages, group them into user-facing changes, and propose a draft release entry.

The HUMAN AUTHOR decides which entries actually ship — do not include everything blindly. For example, usually only features (and sometimes improvements) are announced, while fixes are hidden. Flag anything you are unsure about.

Write the entry in the following YAML schema (all fields optional unless marked required):

```yaml
- version: "1.2.0"            # required, semver
  date: "1404-05-01"          # required, Jalali date (YYYY-MM-DD)
  title: "Release title"      # required, short, action-first, end-user voice
  highlight: true             # optional, mark the release as highlighted
  changes:                    # required, list of user-facing changes
    - title: "Short action-first title"
      type: feature           # feature | improvement | fix
      subtitles:              # optional
        - "Detail 1"
        - "Detail 2"
      image: "public/changelog/1.2.0/feature.png"  # optional, under public/changelog/<version>/
      url: "route.name"       # optional, a valid route name in the app
      video:                  # optional
        source: "aparat"      # aparat | mp4
        id: "aparate-video-id" # when source is aparat
        # src: "/storage/videos/feature.mp4"  # when source is mp4
  notes:                      # optional
    - "Release note"
```

Tone rules:
- Natural Persian, neither overly formal nor informal.
- Short sentences. Titles are action-first (e.g. "ثبت نام سازمانی اضافه شد").
- End-user voice: what the user can now do, not developer jargon.

Quality rules:
- type must be one of: feature, improvement, fix.
- url must be a valid route name in the app.
- image paths must live under public/changelog/<version>/.
- Jalali dates only (YYYY-MM-DD).

Output: the complete YAML block above, filled in, nothing else.
PROMPT
        );
    }
}
