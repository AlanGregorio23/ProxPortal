<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProxPortal - Accesso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-100 flex items-center justify-center p-6">

            <div class="bg-white shadow-xl rounded-3xl p-12 border border-slate-100 space-y-8 w-full max-w-xl">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-600 font-semibold">Accesso</p>

                </div>

            </div>

            @yield('content')
</body>
</html>
