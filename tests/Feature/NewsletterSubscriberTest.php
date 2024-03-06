<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_visitor_can_subscriber()
    {
        $this->assertDatabaseCount('subscribers', 0);

        $response = $this->post('/subscribe', [
            'email' => 'foo@bar.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'You have successfully subscribed. Please check your email spam folder.');

        $this->assertDatabaseCount('subscribers', 1);
    }

    /** @test */
    public function email_is_required_for_subscription()
    {
        $this->assertDatabaseCount('subscribers', 0);

        $response = $this->post('/subscribe', [
            'email' => NULL,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }
}
