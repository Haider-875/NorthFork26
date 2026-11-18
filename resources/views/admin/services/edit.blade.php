@extends('layouts.admin')

@section('header_title', 'Edit Service')

@section('content')
<div class="max-w-2xl border border-border bg-card p-6 sm:p-8 space-y-6">
    <h2 class="font-heading font-800 text-xl">Edit Service</h2>
    <form action="/admin/services/{{ $service['id'] ?? 1 }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Service Name</label>
            <input type="text" name="name" value="{{ $service['name'] ?? '' }}" required class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Category</label>
            <input type="text" name="category" value="{{ $service['category'] ?? '' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Price Description</label>
            <input type="text" name="price" value="{{ $service['price'] ?? '' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full bg-background border border-border px-4 py-2.5 text-sm">{{ $service['description'] ?? '' }}</textarea>
        </div>
        <button type="submit" class="bg-primary text-white font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90">Update Service</button>
    </form>
</div>
@endsection
