<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProxPortal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-200 text-slate-900">
    <header class="bg-white/80 backdrop-blur shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">P</div>
                <div>
                    <p class="text-lg font-semibold">ProxPortal</p>
                </div>
            </div>
            @auth
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Bentornato</p>
                        <p class="font-semibold">{{ Auth::user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition">
                            Esci
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-10">
        @yield('content')
    </main>
</body>
</html>
