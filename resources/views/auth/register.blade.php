@extends('auth.auth')

@section('content')
    <div class="mb-8 space-y-1">
        <p class="text-sm text-slate-500">Nuovo utente</p>
        <h2 class="text-2xl font-bold text-slate-900">Crea il tuo account</h2>
        <p class="text-sm text-slate-500">Crea un profilo utente per inviare le richieste di accesso.</p>
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

    <form method="POST" action="{{ route('register.perform') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block mb-1 font-semibold text-slate-800">Nome e cognome</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                       placeholder="Mario Rossi" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold text-slate-800">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                       placeholder="mario.rossi@mail.it" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block mb-1 font-semibold text-slate-800">Password</label>
                <input type="password" name="password"
                       class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                       placeholder="••••••••" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold text-slate-800">Conferma password</label>
                <input type="password" name="password_confirmation"
                       class="w-full p-3 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                       placeholder="••••••••" required>
            </div>
        </div>

        <div class="flex items-center justify-between text-sm">
            <p class="text-slate-600">Hai già un account?
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">Accedi</a>
            </p>
        </div>

        <button class="w-full bg-slate-900 text-white py-3 rounded-xl font-semibold shadow-md hover:bg-slate-800 transition">
            Registrati
        </button>
    </form>
@endsection
