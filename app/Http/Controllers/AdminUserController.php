<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function update(UpdateUserRequest $request, User $user)
    {
        if (Auth::user()->type !== 'admin') {
            return redirect()->route('requests.index');
        }

        $data = $request->validated();

        $user->update($data);

        return redirect()->back()->with('status', 'utente aggiornato.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->type !== 'admin') {
            return redirect()->route('requests.index');
        }

        $user->delete();

        return redirect()->back()->with('status', 'utente eliminato.');
    }
}
