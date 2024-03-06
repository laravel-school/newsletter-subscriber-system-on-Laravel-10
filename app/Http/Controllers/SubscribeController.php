<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use App\Jobs\SubscriberJoinJob;
use App\Http\Requests\NewsletterRequest;

class SubscribeController extends Controller
{
    public function post(NewsletterRequest $request)
    {
        $validated = $request->validated();

        // Maybe you need more validation rules???

        $Subscriber = Subscriber::create([
            'email' => $validated['email'],
            'hash' => md5($validated['email']),
        ]);

        SubscriberJoinJob::dispatch($Subscriber);

        return redirect()->back()->with('success', 'You have successfully subscribed. Please check your email spam folder.');
    }

    public function verify(string $hash)
    {
        $subscriber = Subscriber::where('hash', $hash)->firstOrFail();

        $subscriber->update([
            'hash' => null,
            'verified_at' => now()
        ]);

        return redirect('/')
            ->with('success', 'You have successfully verified your email.');
    }
}
