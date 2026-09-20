<x-layout title="Add property">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Add property</h1>
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('properties.store') }}">
            @include('properties._form', ['property' => null])
        </form>
    </div>
</x-layout>
