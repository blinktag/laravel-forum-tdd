<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadThreadsTest extends TestCase
{

    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->thread = factory('App\Thread')->create();
    }

    public function testUserCanBrowseThread()
    {
        $this->get('/threads')
             ->assertStatus(200)
             ->assertSee($this->thread->title);
    }

    public function testUserCanViewSingleThread()
    {
        $this->get($this->thread->path())
             ->assertStatus(200)
             ->assertSee($this->thread->title)
             ->assertSee($this->thread->body);
    }

    public function testUserCanReadRepliesOfAThread()
    {
        $reply = create('App\Reply', ['thread_id' => $this->thread->id]);

        $this->get($this->thread->path() . '/replies')
             ->assertStatus(200)
             ->assertSee($reply->body);
    }

    public function testUserCanFilterThreadsByChannel()
    {
        $channel = create('App\Channel');
        $threadInChannel = create('App\Thread', ['channel_id' => $channel->id]);
        $threadNotInChannel = create('App\Thread');

        $this->get("/threads/{$channel->slug}")
             ->assertSee($threadInChannel->title)
             ->assertDontSee($threadNotInChannel->title);
    }

    public function testUserCanFilterthreadsByAuthor()
    {
        $this->signIn(create('App\User', ['name' => 'JohnDoe']));
        $threadByUser = create('App\Thread', ['user_id' => auth()->id()]);
        $threadNotByUser = create('App\Thread');

        $this->get('threads?by=JohnDoe')
             ->assertSee($threadByUser->title)
             ->assertDontSee($threadNotByUser->title);
    }

    public function testUserCanFilterThreadsByPopularity()
    {
        // Given we have 3 threads
        // With 2, 3, 0 replies respectively
        $threadWithTwoReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithTwoReplies], 2);

        $threadWithThreeReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithThreeReplies], 3);

        $threadWithZeroReplies = $this->thread;

        // When I filter all threads by popularity
        $response = $this->getJson('threads?popular=1')->json();
        // They should be returned from most replies to lease

        ;
        $this->assertEquals([3,2,0], array_column($response, 'replies_count'));
    }

    public function testUserCanFilterThreadsByUnanswered()
    {
        $thread = create('App\Thread');
        create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->getJson('threads?unanswered=1')->json();

        $this->assertCount(1, $response);
    }

    public function testUserCanRequestAllRepliesForAGivenThread()
    {
        $thread = create('App\Thread');
        create('App\Reply', ['thread_id' => $thread->id], 2);

        $response = $this->getJson($thread->path() . '/replies')->json();

        $this->assertCount(2, $response['data']);
        $this->assertEquals(2, $response['total']);
    }
}
