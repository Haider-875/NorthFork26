@extends('layouts.admin')

@section('header_title', 'Manage Services Catalog')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-heading font-800 text-2xl text-foreground">Services Catalog</h2>
            <p class="text-xs text-muted-foreground mt-1">Configure automotive repair offerings, standard pricing, and durations.</p>
        </div>
        <a href="/admin/services/create" class="btn-engine bg-primary text-primary-foreground font-bold px-5 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Service
        </a>
    </div>

    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Service Name</th>
                    <th class="py-3 px-4">Category</th>
                    <th class="py-3 px-4">Price Description</th>
                    <th class="py-3 px-4">Est. Duration</th>
                    <th class="py-3 px-4">Featured</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($services as $s)
                    <tr class="hover:bg-secondary/40 transition-colors">
                        <td class="py-3.5 px-4 font-heading font-700 text-foreground">
                            {{ $s->name }}
                            @if($s->short_description)
                                <div class="text-xs font-normal text-muted-foreground line-clamp-1 mt-0.5">{{ $s->short_description }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs text-muted-foreground">
                            {{ $s->category ?: 'General' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs text-primary font-bold">
                            {{ $s->price ?: 'Custom Quote' }}
                        </td>
                        <td class="py-3.5 px-4 text-xs text-muted-foreground">
                            {{ $s->duration ?: '1 hr' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->is_featured)
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold border border-primary/40 bg-primary/10 text-primary rounded-sm">Featured</span>
                            @else
                                <span class="text-xs text-muted-foreground/60">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->is_active)
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold border border-emerald-500/40 bg-emerald-500/10 text-emerald-400 rounded-sm">Active</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold border border-zinc-600 bg-zinc-800 text-zinc-400 rounded-sm">Hidden</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/services/{{ $s->id }}/edit" class="px-3 py-1.5 text-xs font-bold border border-border hover:border-primary text-foreground hover:text-primary transition-colors inline-block">
                                Edit
                            </a>
                            <form action="/admin/services/{{ $s->id }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 text-xs text-primary hover:text-white hover:bg-primary/20 transition-colors" title="Delete Service">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-muted-foreground">
                            No services found in the catalog. Click above to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
