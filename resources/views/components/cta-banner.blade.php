@props([
    'title' => 'Need Auto Repair or Maintenance?',
    'subtitle' => 'Schedule your appointment today and get your vehicle back on the road.',
    'image' => 'https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/130f34b82_generated_cc4f3b48.jpg'
])

<section class="relative overflow-hidden border-y border-border">
    <img src="{{ $image }}" alt="North Fork Auto service bay" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-background/85 backdrop-blur-[2px]"></div>
    <div class="relative section-pad py-20 sm:py-24 text-center">
        <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl tracking-tight max-w-2xl mx-auto text-foreground">
            {{ $title }}
        </h2>
        <p class="mt-4 text-base sm:text-lg text-muted-foreground max-w-xl mx-auto">
            {{ $subtitle }}
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/book" class="btn-engine bg-primary text-primary-foreground px-7 py-3.5 text-sm w-full sm:w-auto">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                Book an Appointment
            </a>
            <a href="tel:+19077333030" class="btn-engine border border-border bg-background/60 hover:border-primary px-7 py-3.5 text-sm w-full sm:w-auto text-foreground">
                <svg class="w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Call (907) 733-3030
            </a>
        </div>
    </div>
</section>
