<?php

namespace Tests\Feature\Access;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Upload;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Public site: blog, uploaded files and the inquiry form. PageAccessTest checks they open for every role.
 */
class PublicSiteTest extends TestCase
{
    /**
     * Fresh DB.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
    }

    /**
     * Blog listing, posts, categories; unknown ones are not found.
     *
     * @return void
     */
    public function test_blog()
    {
        $category = new BlogCategory;
        $category->forceFill(['name' => 'News', 'url' => 'news'])->save();
        BlogPost::where('url', 'hello-world')->update(['category_id' => $category->id]);

        $this->get('/blog')->assertStatus(200)->assertSee('Hello World');
        $this->get('/blog/hello-world')->assertStatus(200)->assertSee('Hello World from LaraAdmin Plus.');
        $this->get('/category/news')->assertStatus(200)->assertSee('Hello World');

        $this->get('/blog/no-such-post')->assertStatus(404);

        // Drafts are not public
        BlogPost::where('url', 'hello-world')->update(['status' => 'Draft']);
        $this->get('/blog/hello-world')->assertStatus(404);
        $this->get('/blog')->assertStatus(200)->assertDontSee('Hello World');
        $this->get('/category/no-such-category')->assertStatus(404);
    }

    /**
     * Public uploads download for anyone; private ones only for their owner or the Super Admin.
     *
     * @return void
     */
    public function test_uploaded_files()
    {
        $upload = Upload::first();
        $this->get('/files/'.$upload->hash.'/'.$upload->name)->assertStatus(200);
        $this->get('/files/'.$upload->hash.'/'.$upload->name.'?download=1')->assertStatus(200)->assertDownload();
        $this->get('/files/wrong-hash/'.$upload->name)->assertStatus(404);

        $upload->public = false;
        $upload->save();
        $this->get('/files/'.$upload->hash.'/'.$upload->name)->assertStatus(403);

        $admin = $this->registerSuperAdmin();
        $this->actingAs($admin)->get('/files/'.$upload->hash.'/'.$upload->name)->assertStatus(200);
    }

    /**
     * The inquiry form mails the site's address.
     *
     * @return void
     */
    public function test_inquiry_form()
    {
        $this->from('/')->post('/inquiry', ['name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Hello'])
            ->assertRedirect('/')->assertSessionHas('success');

        // phpunit.xml uses the array mailer; Mail::fake() does not record closure-based Mail::send()
        $sent = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $this->assertSame('New Inquiry - Visitor', $sent->first()->getOriginalMessage()->getSubject());
        $this->assertSame('hello@laraadmin.com', $sent->first()->getOriginalMessage()->getTo()[0]->getAddress());
    }
}
