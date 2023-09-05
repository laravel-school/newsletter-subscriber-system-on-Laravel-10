<?php

namespace App\Jobs;

use App\Mail\UserJoin;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SubscriberJoinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Subscriber $subscriber)
    {
    }

    public function handle(): void
    {
        Mail::to($this->subscriber->email)
            ->send(new UserJoin($this->subscriber));
    }
}
