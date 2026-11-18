@extends('layouts.admin')

@section('header_title', 'Create Service')

@section('content')
<div class="max-w-2xl border border-border bg-card p-6 sm:p-8 space-y-6">
    <h2 class="font-heading font-800 text-xl">New Service</h2>
    <form action="/admin/services" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Service Name</label>
            <input type="text" name="name" required class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Category</label>
            <input type="text" name="category" value="Maintenance" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Price Description</label>
            <input type="text" name="price" placeholder="e.g. $89.99 or Custom Quote" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full bg-background border border-border px-4 py-2.5 text-sm"></textarea>
        </div>
        <button type="submit" class="bg-primary text-white font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90">Save Service</button>
    </form>
</div>
@endsection
