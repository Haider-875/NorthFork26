@extends('layouts.admin')

@section('header_title', 'Shop Configuration')

@section('content')
<div class="max-w-2xl border border-border bg-card p-6 sm:p-8 space-y-6">
    <h2 class="font-heading font-800 text-xl">General Shop Settings</h2>
    <form action="/admin/settings" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Shop Name</label>
            <input type="text" name="business_name" value="North Fork Auto" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Primary Phone</label>
            <input type="text" name="phone" value="(907) 733-3030" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Email Address</label>
            <input type="email" name="email" value="info@northforkauto.com" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <div>
            <label class="block font-heading font-600 text-xs uppercase mb-2">Address</label>
            <input type="text" name="address" value="Talkeetna Spur Road, Talkeetna, AK 99676" class="w-full bg-background border border-border px-4 py-2.5 text-sm">
        </div>
        <button type="submit" class="bg-primary text-white font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90">Save Settings</button>
    </form>
</div>
@endsection
