@extends('layouts.app')

@section('title', 'North Fork Auto | Trusted Auto Repair in Talkeetna, Alaska')
@section('description', 'Professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.')

@section('content')
<!-- Hero Section -->
<section class="relative min-h-[100svh] flex flex-col justify-between">
    <img src="https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/9d7382181_generated_60d9ab56.jpg" alt="Luxury vehicle on a hydraulic lift inside the North Fork Auto workshop" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-background via-background/85 to-background/40"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-background/60"></div>
    
    <div class="relative section-pad pt-36 pb-16 flex-1 flex flex-col justify-center">
        <div class="max-w-3xl">
            <div class="font-mono-data flex items-center gap-3 mb-6">
                <span class="h-px w-10 bg-primary"></span>
                <span>Talkeetna, Alaska · Auto Repair Experts</span>
            </div>
            
            <h1 class="font-heading font-800 text-4xl sm:text-6xl lg:text-7xl leading-[1.02] tracking-tight text-balance text-foreground">
                Trusted Auto Repair.<br>
                <span class="text-primary">Quality Service.</span><br>
                Every Time.
            </h1>
            
            <p class="mt-6 text-base sm:text-xl text-muted-foreground leading-relaxed max-w-2xl">
                From routine maintenance to complex diagnostics, North Fork Auto provides professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.
            </p>
            
            <!-- Open/Closed Status Pill -->
            <div class="mt-8 border border-border bg-card/70 backdrop-blur px-6 py-4 max-w-2xl">
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="font-heading font-700 text-foreground">Currently Open</span>
                    </div>
                    <span class="font-mono-data text-foreground/80">Mon – Fri 8:00 AM – 5:00 PM</span>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                <a href="/book" class="btn-engine bg-primary text-primary-foreground px-8 py-4 text-sm">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    Book an Appointment
                </a>
                <a href="tel:+19077333030" class="btn-engine border border-foreground/30 bg-background/40 backdrop-blur hover:border-primary px-8 py-4 text-sm text-foreground">
                    <svg class="w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Call Now · (907) 733-3030
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Bar -->
<div class="border-b border-border bg-card/40">
    <div class="section-pad">
        <ul class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-border">
            <li class="flex items-center justify-center gap-3 py-5 px-3 text-center sm:text-left">
                <svg class="w-5 h-5 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span class="font-heading font-600 text-xs sm:text-sm uppercase tracking-wider text-foreground">ASE Certified Technicians</span>
            </li>
            <li class="flex items-center justify-center gap-3 py-5 px-3 text-center sm:text-left">
                <svg class="w-5 h-5 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 12V4a2 2 0 0 1 2-2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="m9 15 2 2 4-4"/></svg>
                <span class="font-heading font-600 text-xs sm:text-sm uppercase tracking-wider text-foreground">Honest, Transparent Pricing</span>
            </li>
            <li class="flex items-center justify-center gap-3 py-5 px-3 text-center sm:text-left">
                <svg class="w-5 h-5 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span class="font-heading font-600 text-xs sm:text-sm uppercase tracking-wider text-foreground">12-Month / 12k-Mi Warranty</span>
            </li>
            <li class="flex items-center justify-center gap-3 py-5 px-3 text-center sm:text-left">
                <svg class="w-5 h-5 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="font-heading font-600 text-xs sm:text-sm uppercase tracking-wider text-foreground">Locally Owned &amp; Operated</span>
            </li>
        </ul>
    </div>
</div>

