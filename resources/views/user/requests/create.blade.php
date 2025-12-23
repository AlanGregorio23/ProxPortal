@extends('layout')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-sm text-slate-500">Richieste di accesso</p>
            <h1 class="text-3xl font-bold text-slate-900">Nuova richiesta</h1>
            <p class="text-sm text-slate-600 mt-1">Scegli il profilo desiderato: bronze, silver o gold.</p>
        </div>
        <a href="{{ route('requests.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700">Vedi richieste</a>
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

    <form method="POST" action="{{ route('requests.store') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
        @csrf
        <div>
            <label class="block mb-2 font-semibold text-slate-800">Profilo richiesto</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach (['bronze' => 'Bronze', 'silver' => 'Silver', 'gold' => 'Gold'] as $value => $label)
                    <label class="flex items-center space-x-3 p-4 rounded-xl border border-slate-200 hover:border-blue-200 cursor-pointer">
                        <input type="radio" name="profile" value="{{ $value }}" class="text-blue-600 focus:ring-blue-500" {{ old('profile') === $value ? 'checked' : '' }} required>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $label }}</p>
                            <p class="text-sm text-slate-500">Accesso livello {{ strtolower($label) }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <button class="w-full md:w-auto px-6 py-3 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition">
            Invia richiesta
        </button>
    </form>
@endsection
