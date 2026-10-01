# Contributing

## Requirements

- PHP 8.4+ (development only, the plugin itself supports PHP 8.1+)
- [Composer](https://getcomposer.org/)
- Node.js with [Corepack](https://github.com/nodejs/corepack) enabled, which provides the pnpm version pinned in `package.json`

## Setup

Clone the repository

```bash
git clone git@github.com:nlemoine/acf-country.git
```

Install dependencies

```bash
composer install
pnpm install
```

## Development

Watch assets

```bash
pnpm start
```

Build for production/release (built assets in `assets/dist` are committed)

```bash
pnpm build
```

## Tests

Tests use [Mantle Testkit](https://mantle.alley.com/docs/testing) and run against WordPress and ACF with SQLite, so no database is needed. WordPress is downloaded to `tmp/` on the first run. There are two suites, `unit` and `integration`, and `composer test` runs both.

```bash
composer test
```

## Coding standards

PHP code follows the [Syde](https://github.com/inpsyde/phpcs) coding standards. PHPCompatibility also checks that the plugin runs on PHP 8.1.

```bash
composer cs      # check
composer cs:fix  # fix what can be fixed automatically
```

## Country data

Country names in `data/` are generated from [Unicode CLDR](https://cldr.unicode.org/) through [symfony/intl](https://github.com/symfony/intl). They are regenerated after every `composer install` and `composer update`. To regenerate or check them manually:

```bash
composer sync-country-data
php bin/sync-country-data.php --check
```

Commit the changes in `data/` along with any `symfony/intl` update. CI fails when `data/` is out of date.

## Translations

After changing translatable strings, regenerate the POT file:

```bash
composer i18n:pot
```

## Commits

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/). [Release Please](https://github.com/googleapis/release-please) uses them to generate the changelog and releases, so use `feat:` and `fix:` for user-facing changes.
