@extends('layout')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-sm text-slate-500">Richieste di accesso</p>
            <h1 class="text-3xl font-bold text-slate-900">Le mie richieste</h1>
            <p class="text-sm text-slate-600 mt-1">Lo stato parte sempre come <span class="font-semibold">pending</span>.</p>
        </div>
        <a href="{{ route('requests.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700">Nuova richiesta</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-2xl text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left">
                <thead class="bg-slate-50 text-slate-600 text-sm">
                    <tr>
                        <th class="px-4 py-3">Profilo</th>
                        <th class="px-4 py-3">Stato</th>
                        <th class="px-4 py-3">Accesso</th>
                        <th class="px-4 py-3">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($requests as $request)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ ucfirst($request->profile) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'rejected' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$request->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if ($request->status === 'approved' && $request->hostname)
                                    <div class="space-y-2">
                                        <div class="space-y-1">
                                            <p><span class="font-semibold">Hostname:</span> {{ $request->hostname }}</p>
                                            <p><span class="font-semibold">Indirizzo:</span> {{ $request->ip_address ?? 'DHCP' }}</p>
                                            <p><span class="font-semibold">User:</span> {{ $request->username }}</p>
                                            <p><span class="font-semibold">Password:</span> {{ $request->password }}</p>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs">
                                            <a
                                                href="{{ route('ssh-key.download', $request) }}"
                                                class="inline-flex px-3 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800"
                                            >
                                                Scarica chiave SSH
                                            </a>
                                        </div>
                                    </div>

                                @elseif ($request->status === 'rejected')
                                    <span class="text-red-600 font-semibold">
                                    Container rifiutato
                                </span>

                                @else
                                    <span class="text-slate-500">
                                In attesa di approvazione
                            </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-slate-600">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Nessuna richiesta presente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
