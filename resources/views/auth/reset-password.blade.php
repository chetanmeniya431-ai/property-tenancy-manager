<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password · Property Tenancy Manager</title>
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

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="username"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-500">
                    Reset password
                </button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-sky-600 hover:text-sky-700">← Back to log in</a>
        </div>
    </div>
</body>
</html>
