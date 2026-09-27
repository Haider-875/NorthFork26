@extends('layouts.admin')

@section('header_title', 'Edit Staff: ' . $user->name)

@section('content')
<div class="max-w-xl">
    <div class="mb-4">
        <a href="/admin/users" class="text-xs text-muted-foreground hover:text-primary flex items-center gap-1 font-semibold">
            ← Back to Staff Directory
        </a>
    </div>

    <div class="border border-border bg-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-border pb-4">
            <h2 class="font-heading font-800 text-xl text-foreground">Edit Staff Member: {{ $user->name }}</h2>
            <p class="text-xs text-muted-foreground mt-1">Update profile information and password.</p>
        </div>

        <form action="/admin/users/{{ $user->id }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Full Name <span class="text-primary">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Email Address <span class="text-primary">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">New Password (leave blank to keep current)</label>
                <input type="password" name="password" minlength="6" placeholder="Leave empty to keep unchanged" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-border">
                <a href="/admin/users" class="text-xs text-muted-foreground hover:text-foreground">Cancel</a>
                <button type="submit" class="bg-primary text-primary-foreground font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
