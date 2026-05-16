# Statica

<p align="center">
<a href="https://packagist.org/packages/abdalhalem/statica"><img src="https://img.shields.io/packagist/v/abdalhalem/statica" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/abdalhalem/statica"><img src="https://img.shields.io/packagist/l/abdalhalem/statica" alt="License"></a>
<a href="https://packagist.org/packages/abdalhalem/statica"><img src="https://img.shields.io/packagist/dt/abdalhalem/statica" alt="Total Downloads"></a>
</p>

A Laravel package for managing dynamic application settings stored as structured JSON, with built-in Blade directives for rendering values and a built-in dashboard for editing them at runtime — no database required.

---

## Features

- Store settings as structured JSON on any Laravel filesystem disk
- Nested key support via dot notation (`app.brand.name`)
- Four entry types: single value, translatable value, list, and translatable list
- **Blade directives** for rendering settings directly in your views
- Built-in dashboard UI with inline editing and drag-and-drop list reordering
- Auto-creates missing keys with defaults on first use
- Per-entry meta labels for dashboard display (supports multiple locales)
- No database migrations required

---

## Requirements

- PHP **8.1+**
- Laravel **10.x** or **11.x**

---

## Installation

Install the package via Composer:

```bash
composer require abdalhalem/statica
```

Publish the config file:

```bash
php artisan vendor:publish --tag=statica-settings-config
```

Publish the dashboard assets:

```bash
php artisan vendor:publish --tag=statica-assets
```

Optionally publish the views if you want to customize the dashboard UI:

```bash
php artisan vendor:publish --tag=statica-views
```

---

## Configuration

After publishing, the config file will be at `config/statica.php`:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Repository Type
    |--------------------------------------------------------------------------
    |
    | The storage driver Statica will use to persist settings.
    | Currently supported: "JsonFile"
    |
    */
    'repository_type' => \Statica\SettingRepositoryType::JsonFile,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk where Statica will store the settings JSON file.
    | Set to null to use the default disk defined in config/filesystems.php.
    |
    */
    'disk' => env('STATICA_DISK', null),

    /*
    |--------------------------------------------------------------------------
    | Vite Dev Server
    |--------------------------------------------------------------------------
    |
    | Enable this during package development to load dashboard assets from
    | the Vite dev server instead of the published compiled files.
    |
    */
    'vite_dev' => env('STATICA_VITE_DEV', false),

];
```

### Environment Variables

```env
STATICA_DISK=local
STATICA_VITE_DEV=false
```

---

## Blade Directives

This is the primary way to use Statica in your views. Directives automatically create the setting with the given default if it does not exist yet, then output or iterate the value.

### `@settings` — Single Value

Outputs a plain string setting. Creates it with the default if missing.

```blade
@settings('app.name', 'My Application')

{{-- with a dashboard display label --}}
@settings('app.name', 'My Application', ['label' => ['en' => 'App Name', 'ar' => 'اسم التطبيق']])
```

**Parameters:**

| # | Parameter | Type | Description |
|---|---|---|---|
| 1 | `$key` | `string` | Dot-notation key |
| 2 | `$default` | `string\|null` | Default value if key doesn't exist |
| 3 | `$meta` | `array` | Optional. `['label' => ['en' => '...', 'ar' => '...']]` for dashboard display |

---

### `@translatable_settings` — Translatable Single Value

Outputs the value for the **current application locale**. Creates it with the defaults if missing.

```blade
@translatable_settings('app.tagline', ['en' => 'Welcome', 'ar' => 'أهلاً'])

{{-- with a dashboard display label --}}
@translatable_settings('app.tagline', ['en' => 'Welcome', 'ar' => 'أهلاً'], ['label' => ['en' => 'Tagline', 'ar' => 'الشعار']])
```

**Parameters:**

| # | Parameter | Type | Description |
|---|---|---|---|
| 1 | `$key` | `string` | Dot-notation key |
| 2 | `$defaults` | `array` | `['locale' => 'value']` map of defaults |
| 3 | `$meta` | `array` | Optional. `['label' => [...]]` for dashboard display |

---

### `@foreach_settings` — List

Iterates over a list setting. Use `@endforeach_settings` to close the loop.

```blade
@foreach_settings('home.features', [['label' => 'Fast'], ['label' => 'Reliable']])
    <li>{{ $__key['label'] }}</li>
@endforeach_settings

{{-- with a dashboard display label --}}
@foreach_settings('home.features', [['label' => 'Fast']], ['label' => ['en' => 'Features', 'ar' => 'المميزات']])
    <li>{{ $__key['label'] }}</li>
