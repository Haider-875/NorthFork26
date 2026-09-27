@extends('layouts.admin')

@section('header_title', 'Customer Inquiries & Messages')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-heading font-800 text-2xl text-foreground">Customer Inquiries</h2>
            <p class="text-xs text-muted-foreground mt-1">Questions and service inquiries submitted from the Contact Us page.</p>
        </div>
        @if($unreadCount > 0)
            <span class="px-3 py-1 bg-primary text-white text-xs font-bold rounded-sm">
                {{ $unreadCount }} Unread {{ Str::plural('Message', $unreadCount) }}
            </span>
        @endif
    </div>

    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Sender</th>
                    <th class="py-3 px-4">Subject &amp; Message Preview</th>
                    <th class="py-3 px-4">Date Received</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($messages as $msg)
                    <tr class="hover:bg-secondary/40 transition-colors {{ !$msg->is_read ? 'bg-primary/[0.03]' : '' }}">
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if(!$msg->is_read)
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold bg-primary text-white rounded-full">New</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold text-muted-foreground">Read</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="font-heading font-700 text-foreground">{{ $msg->name }}</div>
                            <div class="text-xs text-muted-foreground mt-0.5">
                                <a href="mailto:{{ $msg->email }}" class="hover:underline">{{ $msg->email }}</a>
                                @if($msg->phone)
                                    <span>·</span> <a href="tel:{{ $msg->phone }}" class="hover:underline">{{ $msg->phone }}</a>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($msg->subject)
                                <div class="font-semibold text-xs text-foreground">{{ $msg->subject }}</div>
                            @endif
                            <div class="text-xs text-muted-foreground line-clamp-1 mt-0.5">{{ Str::limit($msg->message, 100) }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs whitespace-nowrap text-muted-foreground">
                            {{ $msg->created_at->format('M d, Y') }}<br>
                            <span class="text-[10px]">{{ $msg->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/messages/{{ $msg->id }}" class="px-3 py-1.5 text-xs font-bold border border-border hover:border-primary text-foreground hover:text-primary transition-colors inline-block">
                                View
                            </a>
                            <form action="/admin/messages/{{ $msg->id }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this message?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 text-xs text-primary hover:text-white hover:bg-primary/20 transition-colors" title="Delete Message">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-muted-foreground">
                            No contact messages received yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($messages, 'hasPages') && $messages->hasPages())
        <div class="pt-4 flex justify-between items-center text-xs text-muted-foreground">
            <div>Showing {{ $messages->firstItem() }} to {{ $messages->lastItem() }} of {{ $messages->total() }} messages</div>
            <div>{{ $messages->links() }}</div>
        </div>
    @endif
</div>
@endsection
