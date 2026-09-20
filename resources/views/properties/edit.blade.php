<x-layout title="Edit property">
    <h1 class="mb-6 text-xl font-semibold text-slate-900">Edit property</h1>
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('properties.update', $property) }}">
            @method('PUT')
            @include('properties._form')
        </form>
    </div>
</x-layout>
