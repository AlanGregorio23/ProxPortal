@extends('layout')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dashboard Admin</h1>
        </div>
        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold">Admin</span>
    </div>

    @if (session('status'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-2xl text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 text-red-700 border border-red-100 rounded-2xl text-sm">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Richieste totali</p>
            <p class="text-3xl font-bold text-slate-900">{{ $requests->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Pending</p>
            <p class="text-3xl font-bold text-amber-600">{{ $requests->where('status', 'pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Utenti</p>
            <p class="text-3xl font-bold text-slate-900">{{ $users->count() }}</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <p class="text-sm text-slate-500">Flusso richieste</p>
                    <p class="text-lg font-semibold">Approva o rifiuta</p>
                </div>
                <span class="self-start text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">Gestione</span>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                @forelse ($requests as $request)
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/40 flex flex-col gap-3">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                            <div class="space-y-1">
                                <p class="font-semibold text-slate-900">{{ ucfirst($request->profile) }}</p>
                                <p class="text-sm text-slate-500">Utente: {{ $request->user->name }} ({{ $request->user->email }})</p>
                                <p class="text-xs text-slate-500">Creata il {{ $request->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-center {{ $statusColors[$request->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 text-sm">
                            <form method="POST" action="{{ route('admin.requests.approve', $request) }}" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button class="w-full justify-center px-3 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ $request->status === 'approved' ? 'disabled' : '' }}>Approva</button>
                            </form>
                            <form method="POST" action="{{ route('admin.requests.reject', $request) }}" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button class="w-full justify-center px-3 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ $request->status === 'rejected' ? 'disabled' : '' }}>Rifiuta</button>
                            </form>
                        </div>

                        @if ($request->status === 'approved' && $request->hostname)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-600">
                                <div class="p-3 rounded-lg bg-white border border-slate-100 shadow-sm">
                                    <p class="font-semibold text-slate-800">Accesso</p>
                                    <p><span class="font-semibold">Hostname:</span> {{ $request->hostname }}</p>
                                    <p><span class="font-semibold">Indirizzo:</span> {{ $request->ip_address ?? 'DHCP' }}</p>
                                    <p><span class="font-semibold">User:</span> {{ $request->username }}</p>
                                    <p><span class="font-semibold">Password:</span> {{ $request->password }}</p>
                                    <a
                                        href="{{ route('ssh-key.download', $request) }}"
                                        class="inline-block mt-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800"
                                    >
                                        Scarica chiave SSH
                                    </a>
                                </div>
                                <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                    <p class="font-semibold text-emerald-800">Nodo Proxmox</p>
                                    <p><span class="font-semibold">Node:</span> {{ $request->node }}</p>
                                    <p><span class="font-semibold">VMID:</span> {{ $request->vmid }}</p>
                                    <p><span class="font-semibold">Provisionata il:</span>
                                        {{ optional($request->provisioned_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nessuna richiesta disponibile.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <p class="text-sm text-slate-500">Gestione utenti</p>
                    <p class="text-lg font-semibold">Aggiorna ruolo o elimina</p>
                </div>
                <span class="self-start text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">Admin</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($users as $user)
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/40 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->type === 'admin' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($user->type) }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-1 gap-2 text-sm">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="name" value="{{ $user->name }}" class="w-full p-2 border border-slate-200 rounded-lg" required>
                            <input type="email" name="email" value="{{ $user->email }}" class="w-full p-2 border border-slate-200 rounded-lg" required>
                            <select name="type" class="w-full p-2 border border-slate-200 rounded-lg" required>
                                <option value="user" {{ $user->type === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $user->type === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <button class="w-full px-3 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800">Aggiorna utente</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="text-right">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 text-sm font-semibold" onclick="return confirm('Eliminare questo utente?')">Elimina utente</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nessun utente disponibile.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
