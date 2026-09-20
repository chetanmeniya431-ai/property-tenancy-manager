<?php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'signals' => Signal::orderBy('name')->get(),
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function toggleSignal(Request $request, Signal $signal)
    {
        $signal->update(['active' => ! $signal->active]);

        return back()->with('status', "{$signal->name} is now ".($signal->active ? 'active' : 'inactive').'.');
    }

    public function storeUser(Request $request)
    {
        abort_unless($request->user()->hasRole(Roles::OWNER), 403, 'Only the Property Owner can manage users.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', Roles::ALL)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole($data['role']);

        return back()->with('status', "User {$user->name} created as ".Roles::LABELS[$data['role']].'.');
    }
}
