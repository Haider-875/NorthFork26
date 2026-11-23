@extends('layouts.admin')

@section('header_title', 'Shop Configuration')

@section('content')
<div class="space-y-8 max-w-3xl">
    <div class="border-b border-[#BDEBFA] pb-4 mb-6">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— PREFERENCES &amp; CONFIGURATION —</span>
        <h2 class="text-2xl sm:text-3xl font-heading font-extrabold text-[#071F35] tracking-tight mt-0.5">General Shop Settings</h2>
        <p class="text-xs text-[#334E68] mt-1 font-mono">Update shop contact numbers, physical location, and operating hours</p>
    </div>

    <form action="/admin/settings" method="POST" class="frosted-card p-6 sm:p-8 space-y-6">
        @csrf
        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-2">Shop Name</label>
                <input type="text" name="business_name" value="North Fork Auto" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl">
            </div>
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-2">Primary Phone</label>
                <input type="text" name="phone" value="(907) 733-3030" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl">
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-2">Email Address</label>
                <input type="email" name="email" value="titussr88@gmail.com" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl">
            </div>
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-2">Address</label>
                <input type="text" name="address" value="Talkeetna Spur Road, Talkeetna, AK 99676" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl">
            </div>
        </div>
        <div class="pt-4 flex justify-end">
            <button type="submit" class="btn-navy-pill text-xs px-6 py-2.5 shadow-md">Save Settings</button>
        </div>
    </form>
</div>
@endsection