<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsLetterController extends Controller
{
    /**
     * Handle the incoming request to subscribe to the newsletter.
     */
    public function subscribe(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|unique:news_letters,email',
        ]);

        // Create a new subscription
        \App\Models\NewsLetter::create([
            'email' => $request->email,
            'is_subscribed' => true,
            'subscribed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
