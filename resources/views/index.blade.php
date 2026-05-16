<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" >

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>dashboard</title>
    @if (env('STATICA_DEV'))
        <script type="module" src="http://localhost:5174/@@vite/client"></script>
        <script type="module" src="http://localhost:5174/resources/js/statica.js"></script>
        <link rel="stylesheet" href="http://localhost:5174/resources/css/statica.css">
        </link>
    @else
        <link rel="stylesheet" href="{{ asset('vendor/statica/build/style.css') }}">
        <script src="{{ asset('vendor/statica/build/statica.js') }}" defer></script>
    @endif
</head>

<body class="dark:bg-gray-600">
    <form method="POST" action="{{ route('change_locale') }}" class="ml-4">
        @csrf
        <input type="hidden" name="locale" value="{{ app()->getLocale() == 'ar' ? 'en' : 'ar' }}">
        <button type="submit" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 transition">
            switch locale to {{ app()->getLocale() == 'ar' ? 'EN' : 'AR' }}
        </button>
    </form>
    <div>
        {!! $content !!}
    </div>

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</body>

</html>
