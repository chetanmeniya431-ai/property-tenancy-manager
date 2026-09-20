<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · Property Tenancy Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full items-center justify-center font-sans text-slate-900 antialiased">
    <div class="w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <h1 class="text-lg font-semibold text-sky-700">Property Tenancy Manager</h1>
            <p class="mt-1 text-sm text-slate-500">Hartwell Property Management</p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div class="text-right">
                    <a href="{{ route('password.request') }}" class="text-xs text-sky-600 hover:text-sky-700">Forgot password?</a>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-500">
                    Log in
                </button>
            </form>
        </div>

        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4 text-xs text-slate-500">
            <p class="mb-2 font-medium text-slate-700">Demo logins (password: <code>password</code>)</p>
            <ul class="space-y-0.5">
                <li>admin@propertymanager.local — Property Owner</li>
                <li>manager@propertymanager.local — Property Manager</li>
                <li>maintenance@propertymanager.local — Maintenance Coordinator</li>
                <li>contractor@propertymanager.local — Contractor</li>
                <li>tenant@propertymanager.local — Tenant</li>
            </ul>
        </div>
    </div>
</body>
</html>
