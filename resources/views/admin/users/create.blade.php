@extends('layouts.admin')

@section('header_title', 'Add Staff Member')

@section('content')
<div class="max-w-xl">
    <div class="mb-4">
        <a href="/admin/users" class="text-xs text-muted-foreground hover:text-primary flex items-center gap-1 font-semibold">
            ← Back to Staff Directory
        </a>
    </div>

    <div class="border border-border bg-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-border pb-4">
            <h2 class="font-heading font-800 text-xl text-foreground">Add New Staff Member</h2>
            <p class="text-xs text-muted-foreground mt-1">Provide login credentials for dashboard access.</p>
        </div>

        <form action="/admin/users" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Full Name <span class="text-primary">*</span></label>
                <input type="text" name="name" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Email Address <span class="text-primary">*</span></label>
                <input type="email" name="email" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Password <span class="text-primary">*</span></label>
                <input type="password" name="password" required minlength="6" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary" placeholder="At least 6 characters">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-border">
                <a href="/admin/users" class="text-xs text-muted-foreground hover:text-foreground">Cancel</a>
                <button type="submit" class="bg-primary text-primary-foreground font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
