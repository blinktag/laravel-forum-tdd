<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SubscribeToThreadsTest extends TestCase
{

    use DatabaseMigrations;

    public function testUserCanSubscribeToThreads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $this->post($thread->path() . '/subscriptions');

        $this->assertCount(1, $thread->fresh()->subscriptions);

    }

    public function testUserCanUnsubscribeToThreads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $this->assertCount(0, $thread->subscriptions);

        $thread->subscribe();
        $this->assertCount(1, $thread->fresh()->subscriptions);

        $this->delete($thread->path() . '/subscriptions');
        $this->assertCount(0, $thread->subscriptions);

    }
}
