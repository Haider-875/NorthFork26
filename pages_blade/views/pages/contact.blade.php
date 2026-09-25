@extends('layouts.app')

@section('title', 'Visit Us & Contact | North Fork Auto - Talkeetna, AK')
@section('description', 'Contact North Fork Auto or stop by our Talkeetna, Alaska location. Call (907) 733-3030 or send a message online.')

@section('content')
<div class="pt-24 bg-background">
    <!-- Header -->
    <section class="section-pad py-16 border-b border-border">
        <div class="max-w-3xl">
            <div class="font-mono-data flex items-center gap-2 mb-4">
                <span class="h-px w-8 bg-primary"></span>
                <span>Get In Touch</span>
            </div>
            <h1 class="font-heading font-800 text-3xl sm:text-5xl lg:text-6xl leading-[1.05] tracking-tight text-foreground">
                Visit Us or Send a Message
            </h1>
            <p class="mt-4 text-base sm:text-lg text-muted-foreground leading-relaxed">
                Have questions regarding a repair or want to schedule maintenance? We are here to help. Stop by the shop or send us a note below.
            </p>
        </div>
    </section>

    <!-- 2 Column Section: Left Info Cards / Right Message Form -->
    <section class="section-pad py-16">
        <div class="grid lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-5 space-y-6">
                <!-- Address -->
                <div class="border border-border bg-card p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <div class="font-mono-data text-[10px] mb-1">Our Location</div>
                            <h3 class="font-heading font-700 text-base text-foreground">Shop Address</h3>
                            <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
                                Talkeetna Spur Road<br>Talkeetna, AK 99676
                            </p>
                            <a href="https://maps.google.com/?q=Talkeetna+Spur+Road,+Talkeetna,+AK+99676" target="_blank" rel="noreferrer" class="mt-3 inline-flex items-center gap-1 font-heading font-700 text-xs text-primary hover:underline">
                                Open Google Maps
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="border border-border bg-card p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <div class="font-mono-data text-[10px] mb-1">Phone Lines</div>
                            <h3 class="font-heading font-700 text-base text-foreground">Direct Shop Phone</h3>
                            <div class="mt-2 space-y-1 text-xs">
                                <div>
                                    <span class="text-muted-foreground">Primary: </span>
                                    <a href="tel:+19077333030" class="font-heading font-700 text-primary hover:underline">(907) 733-3030</a>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Direct Shop: </span>
                                    <a href="tel:+19072323859" class="font-heading font-700 text-primary hover:underline">+1 (907) 232-3859</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="border border-border bg-card p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div>
                            <div class="font-mono-data text-[10px] mb-1">Email Inquiries</div>
                            <h3 class="font-heading font-700 text-base text-foreground">Electronic Message</h3>
                            <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
                                Write to us for parts inquiries, warranty questions, or general estimates.
                            </p>
                            <a href="mailto:info@northforkauto.com" class="mt-3 inline-flex items-center gap-1 font-heading font-700 text-xs text-primary hover:underline">
                                info@northforkauto.com
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hours -->
                <div class="border border-border bg-card p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded border border-primary/40 bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-mono-data text-[10px] mb-1">Working Schedule</div>
                            <h3 class="font-heading font-700 text-base text-foreground">Shop Hours</h3>
                            <ul class="mt-3 space-y-1 text-xs">
                                <li class="flex justify-between py-1 border-b border-border/40">
                                    <span class="text-muted-foreground">Monday – Friday</span>
                                    <span class="font-mono-data text-foreground/90">8:00 AM – 5:00 PM</span>
                                </li>
                                <li class="flex justify-between py-1 border-b border-border/40">
                                    <span class="text-muted-foreground">Saturday</span>
                                    <span class="font-mono-data text-foreground/90">Closed</span>
                                </li>
                                <li class="flex justify-between py-1">
                                    <span class="text-muted-foreground">Sunday</span>
                                    <span class="font-mono-data text-foreground/90">Closed</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="border border-border bg-card p-8 sm:p-10">
                    <div class="font-mono-data flex items-center gap-2 mb-3">
                        <span class="h-px w-6 bg-primary"></span>
                        <span>Send a Message</span>
                    </div>
                    <h2 class="font-heading font-800 text-2xl sm:text-3xl text-foreground mb-4">
                        How Can We Help You?
                    </h2>
                    <p class="text-sm text-muted-foreground leading-relaxed mb-8">
                        Fill out the form below and our team will get back to you within 1 business day.
                    </p>

                    <div id="contact-feedback" class="hidden mb-6 p-4 border text-sm"></div>

                    <form id="contact-form" action="/contact" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="contact-name" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                                    Full Name <span class="text-primary">*</span>
                                </label>
                                <input type="text" id="contact-name" name="name" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="John Doe">
                            </div>
                            <div>
                                <label for="contact-email" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                                    Email Address <span class="text-primary">*</span>
                                </label>
                                <input type="email" id="contact-email" name="email" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="john@example.com">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="contact-phone" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                                    Phone Number
                                </label>
                                <input type="tel" id="contact-phone" name="phone" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="(907) 555-0199">
                            </div>
                            <div>
                                <label for="contact-subject" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                                    Subject
                                </label>
                                <input type="text" id="contact-subject" name="subject" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="General Inquiry">
                            </div>
                        </div>

                        <div>
                            <label for="contact-message" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                                Message <span class="text-primary">*</span>
                            </label>
                            <textarea id="contact-message" name="message" rows="5" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="Describe your vehicle needs, issues, or question..."></textarea>
                        </div>

                        <button type="submit" id="contact-submit-btn" class="btn-engine bg-primary text-primary-foreground px-8 py-4 text-sm w-full sm:w-auto">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Full Width Dark Inverted Map -->
    <div class="border-y border-border overflow-hidden bg-card min-h-[420px] relative">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30560.85210344795!2d-150.1444!3d62.3209!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x56cde9d17d84fcf1%3A0xb366aa081e7d80f0!2sTalkeetna%2C%20AK%2099676!5e0!3m2!1sen!2sus!4v1680000000000!5m2!1sen!2sus" 
            class="w-full h-full min-h-[420px] border-0" 
            style="filter: invert(0.92) hue-rotate(180deg) contrast(0.9);" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            title="North Fork Auto Location">
        </iframe>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('contact-form');
        const feedback = document.getElementById('contact-feedback');
        const submitBtn = document.getElementById('contact-submit-btn');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                feedback.classList.add('hidden');

                const formData = new FormData(form);
                const payload = {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    subject: formData.get('subject'),
                    message: formData.get('message')
                };

                try {
                    const res = await fetch('/contact', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        feedback.className = 'mb-6 p-4 border border-emerald-500/40 bg-emerald-500/10 text-emerald-400 text-sm';
                        feedback.textContent = 'Thank you! Your message has been sent successfully. We will be in touch shortly.';
                        feedback.classList.remove('hidden');
                        form.reset();
                    } else {
                        throw new Error(data.message || 'Failed to send message.');
                    }
                } catch (err) {
                    feedback.className = 'mb-6 p-4 border border-primary/40 bg-primary/10 text-primary text-sm';
                    feedback.textContent = err.message || 'An error occurred while submitting your message. Please call us directly.';
                    feedback.classList.remove('hidden');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Message';
                }
            });
        }
    });
</script>
@endsection
