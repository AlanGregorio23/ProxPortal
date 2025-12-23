<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->type !== 'user') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('requests.index');
    }
}
