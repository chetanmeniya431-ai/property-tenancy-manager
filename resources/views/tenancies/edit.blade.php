<x-layout title="Edit tenancy">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Edit tenancy</h1>
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('tenancies.update', $tenancy) }}">
            @method('PUT')
            @include('tenancies._form')
        </form>
    </div>
</x-layout>
