<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        // Same response whether or not the address exists (prevents enumeration).
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If that email is registered, a reset link has been sent.');
    }
}
