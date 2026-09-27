@extends('layouts.admin')

@section('header_title', 'Staff & Administrators')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-heading font-800 text-2xl text-foreground">Staff &amp; Users</h2>
            <p class="text-xs text-muted-foreground mt-1">Authorized personnel with access to the North Fork Auto admin panel.</p>
        </div>
        <a href="/admin/users/create" class="btn-engine bg-primary text-primary-foreground font-bold px-5 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Staff Member
        </a>
    </div>

    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Staff Member</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Member Since</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($users as $u)
                    <tr class="hover:bg-secondary/40 transition-colors">
                        <td class="py-3.5 px-4 font-heading font-700 text-foreground">
                            {{ $u->name }}
                            @if(auth()->id() === $u->id)
                                <span class="ml-2 px-2 py-0.5 text-[9px] uppercase font-bold bg-primary/20 text-primary border border-primary/40 rounded-sm">You</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-muted-foreground text-xs">
                            {{ $u->email }}
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs text-primary font-bold">
                            Administrator
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs text-muted-foreground">
                            {{ $u->created_at ? $u->created_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/users/{{ $u->id }}/edit" class="px-3 py-1.5 text-xs font-bold border border-border hover:border-primary text-foreground hover:text-primary transition-colors inline-block">
                                Edit
                            </a>
                            @if(auth()->id() !== $u->id)
                                <form action="/admin/users/{{ $u->id }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this staff user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1.5 text-xs text-primary hover:text-white hover:bg-primary/20 transition-colors" title="Delete User">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-muted-foreground">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
