@extends('layouts.app')

@section('title', 'About Us | North Fork Auto - Talkeetna, Alaska')
@section('description', 'Learn more about North Fork Auto, our story, certified technicians, and our dedication to honest automotive care in Talkeetna, Alaska.')

@section('content')
<div class="pt-24">
    <!-- Story Section -->
    <section class="section-pad py-16 border-b border-border">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="font-mono-data flex items-center gap-2 mb-4">
                    <span class="h-px w-8 bg-primary"></span>
                    <span>Our Story</span>
                </div>
                <h1 class="font-heading font-800 text-3xl sm:text-5xl lg:text-6xl leading-[1.05] tracking-tight text-foreground">
                    Built on Trust, Driven by Craft
                </h1>
                <p class="mt-6 text-base sm:text-lg text-muted-foreground leading-relaxed">
                    North Fork Auto was founded on a simple commitment: deliver dependable automotive service that drivers in Talkeetna can rely on. From routine maintenance to complex repairs, we treat every vehicle with the precision and care it deserves.
                </p>
                <p class="mt-4 text-muted-foreground leading-relaxed">
                    As a locally owned shop, we know our reputation is built one vehicle, and one customer, at a time. That's why we stand behind every repair with clear communication, honest assessments, and quality replacement parts engineered for extreme Alaskan driving conditions.
                </p>
            </div>
            <div class="relative">
                <img src="https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/14df08a20_generated_292b163c.jpg" alt="North Fork Auto workshop bay and technicians" class="w-full aspect-[4/3] object-cover border border-border">
            </div>
        </div>
    </section>

    <!-- Key Stats Counters -->
    <section class="border-b border-border bg-card/40">
        <div class="section-pad py-12">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl text-primary">100%</div>
                    <div class="font-mono-data text-xs mt-2 text-foreground/80">Locally Owned &amp; Operated</div>
                </div>
                <div>
                    <div class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl text-primary">ASE</div>
                    <div class="font-mono-data text-xs mt-2 text-foreground/80">Certified Technicians</div>
                </div>
                <div>
                    <div class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl text-primary">5.0 ★</div>
                    <div class="font-mono-data text-xs mt-2 text-foreground/80">Customer Satisfaction</div>
                </div>
                <div>
                    <div class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl text-primary">12/12k</div>
                    <div class="font-mono-data text-xs mt-2 text-foreground/80">Warranty on All Repairs</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Commitments (4 Cards) -->
    <section class="section-pad py-20 sm:py-28">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Our Principles</span>
                <span class="h-px w-8 bg-primary"></span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
                Our Core Commitments
            </h2>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                What sets North Fork Auto apart is our steadfast commitment to integrity, craft, and community.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="border border-border bg-card p-6">
                <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3 class="font-heading font-700 text-base text-foreground mb-2">Honest Estimates</h3>
                <p class="text-xs text-muted-foreground leading-relaxed">We will never recommend a repair your vehicle doesn't actually need. Full transparent pricing every time.</p>
            </div>

            <div class="border border-border bg-card p-6">
                <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <h3 class="font-heading font-700 text-base text-foreground mb-2">Quality Parts</h3>
                <p class="text-xs text-muted-foreground leading-relaxed">We use OEM and premium aftermarket components designed to withstand rigorous sub-zero temperatures.</p>
            </div>

            <div class="border border-border bg-card p-6">
                <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-heading font-700 text-base text-foreground mb-2">Clear Communication</h3>
                <p class="text-xs text-muted-foreground leading-relaxed">We keep you informed at every step of the diagnostic and repair process with photo documentation.</p>
            </div>

            <div class="border border-border bg-card p-6">
                <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="font-heading font-700 text-base text-foreground mb-2">Local Commitment</h3>
                <p class="text-xs text-muted-foreground leading-relaxed">We live here, work here, and treat our fellow Mat-Su Valley drivers like family and neighbors.</p>
            </div>
        </div>
    </section>

    <!-- Meet The Team Section -->
    <section class="section-pad py-20 sm:py-28 border-t border-border bg-card/30">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Our Crew</span>
                <span class="h-px w-8 bg-primary"></span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl leading-[1.05] tracking-tight text-foreground">
                Meet Our Team
            </h2>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                Dedicated professionals passionate about automotive craftsmanship.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="border border-border bg-card p-6 text-center">
                <div class="w-24 h-24 rounded-full border-2 border-primary bg-muted mx-auto mb-4 flex items-center justify-center text-2xl font-heading font-800 text-primary">
                    DL
                </div>
                <h3 class="font-heading font-700 text-lg text-foreground">Dave Lindgren</h3>
                <div class="font-mono-data text-xs text-primary mt-1">Lead Master Technician &amp; Owner</div>
                <p class="mt-3 text-xs text-muted-foreground leading-relaxed">
                    Over 18 years of automotive diagnostic experience, specializing in diesel engines and 4x4 drivetrain systems.
                </p>
            </div>

            <div class="border border-border bg-card p-6 text-center">
                <div class="w-24 h-24 rounded-full border-2 border-primary bg-muted mx-auto mb-4 flex items-center justify-center text-2xl font-heading font-800 text-primary">
                    MK
                </div>
                <h3 class="font-heading font-700 text-lg text-foreground">Marcus Kane</h3>
                <div class="font-mono-data text-xs text-primary mt-1">ASE Certified Diagnostic Specialist</div>
                <p class="mt-3 text-xs text-muted-foreground leading-relaxed">
                    Advanced electrical, CAN-bus troubleshooting, and computerized sensor calibration expert.
                </p>
            </div>

            <div class="border border-border bg-card p-6 text-center">
                <div class="w-24 h-24 rounded-full border-2 border-primary bg-muted mx-auto mb-4 flex items-center justify-center text-2xl font-heading font-800 text-primary">
                    TR
                </div>
                <h3 class="font-heading font-700 text-lg text-foreground">Tyler Ross</h3>
                <div class="font-mono-data text-xs text-primary mt-1">Service Advisor &amp; Parts Lead</div>
                <p class="mt-3 text-xs text-muted-foreground leading-relaxed">
                    Ensuring fast turnaround, authentic replacement parts, and smooth appointment coordination.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    @include('components.cta-banner')
</div>
@endsection