@endforeach_settings
```

**Parameters:**

| # | Parameter | Type | Description |
|---|---|---|---|
| 1 | `$key` | `string` | Dot-notation key |
| 2 | `$defaults` | `array` | Array of default list items |
| 3 | `$meta` | `array` | Optional. `['label' => [...]]` for dashboard display |

---

### `@foreach_translatable_settings` — Translatable List

Iterates over a list where each item's label is translatable. Outputs labels for the **current application locale**.

```blade
@foreach_translatable_settings('home.services', [
    ['label' => ['en' => 'Design', 'ar' => 'تصميم']],
    ['label' => ['en' => 'Development', 'ar' => 'تطوير']],
])
    <li>{{ $__iterator['label'] }}</li>
@endforeach_settings
```

With a custom iterator variable name and meta:

```blade
@foreach_translatable_settings(
    'home.services',
    [['label' => ['en' => 'Design', 'ar' => 'تصميم']]],
    ['label' => ['en' => 'Services', 'ar' => 'الخدمات']],
    'service'
)
    <li>{{ $service['label'] }}</li>
@endforeach_settings
```

**Shorthand** — if you don't need defaults or meta, pass just the key and iterator name:

```blade
@foreach_translatable_settings('home.services', 'service')
    <li>{{ $service['label'] }}</li>
@endforeach_settings
```

**Parameters:**

| # | Parameter | Type | Description |
|---|---|---|---|
| 1 | `$key` | `string` | Dot-notation key |
| 2 | `$defaults` or `$iterator` | `array\|string` | Defaults array, or iterator name if skipping defaults |
| 3 | `$meta` | `array` | Optional. `['label' => [...]]` for dashboard display |
| 4 | `$iterator` | `string` | Optional. Variable name for each item in the loop |

---

### `@endforeach_settings`

Closes both `@foreach_settings` and `@foreach_translatable_settings` loops.

```blade
@foreach_settings('home.features', [...])
    {{-- loop body --}}
@endforeach_settings
```

---

## Entry Types

| Type | Blade Directive | PHP Enum |
|---|---|---|
| Single string | `@settings` | `SettingEntryType::Single` |
| Translatable string | `@translatable_settings` | `SettingEntryType::SingleTranslatable` |
| List | `@foreach_settings` | `SettingEntryType::List` |
| Translatable list | `@foreach_translatable_settings` | `SettingEntryType::ListTranslatable` |

---

## PHP API

You can also interact with settings directly via the `SettingsService` class, which is bound as a singleton in the container.

### Resolving the Service

```php
use Statica\SettingsService;

// via dependency injection
public function index(SettingsService $settings) { ... }

// via the container
$settings = app(SettingsService::class);
```

### `getValue`

```php
// single value
$value = $settings->getValue('app.name');

// translatable value for a specific locale
$value = $settings->getValue('app.tagline', 'en', SettingEntryType::SingleTranslatable);

// list
$list = $settings->getValue('home.features', null, SettingEntryType::List);

// translatable list for a specific locale
$list = $settings->getValue('home.services', 'ar', SettingEntryType::ListTranslatable);
```

### `setValue`

```php
$settings->setValue('app.name', 'My App');

$settings->setValue('app.tagline', 'Welcome', 'en', SettingEntryType::SingleTranslatable);

$settings->setValue('home.features', ['label' => 'Fast'], null, SettingEntryType::List);

// persist to disk
$settings->updateData();
```

### `getOrCreate`

Retrieves a value if it exists, or creates it with the given default and persists it:

```php
$value = $settings->getOrCreate('app.name', 'My App');

$value = $settings->getOrCreate('app.tagline', [
    'en' => 'Welcome',
    'ar' => 'أهلاً',
], type: SettingEntryType::SingleTranslatable);
```

### `setLabel`

Sets the dashboard display label for a key:

```php
$settings->setLabel('app.name', [
    'en' => 'Application Name',
    'ar' => 'اسم التطبيق',
]);
```

### `deleteEntry`

```php
$settings->deleteEntry('app.name');
```

### `getAll`

```php
$all = $settings->getAll(); // returns the full settings array
```

---

## Dot Notation

All keys support unlimited nesting via dot notation:

```blade
@settings('company.address.city', 'Cairo')
@settings('company.address.country', 'Egypt')
```

```php
$settings->getValue('company.address.city'); // "Cairo"
$settings->deleteEntry('company.address.city');
```

---

## Dashboard

Statica ships with a built-in dashboard for editing settings at runtime, automatically available at:

```
/statica/dashboard
```

The dashboard is registered automatically by the service provider — no route registration needed. It supports inline editing, translatable fields, and drag-and-drop list reordering.

### Customizing the Dashboard Views

After publishing the views:

```bash
php artisan vendor:publish --tag=statica-views
```

The views will be placed at `resources/views/vendor/statica/`. Laravel will automatically use your overrides instead of the package defaults.

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

---

## Security

If you discover any security related issues, please email the author directly instead of using the issue tracker.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
