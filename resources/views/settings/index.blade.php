@php $isOwner = auth()->user()->hasRole(\App\Support\Roles::OWNER); @endphp
<x-layout title="Settings">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Settings</h1>

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Signals</h2>
        <div class="space-y-2">
            @foreach($signals as $signal)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3 text-sm shadow-sm">
                    <div>
                        <p class="font-medium text-slate-900">{{ $signal->name }}</p>
                        <p class="text-xs text-slate-500">Severity: {{ ucfirst($signal->severity) }}</p>
                    </div>
                    <form method="POST" action="{{ route('settings.signals.toggle', $signal) }}">
                        @csrf
                        <button class="rounded-md border px-3 py-1.5 text-xs font-medium
                            {{ $signal->active ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-500' }}">
                            {{ $signal->active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="mb-3 text-sm font-semibold text-slate-700">Users</h2>
        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Name</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Email</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-500">Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr>
                            <td class="px-4 py-2 text-slate-900">{{ $u->name }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $u->email }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ \App\Support\Roles::LABELS[$u->roles->first()?->name] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($isOwner)
            <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Add user</h3>
                <form method="POST" action="{{ route('settings.users.store') }}" class="grid gap-3 sm:grid-cols-4">
                    @csrf
                    <input name="name" placeholder="Name" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <input name="email" type="email" placeholder="Email" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <select name="role" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach(\App\Support\Roles::LABELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input name="password" type="password" placeholder="Temporary password" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="sm:col-span-4 rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-500">Create user</button>
                </form>
            </div>
        @endif
    </section>
</x-layout>
