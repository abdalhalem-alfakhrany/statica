<?php

namespace Statica\Controllers;

use App;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Statica\SettingEntryType;
use Statica\SettingsService;

class SettingsEditorController
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    public function updateSingleValue(Request $request)
    {
        $this->settingsService->setValue($request->input('path'), $request->input('value'), null, SettingEntryType::Single);
        $this->settingsService->updateData();
        return new Response(['success' => true, 'message' => '']);
    }
    public function updateListValue(Request $request)
    {
        $this->settingsService->setValue($request->input('path'), $request->input('value'), null, SettingEntryType::List);
        $this->settingsService->updateData();
        return new Response(['success' => true, 'message' => '']);
    }

    public function updateSingleTranslatableValue(Request $request)
    {
        $this->settingsService->setValue($request->input('path'), $request->input('value'), null, SettingEntryType::SingleTranslatable);
        $this->settingsService->updateData();
        return new Response(['success' => true, 'message' => '']);
    }

    public function updateLabel(Request $request)
    {
        $this->settingsService->setLabel($request->input('path'), [App::getLocale() => $request->input('value')]);
        $this->settingsService->updateData();
        return new Response(['success' => true, 'message' => '']);
    }

    public function show(Request $request)
    {
        return view('statica::index', ['content' => $this->settingsService->generateDashboard()]);
    }
}
