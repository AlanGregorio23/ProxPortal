<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ProxmoxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index(ProxmoxService $proxmoxService)
    {
        if (Auth::user()->type !== 'admin') {
            return redirect()->route('requests.index');
        }

        $requests = ServiceRequest::with('user')->latest()->get();

        $requests->each(fn (ServiceRequest $request) => $proxmoxService->refreshIp($request));
        $users = User::orderBy('name')->get();

        return view('admin.dashboard', [
            'requests' => $requests,
            'users' => $users,
        ]);
    }
}
