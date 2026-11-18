@extends('layouts.app')

@section('title', 'Schedule Service Appointment | North Fork Auto')
@section('description', 'Book your automotive repair or maintenance appointment in minutes. Pick your service, select a date & time, and we take care of the rest.')

@section('content')
<div class="section-pad pt-32 pb-24 min-h-screen">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-12 text-center">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Book an Appointment</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-foreground">
            Schedule Your Service
        </h1>
        <p class="mt-5 text-muted-foreground text-base sm:text-lg max-w-2xl mx-auto">
            Book in minutes. Choose your service, pick an available time, and we'll take care of the rest. No account needed.
        </p>
    </div>

    <!-- Booking Wizard Container -->
    <div class="max-w-4xl mx-auto border border-border bg-card p-6 sm:p-10">
        <!-- Step Progress Bar -->
        <div class="mb-10">
            <div class="grid grid-cols-4 gap-2">
                <div id="step-tab-1" class="h-1.5 bg-primary rounded-sm transition-all duration-300"></div>
                <div id="step-tab-2" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
                <div id="step-tab-3" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
                <div id="step-tab-4" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
            </div>
            <div class="mt-3 flex justify-between text-[11px] font-mono-data text-muted-foreground">
                <span id="step-label-1" class="text-primary font-bold">1. Contact</span>
                <span id="step-label-2">2. Vehicle</span>
                <span id="step-label-3">3. Service</span>
                <span id="step-label-4">4. Date &amp; Time</span>
            </div>
        </div>

        <div id="booking-feedback" class="hidden mb-6 p-4 border text-sm"></div>

        <form id="booking-wizard-form" action="/book" method="POST">
            @csrf
            
            <!-- STEP 1: Contact Information -->
            <div id="step-panel-1" class="space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Customer Information</h3>
                    <p class="text-xs text-muted-foreground mt-1">Please provide your contact details so we can confirm your booking.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-name" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Full Name <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="cust-name" name="name" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Sarah Connor">
                    </div>
                    <div>
                        <label for="cust-email" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Email Address <span class="text-primary">*</span>
                        </label>
                        <input type="email" id="cust-email" name="email" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="sarah@example.com">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-phone" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Phone Number <span class="text-primary">*</span>
                        </label>
                        <input type="tel" id="cust-phone" name="phone" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="(907) 555-0199">
                    </div>
                    <div>
                        <label for="cust-notes" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Special Requests / Symptoms
                        </label>
                        <input type="text" id="cust-notes" name="notes" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Squeaking noise when braking...">
                    </div>
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="button" id="btn-to-step-2" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Vehicle
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Vehicle Details -->
            <div id="step-panel-2" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Vehicle Details</h3>
                    <p class="text-xs text-muted-foreground mt-1">Tell us about the vehicle you're bringing in.</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-6">
                    <div>
                        <label for="veh-year" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Year <span class="text-primary">*</span>
                        </label>
                        <input type="number" id="veh-year" name="vehicle_year" min="1950" max="{{ date('Y') + 1 }}" required value="2020" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label for="veh-make" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Make <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="veh-make" name="vehicle_make" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Toyota, Subaru, Ford">
                    </div>
                    <div>
                        <label for="veh-model" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Model <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="veh-model" name="vehicle_model" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. 4Runner, Outback, F-150">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="veh-mileage" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Approximate Mileage
                        </label>
                        <input type="text" id="veh-mileage" name="vehicle_mileage" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. 85,000">
                    </div>
                    <div>
                        <label for="veh-vin" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            VIN (Optional)
                        </label>
                        <input type="text" id="veh-vin" name="vehicle_vin" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="17-character VIN">
                    </div>
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-1" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-3" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Service
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Select Service -->
            <div id="step-panel-3" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Select Requested Service</h3>
                    <p class="text-xs text-muted-foreground mt-1">Pick the primary service you need performed.</p>
                </div>

                <input type="hidden" id="selected-service-input" name="service" value="Diagnostic Inspection">

                <div class="grid sm:grid-cols-2 gap-4">
                    @php
                        $bookingServices = [
                            ['name' => 'Diagnostic Inspection', 'desc' => 'Comprehensive scanning and physical inspection.', 'price' => '$120.00'],
                            ['name' => 'Synthetic Oil Change', 'desc' => 'Full synthetic oil, OEM filter, multi-point check.', 'price' => '$89.99'],
                            ['name' => 'Brake Pad & Rotor Service', 'desc' => 'Front or rear pads, rotors, caliper check.', 'price' => '$189.99'],
                            ['name' => 'Transmission Service', 'desc' => 'Fluid exchange and filter check.', 'price' => '$189.99'],
                            ['name' => 'Steering & Suspension', 'desc' => 'Struts, tie-rods, control arms, alignment.', 'price' => 'Custom Quote'],
                            ['name' => 'Battery & Electrical', 'desc' => 'Alternator, starter, battery diagnostic.', 'price' => '$49.99'],
                            ['name' => 'Cooling & Heating / AC', 'desc' => 'Coolant flush, radiator, heater core, AC.', 'price' => '$99.99'],
                            ['name' => 'General Maintenance / Tune-up', 'desc' => 'Spark plugs, belts, filters, inspection.', 'price' => 'Custom Quote']
                        ];
                    @endphp

                    @foreach($bookingServices as $idx => $bs)
                        <div class="service-select-card cursor-pointer border p-4 transition-all duration-200 {{ $idx === 0 ? 'border-primary bg-primary/10' : 'border-border bg-card hover:border-primary/50' }}" data-service-name="{{ $bs['name'] }}">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-heading font-700 text-sm text-foreground">{{ $bs['name'] }}</h4>
                                <span class="font-mono-data text-xs text-primary shrink-0">{{ $bs['price'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $bs['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-2" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-4" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Date &amp; Time
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Date & Time Picker & Submit -->
            <div id="step-panel-4" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Select Appointment Date &amp; Time</h3>
                    <p class="text-xs text-muted-foreground mt-1">Select your preferred drop-off slot (Mon - Fri, 8:00 AM – 5:00 PM).</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="app-date" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Preferred Date <span class="text-primary">*</span>
                        </label>
                        <input type="date" id="app-date" name="preferred_date" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label for="app-time" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Preferred Time Slot <span class="text-primary">*</span>
                        </label>
                        <select id="app-time" name="preferred_time" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
                            <option value="08:00">08:00 AM (Early Drop-off)</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00" selected>10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="13:00">01:00 PM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                    </div>
                </div>

                <!-- Booking Summary Review Card -->
                <div class="border border-border bg-background p-5 text-sm space-y-2">
                    <div class="font-mono-data text-xs text-primary mb-2">Appointment Review</div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Customer:</span><span id="review-cust" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Vehicle:</span><span id="review-veh" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Service:</span><span id="review-srv" class="font-heading font-600 text-foreground">Diagnostic Inspection</span></div>
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-3" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="submit" id="btn-submit-booking" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Confirm &amp; Book Appointment
                    </button>
                </div>
            </div>

            <!-- STEP 5: Success Confirmation Screen -->
            <div id="step-panel-5" class="hidden text-center py-8 space-y-6">
                <div class="w-16 h-16 rounded-full border-2 border-emerald-500 bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="font-heading font-800 text-3xl text-foreground">Appointment Requested!</h3>
                <p class="text-muted-foreground max-w-lg mx-auto text-sm leading-relaxed">
                    Thank you! We have received your booking request. Our service advisor will review your slot and contact you to confirm drop-off details.
                </p>
                <div class="border border-border bg-background p-6 max-w-md mx-auto text-left space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Booking Reference:</span><span id="conf-ref" class="font-mono-data text-primary font-bold">NFA-2026-0001</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Service:</span><span id="conf-service" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Date:</span><span id="conf-date" class="font-mono-data text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Status:</span><span class="px-2 py-0.5 text-[10px] uppercase font-bold border border-primary/40 bg-primary/15 text-primary">Pending Confirmation</span></div>
                </div>
                <div class="pt-4 flex justify-center gap-4">
                    <a href="/" class="btn-engine border border-border px-6 py-3 text-xs text-foreground hover:border-primary">
                        Return Home
                    </a>
                    <a href="tel:+19077333030" class="btn-engine bg-primary text-primary-foreground px-6 py-3 text-xs">
                        Call Shop
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set tomorrow as default minimum date
        const dateInput = document.getElementById('app-date');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const yyyy = tomorrow.getFullYear();
        const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const dd = String(tomorrow.getDate()).padStart(2, '0');
        dateInput.min = `${yyyy}-${mm}-${dd}`;
        dateInput.value = `${yyyy}-${mm}-${dd}`;

        // Check for URL parameter service
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedService = urlParams.get('service');
        if (preselectedService) {
            document.getElementById('selected-service-input').value = preselectedService;
            document.getElementById('review-srv').textContent = preselectedService;
        }

        // Wizard navigation
        const panels = [1, 2, 3, 4, 5].map(i => document.getElementById(`step-panel-${i}`));
        const tabs = [1, 2, 3, 4].map(i => document.getElementById(`step-tab-${i}`));
        const labels = [1, 2, 3, 4].map(i => document.getElementById(`step-label-${i}`));

        function goToStep(step) {
            panels.forEach((p, idx) => {
                if (p) {
                    if (idx + 1 === step) p.classList.remove('hidden');
                    else p.classList.add('hidden');
                }
            });
            tabs.forEach((t, idx) => {
                if (t) {
                    if (idx + 1 <= step) {
                        t.classList.remove('bg-border');
                        t.classList.add('bg-primary');
                    } else {
                        t.classList.remove('bg-primary');
                        t.classList.add('bg-border');
                    }
                }
            });
            labels.forEach((l, idx) => {
                if (l) {
                    if (idx + 1 === step) {
                        l.classList.add('text-primary', 'font-bold');
                    } else {
                        l.classList.remove('text-primary', 'font-bold');
                    }
                }
            });

            // Update reviews
            const name = document.getElementById('cust-name').value;
            const year = document.getElementById('veh-year').value;
            const make = document.getElementById('veh-make').value;
            const model = document.getElementById('veh-model').value;
            const srv = document.getElementById('selected-service-input').value;

            document.getElementById('review-cust').textContent = name || '-';
            document.getElementById('review-veh').textContent = (year || make || model) ? `${year} ${make} ${model}` : '-';
            document.getElementById('review-srv').textContent = srv || '-';
        }

        // Button handlers
        document.getElementById('btn-to-step-2').addEventListener('click', () => {
            const name = document.getElementById('cust-name').value.trim();
            const email = document.getElementById('cust-email').value.trim();
            const phone = document.getElementById('cust-phone').value.trim();
            if (!name || !email || !phone) {
                alert('Please enter your name, email, and phone number.');
                return;
            }
            goToStep(2);
        });

        document.getElementById('btn-back-to-step-1').addEventListener('click', () => goToStep(1));
        document.getElementById('btn-to-step-3').addEventListener('click', () => {
            const make = document.getElementById('veh-make').value.trim();
            const model = document.getElementById('veh-model').value.trim();
            if (!make || !model) {
                alert('Please enter vehicle make and model.');
                return;
            }
            goToStep(3);
        });

        document.getElementById('btn-back-to-step-2').addEventListener('click', () => goToStep(2));
        document.getElementById('btn-to-step-4').addEventListener('click', () => goToStep(4));
        document.getElementById('btn-back-to-step-3').addEventListener('click', () => goToStep(3));

        // Service card selection
        document.querySelectorAll('.service-select-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.service-select-card').forEach(c => {
                    c.classList.remove('border-primary', 'bg-primary/10');
                    c.classList.add('border-border', 'bg-card');
                });
                card.classList.remove('border-border', 'bg-card');
                card.classList.add('border-primary', 'bg-primary/10');
                const sName = card.getAttribute('data-service-name');
                document.getElementById('selected-service-input').value = sName;
                document.getElementById('review-srv').textContent = sName;
            });
        });

        // Form Submit
        const form = document.getElementById('booking-wizard-form');
        const submitBtn = document.getElementById('btn-submit-booking');
        const feedback = document.getElementById('booking-feedback');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            feedback.classList.add('hidden');

            const formData = new FormData(form);
            const payload = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                vehicle_year: formData.get('vehicle_year'),
                vehicle_make: formData.get('vehicle_make'),
                vehicle_model: formData.get('vehicle_model'),
                vehicle_mileage: formData.get('vehicle_mileage'),
                vehicle_vin: formData.get('vehicle_vin'),
                service: formData.get('service'),
                preferred_date: formData.get('preferred_date'),
                preferred_time: formData.get('preferred_time'),
                notes: formData.get('notes')
            };

            try {
                const res = await fetch('/book', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    document.getElementById('conf-ref').textContent = data.booking?.booking_number || 'NFA-' + new Date().getFullYear() + '-0001';
                    document.getElementById('conf-service').textContent = payload.service;
                    document.getElementById('conf-date').textContent = payload.preferred_date + ' at ' + payload.preferred_time;
                    goToStep(5);
                } else {
                    throw new Error(data.message || 'Failed to submit booking.');
                }
            } catch (err) {
                feedback.className = 'mb-6 p-4 border border-primary/40 bg-primary/10 text-primary text-sm';
                feedback.textContent = err.message || 'An error occurred. Please call the shop directly at (907) 733-3030.';
                feedback.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm & Book Appointment';
            }
        });
    });
</script>
@endsection
