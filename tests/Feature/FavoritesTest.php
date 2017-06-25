<?php

namespace Tests\Feature;

use App\Favorite;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoritesTest extends TestCase
{

    use DatabaseMigrations;

    public function testGuestsCannotFavoriteAnything()
    {
        $this->withExceptionHandling();
        $this->post("/replies/1/favorites")
             ->assertRedirect('/login');
    }

    public function testUserCanFavoriteAnyReply()
    {
        $this->signIn();
        $reply = create('App\Reply');

        // If I post to a "favorite" endpoint
        $this->post("/replies/{$reply->id}/favorites");

        // It should be recorded in the database
        $this->assertCount(1, $reply->favorites);
    }

    public function testUserCanUnfavoriteAnyReply()
    {
        $this->signIn();
        $reply = create('App\Reply');

        $reply->favorite();

        $this->delete("/replies/{$reply->id}/favorites");
        $this->assertCount(0, $reply->favorites);
    }

    public function testUserMayOnlyFavoriteAReplyOnce()
    {
        $this->signIn();
        $reply = create('App\Reply');

        // If I post to a "favorite" endpoint
        try {
            $this->post("/replies/{$reply->id}/favorites");
            $this->post("/replies/{$reply->id}/favorites");
        } catch(\Exception $e) {
            $this->fail('Did not expect to insert the same record twice');
        }

        // It should be recorded in the database
        $this->assertCount(1, $reply->favorites);
    }
}