<!-- Featured Services Grid (1px sharp border grid) -->
<section id="services" class="section-pad py-20 sm:py-28">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-12">
        <div>
            <div class="font-mono-data flex items-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Our Capabilities</span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
                Expert Auto Services
            </h2>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed max-w-2xl">
                Comprehensive repair and maintenance for all makes and models. Every job done right the first time.
            </p>
        </div>
        <a href="/services" class="btn-engine border border-border hover:border-primary px-6 py-3 text-xs shrink-0 self-start lg:self-end text-foreground">
            View All Services
            <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>

    <!-- 1px grid -->
    <div class="grid gap-px bg-border sm:grid-cols-2 lg:grid-cols-4 border border-border">
        @php
            $featuredServices = [
                ['title' => 'Diagnostic Inspection', 'cat' => 'Diagnostics', 'desc' => 'State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.', 'icon' => 'gauge'],
                ['title' => 'Brake Repair & Replacement', 'cat' => 'Brakes', 'desc' => 'Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.', 'icon' => 'disc'],
                ['title' => 'Oil & Filter Service', 'cat' => 'Maintenance', 'desc' => 'Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.', 'icon' => 'oil'],
                ['title' => 'Engine Repair & Overhaul', 'cat' => 'Engine', 'desc' => 'Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.', 'icon' => 'engine'],
                ['title' => 'Transmission Service', 'cat' => 'Drivetrain', 'desc' => 'Fluid exchange, filter replacements, clutch service, and transmission diagnostics.', 'icon' => 'cog'],
                ['title' => 'Steering & Suspension', 'cat' => 'Suspension', 'desc' => 'Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.', 'icon' => 'activity'],
                ['title' => 'Electrical & Battery', 'cat' => 'Electrical', 'desc' => 'Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.', 'icon' => 'zap'],
                ['title' => 'Cooling & AC Repair', 'cat' => 'Climate', 'desc' => 'Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.', 'icon' => 'thermometer']
            ];
        @endphp

        @foreach($featuredServices as $service)
            <div class="group relative bg-card p-6 sm:p-8 flex flex-col justify-between hover:bg-card/80 transition-all duration-200">
                <div>
                    <div class="w-12 h-12 border border-border bg-background flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-primary-foreground transition-colors mb-6">
                        @if($service['icon'] === 'gauge')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                        @elseif($service['icon'] === 'disc')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                        @elseif($service['icon'] === 'oil')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        @elseif($service['icon'] === 'engine')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        @elseif($service['icon'] === 'cog')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        @elseif($service['icon'] === 'activity')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        @elseif($service['icon'] === 'zap')
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        @else
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                        @endif
                    </div>
                    <div class="font-mono-data text-[10px] mb-2">{{ $service['cat'] }}</div>
                    <h3 class="font-heading font-700 text-lg text-foreground group-hover:text-primary transition-colors">
                        {{ $service['title'] }}
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                        {{ $service['desc'] }}
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                    <a href="/book?service={{ urlencode($service['title']) }}" class="font-heading font-700 text-xs text-primary uppercase tracking-wider flex items-center gap-1 group-hover:underline">
                        Book Service
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Why Choose Us -->
<section class="section-pad py-20 sm:py-28 border-y border-border bg-card/30">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
        <div class="relative order-2 lg:order-1">
            <img src="https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/c8a445e93_generated_05f79557.jpg" alt="North Fork Auto technician performing a vehicle diagnostic" class="w-full aspect-[4/3] object-cover border border-border">
            <div class="absolute -bottom-5 -right-5 hidden sm:block border border-border bg-card p-6 shadow-2xl max-w-xs">
                <div class="font-heading font-800 text-3xl text-primary mb-1">10+ Years</div>
                <p class="text-xs text-muted-foreground leading-relaxed">
                    Proudly serving Talkeetna, Trapper Creek, and the Mat-Su Borough with dependable auto care.
                </p>
            </div>
        </div>

        <div class="order-1 lg:order-2">
            <div class="font-mono-data flex items-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Why North Fork Auto</span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
                We Keep You Moving Forward
            </h2>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                Alaska roads and weather demand vehicles in top condition. Our certified technicians have the tools, expertise, and dedication to keep your vehicle reliable season after season.
            </p>

            <div class="mt-8 space-y-4">
                <div class="flex gap-4 p-4 border border-border bg-card/60">
                    <div class="w-8 h-8 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h4 class="font-heading font-700 text-sm uppercase tracking-wide text-foreground">Accurate Digital Diagnostics</h4>
                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">We pinpoint issues quickly using modern automotive diagnostic tools, saving you time and guesswork.</p>
                    </div>
                </div>

                <div class="flex gap-4 p-4 border border-border bg-card/60">
                    <div class="w-8 h-8 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h4 class="font-heading font-700 text-sm uppercase tracking-wide text-foreground">No Surprise Invoices</h4>
                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">We explain repairs in plain English, provide honest written estimates, and never perform unapproved work.</p>
                    </div>
                </div>

                <div class="flex gap-4 p-4 border border-border bg-card/60">
                    <div class="w-8 h-8 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h4 class="font-heading font-700 text-sm uppercase tracking-wide text-foreground">Fast Turnaround Times</h4>
                        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">We respect your schedule and get you back on Alaskan roads as quickly and safely as possible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section class="section-pad py-20 sm:py-28">
    <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Customer Reviews</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
            What Our Customers Say
        </h2>
        <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
            Real experiences from drivers across the Talkeetna area who trust North Fork Auto.
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="border border-border bg-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-1 text-primary mb-4">
                    @for($i=0; $i<5; $i++)
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-muted-foreground leading-relaxed italic">
                    "North Fork Auto diagnosed an electrical glitch in my truck that two other shops couldn't figure out. Honest, fair, and got it done fast."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                <div>
                    <h5 class="font-heading font-700 text-sm text-foreground">James R.</h5>
                    <span class="font-mono-data text-[10px]">Talkeetna, AK · 2019 Ford F-250</span>
                </div>
            </div>
        </div>

        <div class="border border-border bg-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-1 text-primary mb-4">
                    @for($i=0; $i<5; $i++)
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-muted-foreground leading-relaxed italic">
                    "Had our brakes done before winter. Super thorough inspection, explained everything clearly, and great price. Wouldn't take my Subaru anywhere else."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                <div>
                    <h5 class="font-heading font-700 text-sm text-foreground">Sarah M.</h5>
                    <span class="font-mono-data text-[10px]">Trapper Creek, AK · 2021 Subaru Outback</span>
                </div>
            </div>
        </div>

        <div class="border border-border bg-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-1 text-primary mb-4">
                    @for($i=0; $i<5; $i++)
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-muted-foreground leading-relaxed italic">
                    "Top-notch mechanics who know how to prep vehicles for Alaskan winter conditions. Quality parts, zero fluff, straight answers."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                <div>
                    <h5 class="font-heading font-700 text-sm text-foreground">David K.</h5>
                    <span class="font-mono-data text-[10px]">Willow, AK · 2018 Toyota Tacoma</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
