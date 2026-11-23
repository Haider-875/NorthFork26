@extends('layouts.admin')

@section('header_title', 'Manage Services')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#BDEBFA] gap-4">
        <div>
            <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— CATALOG OF WORK —</span>
            <h2 class="text-2xl sm:text-3xl font-heading font-extrabold text-[#071F35] tracking-tight mt-0.5">Shop Services Catalog</h2>
            <p class="text-xs text-[#334E68] mt-1 font-mono">Manage shop repair capabilities and published services</p>
        </div>
        <a href="/admin/services/create" class="btn-navy-pill text-xs px-5 py-2.5 shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Service
        </a>
    </div>

    <div class="frosted-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA] font-mono text-[#071F35] bg-[#D8F1FA]/60">
                    <tr>
                        <th class="p-4 font-bold">Service Name</th>
                        <th class="p-4 font-bold">Category</th>
                        <th class="p-4 font-bold">Price</th>
                        <th class="p-4 font-bold">Est. Duration</th>
                        <th class="p-4 font-bold">Featured</th>
                        <th class="p-4 text-right font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/40 bg-white/70">
                    @forelse($services ?? [] as $s)
                        <tr class="hover:bg-[#EBF7FD]/80 transition-colors">
                            <td class="p-4 font-heading font-bold text-[#071F35] text-sm">{{ $s['name'] }}</td>
                            <td class="p-4 font-mono text-[#334E68] font-medium">{{ $s['category'] ?? 'General' }}</td>
                            <td class="p-4 font-mono text-[#087DB5] font-bold text-sm">{{ $s['price'] ?? '$0.00' }}</td>
                            <td class="p-4 font-mono text-[#334E68]">{{ $s['duration'] ?? '1 hr' }}</td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-3 py-1 font-mono text-[10px] font-bold uppercase rounded-full {{ !empty($s['featured']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    {{ !empty($s['featured']) ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="/admin/services/{{ $s['id'] }}/edit" class="btn-white-pill text-[11px] px-3.5 py-1.5 shadow-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-[#334E68] font-mono">No services configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection