<x-layout title="Add tenancy">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Add tenancy</h1>
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('tenancies.store') }}">
            @include('tenancies._form', ['tenancy' => null])
        </form>
    </div>
    <p class="mt-3 text-xs text-slate-500">You can upload the lease PDF from the tenancy page once it's created.</p>
</x-layout>
