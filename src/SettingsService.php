<?php

namespace Statica;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App;

use function array_key_exists;
use function is_array;
use function array_unique;

class SettingNotFoundException extends Exception
{
    public function __construct(string $key)
    {
        parent::__construct("Setting key '{$key}' not found");
    }
}

class SettingTranslationNotFoundException extends Exception
{
    public function __construct(string $key, string $lang)
    {
        parent::__construct("the key $key didn't have a value of language $lang");
    }
}

class ListNotFoundException extends Exception
{
    public function __construct($key)
    {
        parent::__construct("the key $key didn't have a list value ");

    }
}

class SettingsService
{
    private array $settings = [];

    public function __construct(private SettingRepositoryType $type)
    {
        switch ($type) {
            case SettingRepositoryType::JsonFile:
                $this->settings = json_decode(Storage::disk(config('statica.disk'))->get('root.json'), true) ?? [];
                if (Storage::disk(config('statica.disk'))->exists('root.json')) {
                } else {
                    Storage::disk(config('statica.disk'))->put('root.json', json_encode([]));
                }
                break;
            default:
                throw new Exception("Repository type " . $type->name . " not found");
        }
    }

    public function getValue(string $key, string $lang = null, SettingEntryType $type = SettingEntryType::Single): mixed
    {

        $segments = explode('.', $key);
        $current = &$this->settings;

        foreach ($segments as $index => $segment) {
            if (!isset($current[$segment])) {
                throw new SettingNotFoundException($key);
            }

            $isLast = $index === array_key_last($segments);

            if (!$isLast) {
                $current = &$current[$segment]['value'];
            }
        }


        if (isset($current[$segment]['value'])) {

            switch ($type) {
                case SettingEntryType::Single:
                    if (!isset($current[$segment]['value']['string']))
                        throw new SettingNotFoundException($key);
                    return $current[$segment]['value']['string'];
                case SettingEntryType::SingleTranslatable:
                    if (!isset($current[$segment]['value']['translations'][$lang]))
                        throw new SettingTranslationNotFoundException($key, $lang);
                    return $current[$segment]['value']['translations'][$lang];
                case SettingEntryType::List:
                    if (!isset($current[$segment]['value']['list']))
                        throw new ListNotFoundException($key);
                    return $current[$segment]['value']['list'];
                case SettingEntryType::ListTranslatable:
                    if (!isset($current[$segment]['value']['list']))
                        throw new ListNotFoundException($key);
                    return array_map(function ($entry) use ($lang) {
                        $entry['label'] = $entry['label'][$lang] ?? $entry['label'];
                        return $entry;
                    }, $current[$segment]['value']['list']);
                default:
                    break;
            }

            throw new SettingNotFoundException($key);
        }


        if (is_array($current[$segment]['value'])) {
            throw new SettingNotFoundException($key);
        }

        return $current[$segment]['value'];
    }

    public function setValue(string $key, array|string|null $value, string|null $lang = null, SettingEntryType $type = SettingEntryType::Single): void
    {
        $segments = explode('.', $key);
        $current = &$this->settings;

        foreach ($segments as $index => $segment) {
            $isLast = $index === array_key_last($segments);

            if (!isset($current[$segment])) {
                $current[$segment] = [
                    'meta' => ['label' => ['en' => $segment, 'ar' => $segment]],
                    'value' => [],
                ];
            }

            if ($isLast) {
                switch ($type) {
                    case SettingEntryType::Single:
                        $current[$segment]['value']['string'] = $value ?? $segment;
                        break;
                    case SettingEntryType::SingleTranslatable:
                        $existingValue = $current[$segment]['value']['translations'] ?? [];
                        if (is_array($value))
                            $current[$segment]['value']['translations'] = [...$existingValue, ...$value];
                        break;
                    case SettingEntryType::List:
                    case SettingEntryType::ListTranslatable:
                        foreach ($value as $key => $item) {
                            $current[$segment]['value']['list'][$key] = $item;
                        }
                        break;
                    default:
                        break;
                }
            }

            $current = &$current[$segment]['value'];
        }
    }

    public function updateData()
    {
        $this->writeSettings();
    }

    private function writeSettings(): void
    {
        if (!Storage::disk(config('statica.disk'))->exists('root.json')) {
            throw new Exception("root settings file root.json not found");
        }

        Storage::disk(config('statica.disk'))->put('root.json', json_encode($this->settings, JSON_PRETTY_PRINT));
    }

