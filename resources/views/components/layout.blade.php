@php
    use App\Support\Roles;
    $user = auth()->user();
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Properties', 'route' => 'properties.index', 'icon' => 'building'],
        ['label' => 'Tenancies', 'route' => 'tenancies.index', 'icon' => 'document'],
        ['label' => 'Maintenance', 'route' => 'maintenance.index', 'icon' => 'wrench'],
        ['label' => 'Signals', 'route' => 'signals.index', 'icon' => 'bell'],
    ];
    if ($user && $user->hasAnyRole(Roles::BACK_OFFICE)) {
        $navItems[] = ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'chart'];
        $navItems[] = ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'cog'];
    }
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} · Property Tenancy Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <!-- Mobile top bar -->
        <div class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
            <button @click="sidebarOpen = true" class="rounded-md p-2 text-slate-600 hover:bg-slate-100">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
            <span class="text-sm font-semibold text-sky-700">Property Tenancy Manager</span>
            <span class="w-10"></span>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div class="fixed inset-y-0 left-0 w-72 bg-white p-4 shadow-xl">
                <div class="mb-6 flex items-center justify-between">
                    <span class="text-sm font-semibold text-sky-700">Property Tenancy Manager</span>
                    <button @click="sidebarOpen = false" class="rounded-md p-1 text-slate-500 hover:bg-slate-100">✕</button>
                </div>
                <x-nav :items="$navItems" />
            </div>
        </div>

        <div class="lg:flex">
            <!-- Desktop sidebar -->
            <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white px-4 py-6 lg:block">
                <div class="mb-8 px-2">
                    <span class="text-base font-semibold text-sky-700">Property Tenancy Manager</span>
                    <p class="mt-1 text-xs text-slate-500">Hartwell Property Management</p>
                </div>
                <x-nav :items="$navItems" />

                @if($user)
                <div class="mt-8 border-t border-slate-200 px-2 pt-4">
                    <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500">{{ \App\Support\Roles::LABELS[$user->roles->first()?->name] ?? '' }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button class="text-xs font-medium text-slate-500 hover:text-sky-700">Log out</button>
                    </form>
                </div>
                @endif
            </aside>

            <!-- Main content -->
            <main class="min-h-screen flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    @if(session('status'))
                        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
