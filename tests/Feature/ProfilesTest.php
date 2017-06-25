<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProfilesTest extends TestCase
{
    use DatabaseMigrations;

    public function testUserHasAProfile()
    {
        $user = create('App\User');

        $this->get("/profiles/{$user->name}")
             ->assertSee($user->name);
    }

    public function testProfilesDisplayAllThreadsCreatedByUser()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id() ]);

        $this->get("/profiles/" . auth()->user()->name)
             ->assertSee($thread->title)
             ->assertSee($thread->body);
    }
}
