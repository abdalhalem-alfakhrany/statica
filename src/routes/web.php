<?php

use Illuminate\Support\Facades\Route;
use Statica\Controllers\SettingsEditorController;

Route::get('cases', function () {
    Storage::disk('settings')->put('root.json', "{}");
    $html = collect([
        <<<EOD
        <style>
            body {
            background-color: #2a2a2a;
            color: white;
            font-family: system-ui;
            }
            .code {
            background: #444;
            padding: 10px;
            border-radius: 10px;
            width: fit-content;
            white-space: pre;
            }
            .result {
            background: #888;
            padding: 10px;
            border-radius: 10px;
            width: fit-content;
        }
        </style>
        EOD,
        <<<EOD
        <form method="POST" action="{{ route('change_locale') }}" class="ml-4">
            @csrf
            <!-- Locale value -->
            <input type="hidden" name="locale" value="{{ app()->getLocale() == 'ar' ? 'en' : 'ar' }}">

            <button type="submit"
                class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 transition">
                switch locale to {{ app()->getLocale() == 'ar' ? 'EN' : 'AR' }}
            </button>
        </form>
        EOD,
        <<<EOD
        <span class="title">translatable with the default value</span><br>
        <p class="code">@@translatable_settings('name',['en' => 'abdalhalem', 'ar' => 'عبدالحليم'], ['label' => ['ar'=>'الاسم', 'en'=> 'the name' ]])<p>
        <p class="result">@translatable_settings('name',['en' => 'abdalhalem', 'ar' => 'عبدالحليم'], ['label' => ['ar'=>'الاسم', 'en'=> 'the name' ]])<p>
        EOD,
        <<<EOD
        <span class="title">nested</span><br>
        <p class="code">
            @@settings('user.name','abdalhalem' , ['label' => ['en'=>'Name','ar'=>'الاسم']]) <br/>
            @@settings('user.job','back end developer',['label' => ['en'=>'Job', 'ar'=>'الوظيفه']]) <br/>
        <p>
        <p class="result">
            @settings('user.name','abdalhalem' , ['label' => ['en' => 'Name' ,'ar' => 'الاسم']]) <br/>
            @settings('user.job','back end developer', ['label' => ['en' => 'Job' ,'ar' => 'الوظيفه']]) <br/>
        </p>
        EOD,
        <<<EOD
        <span class="title">list case</span><br>
        <p class="code">
            @@foreach_settings(
                'tech-stack',
                [['label' => 'PHP'],['label' => 'Laravel'],['label' => 'Js'],['label' => 'SQL']],
                [
                    'label' => [
                        'en' => 'Tech Stack',
                        'ar' => 'المهارات التقنيه',
                    ]
                ],
                'item'
                )
                @{{ \$item['label'] }} &lt;br/&gt;
            @@endforeach_settings
        </p>
        <p class="result">
            @foreach_settings(
                'tech-stack',
                [['label' => 'PHP'],['label' => 'Laravel'],['label' => 'Js'],['label' => 'SQL']],
                [
                    'label' => [
                    'en' => 'Tech Stack',
                    'ar' => 'المهارات التقنيه',
                    ]
                ],
                'item'
                )
                {{ \$item['label'] }} <br/>
            @endforeach_settings
        </p>
        <span class="title">could be reused again without default values if you are sure it's already set</span><br>
        <p class="code">
        @@foreach_settings('tech-stack','item')
            @{{ \$item['label'] }} &lt;br/&gt;
        @@endforeach_settings
        </p>
        <p class="result">
        @foreach_settings('tech-stack','item')
            {{ \$item['label'] }} <br/>
        @endforeach_settings
        </p>
        EOD,
        <<<EOD
        <span class="title">translatable list case</span><br>
        <p class="code">
            @@foreach_translatable_settings(
                'navbar',
                [
                    ['label' => ['en' => 'Home', 'ar' => 'الرئيسية'], 'link' => '/'],
                    ['label' => ['en' => 'About', 'ar' => 'عننا'],'link'=>'/about'],
                    ['label' => ['en' => 'Services', 'ar' => 'خدماتنا'], 'link'=>'/services'],
                    ['label' => ['en' => 'Contact', 'ar' => 'تواصل معنا'], 'link'=>'/contact']
                ],
                [
                    'label' => [
                    'en' => 'Website Navbar',
                    'ar' => 'شريط التنقل',
                    ]
                ],
                'item'
                )
                @{{ \$item['label'] }} <br/>
            @@endforeach_settings
        </P>
        <p class="result">
            @foreach_translatable_settings(
                'navbar',
                [
                    ['label' => ['en' => 'Home', 'ar' => 'الرئيسية'], 'link' => '/'],
                    ['label' => ['en' => 'About', 'ar' => 'عننا'],'link'=>'/about'],
                    ['label' => ['en' => 'Services', 'ar' => 'خدماتنا'], 'link'=>'/services'],
                    ['label' => ['en' => 'Contact', 'ar' => 'تواصل معنا'], 'link'=>'/contact']
                ],
                [
                    'label' => [
                    'en' => 'Website Navbar',
                    'ar' => 'شريط التنقل',
                    ]
                ],
                'item'
                )
                {{ \$item['label'] }} <br/>
            @endforeach_settings
        </p>
        EOD,
        <<<EOD
        <span style="font-size: 35px">list case</span><br>
            @foreach_translatable_settings('navbar','item')
                <br/>{{ \$item['label'] }}
            @endforeach_settings
        EOD,
    ])
        ->map(function ($html, $index) {
            try {
                return Blade::render($html);
            } catch (\Throwable $th) {
                return "error in case $index => " . $th->getMessage() . "<br><hr>";
            }
        })
        ->join("");

    return response($html)->header('Content-Type', 'text/html');
})->middleware('web');


Route::get('dashboard', [SettingsEditorController::class, 'show'])->middleware('web')->name('show');
