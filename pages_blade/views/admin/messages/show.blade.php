@extends('layouts.admin')

@section('header_title', 'Inquiry from ' . $message->name)

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="/admin/messages" class="text-xs text-muted-foreground hover:text-primary flex items-center gap-1 font-semibold">
            ← Back to All Inquiries
        </a>
        <form action="/admin/messages/{{ $message->id }}" method="POST" onsubmit="return confirm('Delete this inquiry?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-primary hover:underline flex items-center gap-1 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Message
            </button>
        </form>
    </div>

    <div class="border border-border bg-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-border pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-800 text-xl text-foreground">{{ $message->name }}</h2>
                <div class="text-xs text-muted-foreground mt-1 flex flex-wrap items-center gap-3">
                    <a href="mailto:{{ $message->email }}" class="text-primary hover:underline">{{ $message->email }}</a>
                    @if($message->phone)
                        <span>·</span>
                        <a href="tel:{{ $message->phone }}" class="text-foreground hover:underline">{{ $message->phone }}</a>
                    @endif
                </div>
            </div>
            <div class="text-xs font-mono-data text-muted-foreground sm:text-right">
                <span>Received:</span><br>
                <span class="text-foreground font-semibold">{{ $message->created_at->format('M d, Y · h:i A') }}</span>
            </div>
        </div>

        @if($message->subject)
            <div>
                <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-1">Subject</span>
                <div class="font-heading font-700 text-base text-foreground">{{ $message->subject }}</div>
            </div>
        @endif

        <div>
            <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-2">Customer Message</span>
            <div class="p-5 bg-background border border-border text-sm text-foreground/90 leading-relaxed whitespace-pre-line rounded-sm">
                {{ $message->message }}
            </div>
        </div>

        <div class="pt-4 border-t border-border flex flex-wrap gap-3">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject ?? 'North Fork Auto Inquiry') }}" class="bg-primary text-white font-bold px-6 py-2.5 text-xs uppercase hover:bg-primary/90 transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Reply via Email
            </a>
            @if($message->phone)
                <a href="tel:{{ $message->phone }}" class="bg-secondary text-foreground hover:bg-secondary/80 border border-border font-bold px-6 py-2.5 text-xs uppercase transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Call Customer
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