    public function getOrCreate(string $key, array|string|null $default = '', string|null $lang = null, SettingEntryType $type = SettingEntryType::Single)
    {
        try {
            return $this->getValue($key, $lang, $type);
        } catch (SettingNotFoundException) {
            switch ($type) {
                case SettingEntryType::Single:
                    $this->setValue($key, $default, null, $type);
                    break;
                case SettingEntryType::SingleTranslatable:
                    $this->setValue($key, $default, null, $type);
                    break;
                default:
                    break;
            }
        } catch (ListNotFoundException) {
            switch ($type) {
                case SettingEntryType::List:
                    $this->setValue($key, $default, null, $type);
                    $this->writeSettings();
                    break;
                case SettingEntryType::ListTranslatable:
                    $this->setValue($key, $default, $lang, $type);
                    $this->writeSettings();
                    break;
                default:
                    break;
            }
        }

        return $this->getValue($key, $lang, $type);
    }

    public function setLabel(string $key, array $value): void
    {
        $segments = explode('.', $key);
        $current = &$this->settings;

        foreach ($segments as $index => $segment) {
            $isLast = $index === array_key_last($segments);

            if (!isset($current[$segment])) {
                $current[$segment] = [
                    'meta' => ['label' => ['en' => $segment, 'ar' => $segment]],
                    'value' => [],
                ];
            }

            if ($isLast) {
                if (is_array($value))
                    foreach ($value as $locale => $localeValue)
                        $current[$segment]['meta']['label'][$locale] = $localeValue;
            }

            $current = &$current[$segment]['value'];
        }
    }

    public function deleteEntry(string $key): void
    {
        $segments = explode('.', $key);
        $current = &$this->settings;

        foreach ($segments as $index => $segment) {
            $isLast = $index === array_key_last($segments);

            if (!isset($current[$segment])) {
                return;
            }

            if ($isLast) {
                unset($current[$segment]);
                return;
            }

            if (!is_array($current[$segment]['value'] ?? null)) {
                return;
            }

            $current = &$current[$segment]['value'];
        }
    }

    public function getAll(): array
    {
        return $this->settings;
    }

    public function generateDashboard(): string
    {
        $html = '';
        foreach ($this->settings as $key => $entry) {
            $html .= $this->generate($entry, $key, 0);
        }
        return $html;
    }

    private function generate(array $entry, string $path = '', int $depth = 0): string
    {
        if (!array_key_exists('value', $entry)) {
            throw new Exception("missing 'value' key at path '$path': " . json_encode($entry));
        }

        $entryInput = '';
        $value = $entry['value'];
        $type = $this->resolveType($value);

        switch ($type) {
            case SettingEntryType::Single:
                $entryInput = view('statica::components.single', ['path' => $path, 'value' => $value['string']])->render();
                break;

            case SettingEntryType::SingleTranslatable:
                $entryInput = view('statica::components.single-translatable', ['path' => $path, 'translations' => $value['translations']])->render();
                break;

            case SettingEntryType::List:
                $entryInput = view('statica::components.list', ['path' => $path, 'list' => $value['list']])->render();
                break;

            case SettingEntryType::ListTranslatable:
                $entryInput = view('statica::components.list-translatable', ['path' => $path, 'list' => $value['list']])->render();
                break;

            default:
                // no leaf value — recurse into nested children
                foreach ($value as $key => $child) {
                    if (is_array($child) && array_key_exists('meta', $child) && array_key_exists('value', $child)) {
                        $childPath = $path ? "$path.$key" : $key;
                        $entryInput .= $this->generate($child, $childPath, $depth + 1);
                    }
                }
                break;
        }

        return view('statica::components.entry-wrapper', [
            'indent' => $depth,
            'path' => $path,
            'label' => $entry['meta']['label'][App::getLocale()] ?? reset($entry['meta']['label']),
            'content' => $entryInput,
        ])->render();
    }
    public function resolveType(array $value): ?SettingEntryType
    {
        if (isset($value['string'])) {
            return SettingEntryType::Single;
        }

        if (isset($value['translations']) && is_array($value['translations'])) {
            return SettingEntryType::SingleTranslatable;
        }

        if (isset($value['list']) && is_array($value['list'])) {
            // check if list items have translatable labels
            $firstItem = $value['list'][0] ?? null;
            if ($firstItem && isset($firstItem['label']) && is_array($firstItem['label'])) {
                return SettingEntryType::ListTranslatable;
            }
            return SettingEntryType::List;
        }

        return null; // nested group, not a leaf
    }
}