@include('components.cta-banner')

<!-- Visit Us / Contact Section -->
<section class="section-pad py-20 sm:py-28 border-t border-border bg-card/30">
    <div class="max-w-3xl mb-12">
        <div class="font-mono-data flex items-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Visit Us</span>
        </div>
        <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
            Find Us &amp; Stop By
        </h2>
        <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
            Conveniently located on Talkeetna Spur Road. Walk-ins welcome during business hours.
        </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-10">
        <div class="space-y-6">
            <div class="grid sm:grid-cols-2 gap-4">
                <!-- Address Card -->
                <div class="border border-border bg-card p-6">
                    <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="font-mono-data text-[10px] mb-1">Our Location</div>
                    <h4 class="font-heading font-700 text-sm text-foreground">Shop Address</h4>
                    <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                        Talkeetna Spur Road<br>Talkeetna, AK 99676
                    </p>
                    <a href="https://maps.google.com/?q=Talkeetna+Spur+Road,+Talkeetna,+AK+99676" target="_blank" rel="noreferrer" class="mt-4 inline-flex items-center gap-1 font-heading font-700 text-xs text-primary hover:underline">
                        Get Directions
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                    </a>
                </div>

                <!-- Phone Card -->
                <div class="border border-border bg-card p-6">
                    <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div class="font-mono-data text-[10px] mb-1">Direct Line</div>
                    <h4 class="font-heading font-700 text-sm text-foreground">Give Us a Call</h4>
                    <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                        Questions or urgent repair requests? Call us directly.
                    </p>
                    <a href="tel:+19077333030" class="mt-4 inline-flex items-center gap-1 font-heading font-700 text-xs text-primary hover:underline">
                        (907) 733-3030
                    </a>
                </div>

                <!-- Email Card -->
                <div class="border border-border bg-card p-6">
                    <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <div class="font-mono-data text-[10px] mb-1">Electronic Mail</div>
                    <h4 class="font-heading font-700 text-sm text-foreground">Send an Email</h4>
                    <p class="mt-2 text-xs text-muted-foreground leading-relaxed">
                        For general inquiries and written quote estimates.
                    </p>
                    <a href="mailto:info@northforkauto.com" class="mt-4 inline-flex items-center gap-1 font-heading font-700 text-xs text-primary hover:underline">
                        info@northforkauto.com
                    </a>
                </div>

                <!-- Business Hours Card -->
                <div class="border border-border bg-card p-6">
                    <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="font-mono-data text-[10px] mb-1">Working Schedule</div>
                    <h4 class="font-heading font-700 text-sm text-foreground">Shop Hours</h4>
                    <ul class="mt-2 space-y-1 text-xs text-muted-foreground">
                        <li class="flex justify-between"><span>Mon – Fri</span><span class="font-mono-data text-foreground/80">8:00 AM – 5:00 PM</span></li>
                        <li class="flex justify-between"><span>Sat – Sun</span><span class="font-mono-data text-foreground/80">Closed</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Google Maps Embed (with dark invert styling) -->
        <div class="border border-border overflow-hidden bg-card min-h-[360px] relative">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30560.85210344795!2d-150.1444!3d62.3209!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x56cde9d17d84fcf1%3A0xb366aa081e7d80f0!2sTalkeetna%2C%20AK%2099676!5e0!3m2!1sen!2sus!4v1680000000000!5m2!1sen!2sus" 
                class="w-full h-full min-h-[360px] border-0" 
                style="filter: invert(0.92) hue-rotate(180deg) contrast(0.9);" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                title="North Fork Auto Location">
            </iframe>
        </div>
    </div>
</section>
@endsection
