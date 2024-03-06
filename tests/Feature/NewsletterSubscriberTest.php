<?php

namespace Tests\Feature;

use App\Jobs\SubscriberJoinJob;
use App\Mail\UserJoin;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
    public function email_should_be_a_proper_email_address()
    {
        $this->assertDatabaseCount('subscribers', 0);

        $response = $this->post('/subscribe', [
            'email' => 'foo'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    /** @test */
    public function email_should_be_unique()
    {
        // Email already exists in the database
        Subscriber::factory()->create([ 'email' => 'foo@bar.com' ]);
        $this->assertDatabaseCount('subscribers', 1);

        $response = $this->post('/subscribe', ['email' => 'foo@bar.com']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 1);
    }

    /** @test */
    public function minimum_length_of_email_should_be_8()
    {
        $this->assertDatabaseCount('subscribers', 0);

        $response = $this->post('/subscribe', ['email' => '1@1.com']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    /** @test */
    public function maximum_length_of_email_should_be_64()
    {
        $this->assertDatabaseCount('subscribers', 0);

        $response = $this->post('/subscribe', ['email' => Str::repeat('a', 64) .  '@bar.com']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    /** @test */
    public function it_sends_email_when_someone_subscriber()
    {
        Mail::fake();
        config(['queue.default' => 'sync']);

        $email = 'test@example.com';

        $this->assertDatabaseCount('subscribers', 0);

        $this->post('/subscribe', ['email' => $email]);

        $subscriber = Subscriber::where('email', $email)->first();

        SubscriberJoinJob::dispatch($subscriber);

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', ['email' => $email]);

        Mail::assertSent(UserJoin::class, function ($mail) use ($subscriber) {
            return $mail->hasTo($subscriber->email) &&
                $mail->assertSeeInHtml("Hello ". $subscriber->email) &&
                $mail->assertSeeInHtml("Confirm email");
        });
    }

    /** @test */
    public function a_visitor_can_verify_their_email_and_update_verified_at_column()
    {
        $subscriber = Subscriber::factory()->create([
            'hash' => md5('foo'),
        ]);

        $this->get('/subscribe/'.$subscriber->hash)
            ->assertStatus(302)
            ->assertRedirect('/')
            ->assertSessionHas('success', 'You have successfully verified your email.');

        $this->assertDatabaseHas('subscribers', [
            'verified_at' => now(),
        ]);
    }

    /** @test */
    public function it_return_404_if_hash_does_not_matched()
    {
         Subscriber::factory()->create([
            'hash' => 'foo',
        ]);

        $this->get('/subscribe/fake')
            ->assertStatus(404);
    }
}
