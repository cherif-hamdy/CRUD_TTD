<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_can_read_all_posts()
    {
        $post = Post::factory()->create();

        $response = $this->get('/posts');

        $response->assertSee($post->title);
    }

    public function test_user_can_read_single_post()
    {
        $post = Post::factory()->create();

        $response = $this->get('/posts/' . $post->id);

        $response->assertSee($post->title)->assertSee($post->desc);
    }

    public function test_authenticated_user_can_create_post()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->create();

        $this->post('/posts', $post->toArray());

        $this->assertDatabaseHas('posts', ['title' => $post->title, 'id' => $post->id, 'desc' => $post->desc]);
    }

    public function test_unauthenticated_user_cannot_create_post()
    {
        $post = Post::factory()->create();

        $this->post('/posts', $post->toArray())->assertRedirect('/login');

    }

    public function test_post_require_title()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->make(['title' => null]);

        $this->post('/posts', $post->toArray())->assertSessionHasErrors('title');

    }

    public function test_post_require_desc()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->make(['desc' => null]);

        $this->post('/posts', $post->toArray())->assertSessionHasErrors('desc');

    }

    public function test_authorized_user_can_update_post()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $post->title = "updated title";

        $this->put('/posts/' . $post->id, $post->toArray());

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => $post->title]);
    }

    public function test_unauthorized_user_cannot_update_post()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->create();

        $post->title = "updated title";

        $this->put('/posts/' . $post->id, $post->toArray())->assertStatus(403);

    }

    public function test_authorized_user_can_delete_post()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $this->delete('/posts/'.$post->id);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_unauthorized_user_cannot_delete_post()
    {
        $this->actingAs(User::factory()->create());

        $post = Post::factory()->create();

        $this->delete('/posts/'.$post->id)->assertStatus(403);


    }
}
