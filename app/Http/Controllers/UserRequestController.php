<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ProxmoxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRequestController extends Controller
{
    public function index(ProxmoxService $proxmoxService)
    {
        if (Auth::user()->type !== 'user') {
            return redirect()->route('admin.dashboard');
        }

        $requests = Auth::user()->serviceRequests()->latest()->get();
        $requests->each(fn (ServiceRequest $request) => $proxmoxService->refreshIp($request));

        return view('user.requests.index', compact('requests'));
    }

    public function create()
    {
        if (Auth::user()->type !== 'user') {
            return redirect()->route('admin.dashboard');
        }

        return view('user.requests.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->type !== 'user') {
            return redirect()->route('admin.dashboard');
        }

        $data = $request->validate([
            'profile' => ['required', 'in:bronze,silver,gold'],
        ]);

        ServiceRequest::create([
            'user_id' => Auth::id(),
            'profile' => $data['profile'],
            'status' => ServiceRequest::STATUS_PENDING,
        ]);

        return redirect()->route('requests.index')->with('status', 'richiesta inviata correttamente.');
    }
}
