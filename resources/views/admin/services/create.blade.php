@extends('layouts.admin')

@section('header_title', 'Create New Service')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="/admin/services" class="text-xs text-muted-foreground hover:text-primary flex items-center gap-1 font-semibold">
            ← Back to Services Catalog
        </a>
    </div>

    <div class="border border-border bg-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-border pb-4">
            <h2 class="font-heading font-800 text-xl text-foreground">Add New Service</h2>
            <p class="text-xs text-muted-foreground mt-1">Add a new automotive repair or maintenance offering to the public catalog.</p>
        </div>

        <form action="/admin/services" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">
                    Service Name <span class="text-primary">*</span>
                </label>
                <input type="text" name="name" required placeholder="e.g. Brake Pad & Rotor Replacement" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Category</label>
                    <input type="text" name="category" value="Maintenance" placeholder="e.g. Brakes, Engine, Diagnostics" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Price Description</label>
                    <input type="text" name="price" placeholder="e.g. $189.99 or Custom Quote" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Estimated Duration</label>
                    <input type="text" name="duration" value="1 - 2 hours" placeholder="e.g. 1 hr, 2-3 hours" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div class="flex items-center gap-6 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-foreground">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-border text-primary focus:ring-primary w-4 h-4">
                        <span>Featured on Homepage</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-foreground">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-border text-primary focus:ring-primary w-4 h-4">
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Short Summary</label>
                <input type="text" name="short_description" placeholder="Brief 1-sentence summary displayed in service cards" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Full Description</label>
                <textarea name="description" rows="4" placeholder="Detailed service description, what is included, warranty details..." class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-border">
                <a href="/admin/services" class="text-xs text-muted-foreground hover:text-foreground">Cancel</a>
                <button type="submit" class="bg-primary text-primary-foreground font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors">
                    Save Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
