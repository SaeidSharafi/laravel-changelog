# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/saeidsharafi/changelog.svg?style=flat-square)](https://packagist.org/packages/saeidsharafi/changelog)
[![Total Downloads](https://img.shields.io/packagist/dt/saeidsharafi/changelog.svg?style=flat-square)](https://packagist.org/packages/saeidsharafi/changelog)
![GitHub Actions](https://github.com/saeidsharafi/changelog/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require saeidsharafi/changelog
```

## Usage

```php
// Usage description here
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Changelog File Formats

This package supports changelogs in **Markdown**, **YAML**, or **JSON** formats. The file is auto-detected in this order: `changelog.yaml`, `changelog.json`, `CHANGELOG.md`.

### YAML Example
```yaml
- version: 1.2.0
  date: 2025-05-03
  changes:
    - Added new feature X
    - Fixed bug Y
- version: 1.1.0
  date: 2025-04-01
  changes:
    - Improved performance
```

### JSON Example
```json
[
  {
    "version": "1.2.0",
    "date": "2025-05-03",
    "changes": [
      "Added new feature X",
      "Fixed bug Y"
    ]
  },
  {
    "version": "1.1.0",
    "date": "2025-04-01",
    "changes": [
      "Improved performance"
    ]
  }
]
```

### Markdown Example
```
# Changelog

## 1.2.0 - 2025-05-03
- Added new feature X
- Fixed bug Y

## 1.1.0 - 2025-04-01
- Improved performance
```

## Artisan Command: changelog:entry

You can scaffold a new changelog entry using:

```bash
php artisan changelog:entry --version=1.3.0 --date=2025-06-01 --changes="New feature,Another change"
```

**Options:**
- `--file`  Specify a custom changelog file path (overrides autodetect, e.g. `--file=/path/to/changelog.yaml`)
- `--version`  Version number (e.g. 1.3.0)
- `--date`  Release date (YYYY-MM-DD)
- `--changes`  Comma-separated or multiline list of changes

If options are omitted, you will be prompted interactively.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email saeidsharafi263@gmail.com instead of using the issue tracker.

## Credits

-   [Saeid Sharafi](https://github.com/saeidsharafi)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
