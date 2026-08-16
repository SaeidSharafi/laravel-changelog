# AI_GUIDE.md — Writing Changelog Entries

This guide is for AI assistants (and humans) authoring release-notes entries for the
**laravel-changelog** package. The changelog lives in the consuming application
(`changelog.yaml` by default), not in this package.

## Workflow

1. Determine the new app version and the last released version from the changelog file.
2. Gather candidate entries from the **git commits since the last released version**.
   Read the commit messages and group them into user-facing changes.
3. Propose a draft release entry following the schema below.
4. **The human author decides which entries ship.** Usually only features (and sometimes
   improvements) are announced; fixes are typically hidden. Do not include everything
   blindly — flag anything you are unsure about and let the human decide.

## YAML Schema

The changelog file is an ordered list of releases, **newest first**. All fields are
optional unless marked required.

```yaml
- version: "1.2.0"             # required, semver
  date: "1404-05-01"           # required, Jalali date (YYYY-MM-DD)
  title: "Release title"       # required, short, action-first, end-user voice
  highlight: true              # optional, mark the release as highlighted
  changes:                     # required, list of user-facing changes
    - title: "Short action-first title"
      type: feature            # feature | improvement | fix
      subtitles:               # optional
        - "Detail 1"
        - "Detail 2"
      image: "public/changelog/1.2.0/feature.png"  # optional
      url: "route.name"        # optional, a valid route name in the app
      video:                   # optional
        source: "aparat"       # aparat | mp4
        id: "aparate-video-id" # when source is aparat
        # src: "/storage/videos/feature.mp4"  # when source is mp4
  notes:                       # optional
    - "Release note"
```

## Tone Rules

- Natural Persian, neither overly formal nor informal.
- Short sentences. Titles are action-first (e.g. «ثبت نام سازمانی اضافه شد»).
- End-user voice: what the user can now do, not developer jargon.
- Dates are Jalali (`YYYY-MM-DD`), not Gregorian.

## Quality Rules

- `type` must be one of: `feature`, `improvement`, `fix`.
- `url` must be a valid route name in the application.
- Image paths must live under `public/changelog/<version>/`.
- A video has `source: aparat` with an `id`, or `source: mp4` with a `src`.
- `highlight: true` is reserved for releases worth calling out in the UI.

## Authoring Entry With the Artisan Command

```bash
php artisan changelog:entry --app-version=1.2.0 --highlight
# interactive: type / url / image / video are prompted per change

php artisan changelog:entry --ai --app-version=1.2.0
# prints a ready-to-paste AI prompt (no file is written)
```
