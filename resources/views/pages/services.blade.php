@extends('layouts.app')

@section('title', 'Complete Automotive Service & Repair | North Fork Auto')
@section('description', 'From preventive maintenance to major repairs, North Fork Auto keeps your vehicle running safely and reliably, with honest recommendations and quality workmanship in Talkeetna, Alaska.')

@section('content')
<div class="pt-24">
    <!-- Header Section -->
    <section class="section-pad py-16 border-b border-border">
        <div class="max-w-3xl">
            <div class="font-mono-data flex items-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Our Services</span>
            </div>
            <h1 class="font-heading font-extrabold font-800 text-3xl sm:text-5xl lg:text-6xl leading-[1.05] tracking-tight text-foreground">
                Complete Automotive Service &amp; Repair
            </h1>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                From preventive maintenance to major repairs, North Fork Auto keeps your vehicle running safely and reliably, with honest recommendations and quality workmanship.
            </p>
        </div>
    </section>

    <!-- Services Grid by Category -->
    <div class="section-pad py-16 space-y-16">
        <!-- Category: PREVENTIVE MAINTENANCE -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">PREVENTIVE MAINTENANCE</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Oil Change</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Full-service oil change with premium oil and filter replacement to keep your engine protected and running clean.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Dashboard oil light on</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Engine running louder than usual</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>It's been 5,000+ miles since last change</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Oil%20Change" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Fluid Service</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Inspection and replacement of vital fluids including coolant, transmission, brake, and power steering fluid.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Low fluid warning</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Discolored or burnt-smelling fluid</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Hard shifting or spongy brakes</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Fluid%20Service" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Filter Replacement</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Air and cabin filter replacement to protect your engine and keep the air inside your vehicle clean.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Reduced airflow from vents</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Musty cabin odor</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Decreased fuel economy</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Filter%20Replacement" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Scheduled Maintenance</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Manufacturer-recommended maintenance to preserve performance, safety, and warranty coverage.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Maintenance reminder light on</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Approaching mileage milestone</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Due for 30k/60k/90k service</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Scheduled%20Maintenance" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: BRAKES -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">BRAKES</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Brake Inspection</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Thorough inspection of pads, rotors, calipers, and lines to ensure safe, reliable stopping.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Squealing or grinding noise</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Soft or spongy pedal</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Steering wheel vibration when braking</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Brake%20Inspection" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Brake Pad Replacement</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Quality pad replacement to restore stopping power and prevent rotor damage.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Squeaking when braking</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Longer stopping distance</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Brake pad warning light</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Brake%20Pad%20Replacement" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Brake Rotor Service</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Resurfacing or replacement of rotors to eliminate pulsation and restore smooth braking.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Pedal pulsation</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Steering wheel shake</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Grooved or scored rotors</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Brake%20Rotor%20Service" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Brake Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Complete brake system repair including calipers, lines, and master cylinder service.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Brake fluid leak</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Brake pedal sinks to floor</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Illuminated brake warning</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Brake%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: ENGINE & DIAGNOSTICS -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">ENGINE & DIAGNOSTICS</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Check Engine Diagnostics</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Advanced scan and diagnosis to identify the cause behind your check engine light.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Check engine light on</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Rough idle</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Failed emissions test</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Check%20Engine%20Diagnostics" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Engine Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Expert engine repair from minor fixes to major overhauls, performed to exacting standards.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Loss of power</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Knocking or tapping noise</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Excessive exhaust smoke</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Engine%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Computer Diagnostics</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Full electronic diagnostic scan of all vehicle systems to pinpoint issues fast.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Multiple warning lights</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Intermittent drivability issues</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Unexplained performance drop</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Computer%20Diagnostics" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Performance Issues</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Diagnosis and repair of hesitation, rough running, and reduced fuel economy.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Hesitation on acceleration</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Stalling</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Poor fuel economy</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Performance%20Issues" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: TIRES & ALIGNMENT -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">TIRES & ALIGNMENT</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Tire Installation</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Professional tire mounting, balancing, and installation for a smooth, safe ride.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Frequent vibration</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Uneven tire wear</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>New tires purchased</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Tire%20Installation" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Tire Rotation</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Regular rotation to extend tire life and ensure even wear across all four wheels.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Uneven tread wear</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Every 5,000â7,000 miles</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Vehicle pulling to one side</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Tire%20Rotation" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Wheel Alignment</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Precision alignment to correct handling, reduce tire wear, and improve fuel economy.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Steering wheel off-center</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Vehicle pulls left or right</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Uneven tire wear</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Wheel%20Alignment" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Tire Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Safe, durable repair of punctures when the tire is still serviceable.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Slow leak</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Tire pressure warning</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Visible puncture</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Tire%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: ELECTRICAL -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">ELECTRICAL</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="2" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="22"/><path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/><path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Battery Replacement</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Testing and replacement of weak or failed batteries with reliable, long-lasting units.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Slow engine crank</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Dashboard lights dim</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Battery warning light</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Battery%20Replacement" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Alternator</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Alternator testing and replacement to keep your battery charged and electrical system running.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Battery keeps dying</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Dimming headlights</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Whining noise from engine</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Alternator" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Starter</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Starter inspection and replacement to get your engine turning over reliably.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Clicking when turning key</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Engine won't start</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Intermittent starting</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Starter" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Electrical Diagnostics</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Diagnosis of wiring, sensor, and module issues across your vehicle's electrical system.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Flickering lights</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Blown fuses repeatedly</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Accessory not working</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Electrical%20Diagnostics" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: AC & HEATING -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">AC & HEATING</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">AC Diagnostics</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Full inspection of your air conditioning system to find leaks and cooling issues.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>AC blowing warm</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Weak airflow</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Strange odors from vents</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=AC%20Diagnostics" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="2" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="22"/><path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/><path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">AC Recharge</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Recharge of refrigerant to restore cold, comfortable cabin air.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>AC not as cold as before</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Takes long to cool down</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Low refrigerant warning</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=AC%20Recharge" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Heating Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Diagnosis and repair of heater core, thermostat, and blower issues.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>No heat from vents</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Engine running cold</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Sweet smell inside cabin</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Heating%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="2" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="22"/><path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/><path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Climate Control</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Repair of electronic climate control systems for consistent cabin comfort.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Temperature won't adjust</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Fan stuck on one speed</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Defroster not working</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Climate%20Control" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: TRANSMISSION -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">TRANSMISSION</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">3 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Transmission Service</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Fluid exchange and filter service to extend transmission life and smooth shifting.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Rough or delayed shifts</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Transmission fluid dark/burnt</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Maintenance interval reached</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Transmission%20Service" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Transmission Diagnostics</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Computer and pressure diagnostics to pinpoint transmission problems.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Check engine or trans light</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Slipping gears</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Grinding noise</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Transmission%20Diagnostics" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Transmission Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Repair and rebuild services for manual and automatic transmissions.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Won't go into gear</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Leaking fluid</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Burning smell</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Transmission%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: SUSPENSION & STEERING -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">SUSPENSION & STEERING</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">3 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Shocks & Struts</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Replacement of worn shocks and struts to restore ride comfort and control.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Bouncy ride</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Nose dive when braking</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Uneven tire wear</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Shocks%20%26%20Struts" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Suspension Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Repair of bushings, control arms, and mounts for stable, confident handling.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Clunking over bumps</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Wandering steering</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Vehicle sits unevenly</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Suspension%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Steering Repair</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Diagnosis and repair of steering components including rack, pump, and tie rods.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Loose or stiff steering</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Power steering whine</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Steering wheel vibration</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Steering%20Repair" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category: GENERAL REPAIR -->
        <div>
            <div class="flex items-center gap-4 mb-8">
                <h2 class="font-heading font-extrabold font-800 text-2xl sm:text-3xl uppercase tracking-tight text-foreground">GENERAL REPAIR</h2>
                <span class="flex-1 h-px bg-border"></span>
                <span class="font-mono-data text-xs">4 services</span>
            </div>

            <div class="grid gap-px bg-border border border-border sm:grid-cols-2 lg:grid-cols-3">
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Belts</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Inspection and replacement of serpentine and timing belts to prevent breakdowns.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Squealing under hood</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Cracked or frayed belt</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Approaching replacement interval</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Belts" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Hoses</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Coolant and vacuum hose inspection and replacement to prevent leaks and overheating.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Coolant leak</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Bulging hose</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Engine running hot</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Hoses" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">Cooling System</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Radiator, thermostat, and water pump service to prevent overheating.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Engine overheating</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Coolant temperature warning</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Coolant loss</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=Cooling%20System" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/50 transition-colors">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center w-11 h-11 border border-border text-primary shrink-0 bg-background/50">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                            </div>
                            <h3 class="font-heading font-bold font-700 text-lg leading-tight text-foreground">General Vehicle Repairs</h3>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4">Honest, dependable repair for whatever your vehicle needs.</p>

                    <div class="mb-5">
                        <div class="font-mono-data mb-2 text-xs uppercase tracking-wider text-primary">Common Signs</div>
                        <ul class="space-y-1.5">
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Unsure what's wrong</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Multiple concerns</span>
                            </li>
                            <li class="flex gap-2 text-sm text-foreground/80">
                                <span class="text-primary mt-0.5 font-bold">›</span>
                                <span>Pre-purchase inspection</span>
                            </li>
                        </ul>
                    </div>
                    </div>
                    <div class="pt-4 border-t border-border/50 mt-auto">
                        <a href="/book?service=General%20Vehicle%20Repairs" class="btn-engine border border-border px-5 py-3 text-xs hover:border-primary hover:bg-primary hover:text-primary-foreground flex items-center justify-between text-foreground">
                            <span>Book This Service</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom CTA Banner -->
    <section class="relative py-20 overflow-hidden border-t border-border bg-card/40">
        <div class="section-pad relative z-10 text-center max-w-2xl mx-auto">
            <h2 class="font-heading font-extrabold font-800 text-3xl sm:text-4xl text-foreground mb-4">
                Not Sure What Your Vehicle Needs?
            </h2>
            <p class="text-base text-muted-foreground leading-relaxed mb-8">
                Bring it in. Our technicians will diagnose the issue and give you an honest recommendation.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/book" class="btn-engine bg-primary text-primary-foreground px-8 py-3.5 text-xs">
                    Schedule Diagnostic
                </a>
                <a href="tel:+19077333030" class="btn-engine border border-border bg-card/60 hover:border-primary text-foreground px-8 py-3.5 text-xs">
                    Call (907) 733-3030
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
