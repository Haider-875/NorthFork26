@extends('layouts.admin')

@section('header_title', 'Shop Configuration & Settings')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="border border-border bg-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-border pb-4">
            <h2 class="font-heading font-800 text-xl text-foreground">General Shop Settings</h2>
            <p class="text-xs text-muted-foreground mt-1">Manage public contact details, hours, after-hours emergency phone, and site branding.</p>
        </div>

        <form action="/admin/settings" method="POST" class="space-y-6">
            @csrf

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Shop / Business Name</label>
                    <input type="text" name="business_name" value="{{ $settings['business_name'] ?? 'North Fork Auto' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>

                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Official Website Domain</label>
                    <input type="text" name="domain" value="{{ $settings['domain'] ?? 'www.northforkauto.com' }}" placeholder="www.northforkauto.com" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">
                        Primary Phone Number
                    </label>
                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '(907) 733-3030' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary font-mono-data">
                    <span class="text-[11px] text-muted-foreground mt-1 block">Main shop line (907) 733-3030</span>
                </div>

                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">
                        After Hours Queries Phone
                    </label>
                    <input type="text" name="after_hours_phone" value="{{ $settings['after_hours_phone'] ?? '+1 (907) 232-3859' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary font-mono-data">
                    <span class="text-[11px] text-primary mt-1 block font-semibold">Emergency &amp; after hours line</span>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">
                        Official Contact Email
                    </label>
                    <input type="email" name="email" value="{{ $settings['email'] ?? 'titussr84@yahoo.com' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                    <span class="text-[11px] text-muted-foreground mt-1 block">Inquiries will route here</span>
                </div>

                <div>
                    <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Shop Operating Hours</label>
                    <input type="text" name="hours" value="{{ $settings['hours'] ?? 'Mon – Fri: 8:00 AM – 5:00 PM' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Physical Shop Address</label>
                <input type="text" name="address" value="{{ $settings['address'] ?? 'Talkeetna Spur Road, Talkeetna, AK 99676' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block font-heading font-600 text-xs uppercase mb-2 text-foreground">Footer Attribution Credit</label>
                <input type="text" name="footer_credit" value="{{ $settings['footer_credit'] ?? 'Designed by Azora Solution · www.azorasolution.com' }}" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                <span class="text-[11px] text-muted-foreground mt-1 block">Credits displayed at the base of the site</span>
            </div>

            <div class="pt-4 border-t border-border flex justify-end">
                <button type="submit" class="bg-primary text-primary-foreground font-bold px-8 py-3 text-xs uppercase hover:bg-primary/90 transition-colors">
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
