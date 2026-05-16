<?php

namespace Statica;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Log;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/statica.php', 'statica');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'statica');
        $this->app->singleton(SettingsService::class, fn() => new SettingsService(config('statica.repository_type')));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/statica.php' => config_path('statica.php'),
        ], 'statica-settings-config');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/statica'),
        ], 'public');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->registerDirectives();
    }

    private function registerDirectives(): void
    {
        Blade::directive('settings', function ($expression) {
            return "<?php
                \$__args   = [{$expression}];
                \$__key    = \$__args[0];
                \$__default = \$__args[1] ?? null;
                \$__meta  = \$__args[2] ?? [];
                echo app(\Statica\SettingsService::class)->getOrCreate(\$__key, \$__default, null, \Statica\SettingEntryType::Single);
                app(\Statica\SettingsService::class)->setLabel(\$__key, \$__meta['label'] ?? []);
                app(\Statica\SettingsService::class)->updateData();
            ?>";
        });

        Blade::directive('translatable_settings', function ($expression) {
            return "<?php
                \$__args   = [{$expression}];
                \$__key    = \$__args[0];
                \$__defaults  = \$__args[1] ?? [];
                \$__meta  = \$__args[2] ?? [];
                echo app(\Statica\SettingsService::class)->getOrCreate(\$__key, \$__defaults, app()->getLocale(), \Statica\SettingEntryType::SingleTranslatable);
                app(\Statica\SettingsService::class)->setLabel(\$__key, \$__meta['label'] ?? []);
                app(\Statica\SettingsService::class)->updateData();
            ?>";
        });

        Blade::directive('foreach_settings', function ($expression) {
            return "<?php
                \$__args   = [{$expression}];
                \$__key    = \$__args[0];
                \$__defaults  = [];
                \$__meta  = [];
                \$__iterator = '';

                if (is_array(\$__args[1])) {
                    \$__defaults  = \$__args[1] ?? \$__defaults;
                    \$__meta  = \$__args[2] ?? \$__meta;
                    \$__iterator = \$__args[3] ?? \$__iterator;
                } else {
                    \$__iterator = \$__args[1] ?? \$__iterator;
                }

                app(\Statica\SettingsService::class)->setLabel(\$__key, \$__meta['label'] ?? []);
                app(\Statica\SettingsService::class)->updateData();
                foreach(app(\Statica\SettingsService::class)->getOrCreate(\$__key, \$__defaults, null, \Statica\SettingEntryType::List) as \$\$__iterator):
                ?>";
        });

        Blade::directive('foreach_translatable_settings', function ($expression) {
            return "<?php
                \$__args   = [{$expression}];
                \$__key    = \$__args[0];
                \$__defaults  = [];
                \$__meta  = [];
                \$__iterator = '';

                if (is_array(\$__args[1])) {
                    \$__defaults  = \$__args[1] ?? \$__defaults;
                    \$__meta  = \$__args[2] ?? \$__meta;
                    \$__iterator = \$__args[3] ?? \$__iterator;
                } else {
                    \$__iterator = \$__args[1] ?? \$__iterator;
                }

                app(\Statica\SettingsService::class)->setLabel(\$__key, \$__meta['label'] ?? []);
                app(\Statica\SettingsService::class)->updateData();
                foreach(app(\Statica\SettingsService::class)->getOrCreate(\$__key, \$__defaults, app()->getLocale(), \Statica\SettingEntryType::ListTranslatable) as \$\$__iterator):
                ?>";
        });

        Blade::directive('endforeach_settings', fn() => "<?php endforeach;?>");
    }
}
