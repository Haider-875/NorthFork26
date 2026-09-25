@extends('layouts.admin')

@section('header_title', 'Manage Services')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="font-heading font-800 text-2xl">Shop Services Catalog</h2>
        <a href="/admin/services/create" class="bg-primary text-white font-bold px-4 py-2 text-xs uppercase hover:bg-primary/90">+ Add Service</a>
    </div>

    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Service Name</th>
                    <th class="py-3 px-4">Category</th>
                    <th class="py-3 px-4">Price</th>
                    <th class="py-3 px-4">Est. Duration</th>
                    <th class="py-3 px-4">Featured</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($services ?? [] as $s)
                    <tr class="hover:bg-secondary/40">
                        <td class="py-3 px-4 font-heading font-700">{{ $s['name'] }}</td>
                        <td class="py-3 px-4 font-mono-data text-xs">{{ $s['category'] ?? 'General' }}</td>
                        <td class="py-3 px-4 font-mono-data text-primary">{{ $s['price'] ?? '$0.00' }}</td>
                        <td class="py-3 px-4 text-xs text-muted-foreground">{{ $s['duration'] ?? '1 hr' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 text-[10px] uppercase font-bold {{ !empty($s['featured']) ? 'bg-primary/10 text-primary border border-primary/40' : 'text-muted-foreground' }}">
                                {{ !empty($s['featured']) ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="/admin/services/{{ $s['id'] }}/edit" class="text-xs text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-muted-foreground">No services configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
