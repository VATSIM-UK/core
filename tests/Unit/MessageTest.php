<?php

namespace Tests\Unit;

use App\Models\Messages\Thread\Participant;
use App\Models\Messages\Thread\Post;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use DatabaseTransactions;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = factory(\App\Models\Messages\Thread::class)->create();
        factory(Account::class, 2)
            ->create()
            ->each(function($participant) {
                $this->thread->participants()->save($participant);
            });
        factory(Post::class, 2)
            ->create()
            ->each(function($post) {
                $this->thread->posts()->save($post);
            });
    }

    public function testThreadPosts()
    {
        $this->assertEquals(2, $this->thread->posts->count());
    }

    public function testThreadParticipants()
    {
        $this->assertEquals(2, $this->thread->participants->count());
    }

    public function testPostsThread()
    {
        $this->assertEquals($this->thread->id, $this->thread->posts->first()->thread->id);
    }

    public function testPostsAuthor()
    {
        $this->assertNotNull($this->thread->posts->first()->author);
    }
}
