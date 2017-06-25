<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NotificationsTest extends TestCase
{

    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    public function testNotificationIsPreparedWhenASubscribedThreadReceivesANewReplyThatIsNotByCurrentUser()
    {
        $thread = create('App\Thread')->subscribe();

        $this->assertCount(0, auth()->user()->fresh()->notifications);

        $thread->addReply([
            'user_id' => auth()->id(),
            'body'    => 'Lorem ipsum dolor sit amet'
        ]);

        $this->assertCount(0, auth()->user()->fresh()->notifications);

        $thread->addReply([
            'user_id' => create('App\User')->id,
            'body'    => 'Lorem ipsum dolor sit amet'
        ]);

        $this->assertCount(1, auth()->user()->fresh()->notifications);
    }

    public function testUserCanFetchTheirUnreadNotifications()
    {
        create(\Illuminate\Notifications\DatabaseNotification::class);

        $response = $this->getJson("/profiles/" . auth()->user() . "/notifications/")->json();

        $this->assertCount(1, $response);
    }

    public function testUserCanMarkANotificationAsRead()
    {
        create(\Illuminate\Notifications\DatabaseNotification::class);

        $user = auth()->user();

        $this->assertCount(1, $user->fresh()->unreadNotifications);

        $notification = $user->unreadNotifications->first();

        $this->delete("/profiles/" . auth()->user()->name . "/notifications/{$notification->id}");

        $this->assertCount(0, $user->fresh()->unreadNotifications);

    }
}
