<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Notifications\ThreadWasUpdated;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ThreadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();
        $this->thread = factory('App\Thread')->create();
    }

    public function test_thread_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    public function test_thread_has_creator()
    {
        $this->assertInstanceOf('App\User', $this->thread->creator);
    }

    public function test_thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'body'    => 'foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    public function test_thread_belongs_to_a_channel()
    {
        $thread = create('App\Thread');

        $this->assertInstanceOf('App\Channel', $thread->channel);
    }

    public function test_a_thread_can_make_a_string_path()
    {
        $thread = create('App\Thread');
        $this->assertEquals("/threads/{$thread->channel->slug}/{$thread->id}", $thread->path());
    }

    public function testAThreadCanBeSubscribedTo()
    {
        // Given we have a thread
        $thread = create('App\Thread');

        // And an authenticated user
        $this->signIn();

        // When the user subscribes to the thread
        $thread->subscribe(auth()->id());


        // Then we should be able to fetch all threads that the user has subscribed to
        $subs = $thread->subscriptions()
                       ->where('user_id', auth()->id())
                       ->count();
        $this->assertEquals(1, $subs);
    }

    public function testAThreadCanBeUnubscribedTo()
    {
        // Given we have a thread
        $thread = create('App\Thread');

        // And a user is subscribed to the thread
        $thread->subscribe($user_id = 1);

        $thread->unsubscribe($user_id = 1);

        // Subscriptions should equal zero
        $this->assertCount(0, $thread->fresh()->subscriptions);
    }

    public function testItKnowsIfUserIsSubscribed()
    {
        $thread = create('App\Thread');

        $this->signIn();

        $this->assertFalse($thread->isSubscribedTo);

        $thread->subscribe();

        $this->assertTrue($thread->isSubscribedTo);
    }

    public function testReplyGeneratesNotification()
    {
        Notification::fake();

        $this->signIn()
             ->thread
             ->subscribe()
             ->addReply([
                'body'    => 'foobar',
                'user_id' => 999 // Will fail if ID is same as signed in user
            ]);

        Notification::assertSentTo(auth()->user(), ThreadWasUpdated::class);
    }

    /** @test */
    public function a_thread_can_check_if_the_authenticated_user_has_read_all_replies()
    {
        $this->signIn();
        $thread = create('App\Thread');
        tap(auth()->user(), function ($user) use ($thread) {
            $this->assertTrue($thread->hasUpdatesFor($user));
            $user->read($thread);
            $this->assertFalse($thread->hasUpdatesFor($user));
        });
    }
}
