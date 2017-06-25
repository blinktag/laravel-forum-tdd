<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ParticipateInForumTest extends TestCase
{
    use DatabaseMigrations;

    public function test_unauthenticated_users_cannot_add_replies()
    {
        $this->withExceptionHandling()
             ->post("/threads/development/1/replies", [])
             ->assertRedirect('/login');
    }

    public function test_an_authenticated_user_may_participate_in_forum_threads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply');

        $this->post("{$thread->path()}/replies", $reply->toArray())
             ->assertStatus(302);

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
        $this->assertEquals(1, $thread->fresh()->replies_count);

    }

    public function testReplyRequiresABody()
    {
        $this->withExceptionHandling()
             ->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', ['body' => null]);

        $this->post("{$thread->path()}/replies", $reply->toArray())
             ->assertSessionHasErrors('body');
    }

    public function testUnauthorizedUsersCannotDeleteReplies()
    {
        $this->withExceptionHandling();
        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
             ->assertRedirect('/login');

        $this->signIn();
        $this->delete("/replies/{$reply->id}")
             ->assertStatus(403);

    }

    public function testAuthorizedUsersCanDeleteReplies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);
        $this->assertEquals(1, $reply->thread->replies_count);
        $this->delete("/replies/{$reply->id}")
             ->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, $reply->thread->fresh()->replies_count);

    }

    public function testAuthorizedUsersCanEditReplies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $updated_reply = 'Text has changed';
        $this->patch("/replies/{$reply->id}", ['body' => $updated_reply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updated_reply]);
    }

    public function testUnauthorizedUsersCannotEditReplies()
    {
        $this->withExceptionHandling();
        $updated_reply = 'Text has changed';

        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}", ['body' => $updated_reply])
             ->assertRedirect('/login');

        $this->signIn();
        $this->patch("/replies/{$reply->id}", ['body' => $updated_reply])
             ->assertStatus(403);
    }

    /** @test */
    public function replies_that_contain_spam_may_not_be_created()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = make('App\Reply', [
            'body' => 'Yahoo Customer Support'
        ]);

        $this->expectException(\Exception::class);
        $this->post("{$thread->path()}/replies", $reply->toArray());
    }
}
