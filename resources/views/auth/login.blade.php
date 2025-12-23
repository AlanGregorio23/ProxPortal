@extends('auth.auth')

@section('content')
    <div class="mb-8 space-y-1">
        <p class="text-sm text-slate-500">Bentornato</p>
        <h2 class="text-2xl font-bold text-slate-900">Accedi al tuo account</h2>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 text-red-700 border border-red-100 rounded-2xl">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.perform') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-semibold text-slate-800">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                   placeholder="nome@azienda.it" required>
        </div>

        <div>
            <label class="block mb-1 font-semibold text-slate-800">Password</label>
            <input type="password" name="password"
                   class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                   placeholder="••••••••" required>
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>Ricordami</span>
            </label>
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">Registrati</a>
        </div>

        <button class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold shadow-md hover:bg-blue-700 transition">
            Accedi
        </button>
    </form>
@endsection
