@extends('layouts.admin')

@section('header_title', 'Staff & Users')

@section('content')
<div class="space-y-6">
    <h2 class="font-heading font-800 text-2xl">Staff &amp; Administrators</h2>
    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Name</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <tr class="hover:bg-secondary/40">
                    <td class="py-3 px-4 font-heading font-700">Administrator</td>
                    <td class="py-3 px-4">admin@northforkauto.com</td>
                    <td class="py-3 px-4 font-mono-data text-xs text-primary">Master Admin</td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/40">Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
