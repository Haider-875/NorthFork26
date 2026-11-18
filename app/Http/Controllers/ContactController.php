<?php

namespace AppHttpControllers;

use AppModelsContactMessage;
use IlluminateHttpRequest;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
        ]);

        $message = ContactMessage::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully.',
                'data' => $message
            ]);
        }

        return back()->with('success', 'Your message has been sent successfully.');
    }
}
