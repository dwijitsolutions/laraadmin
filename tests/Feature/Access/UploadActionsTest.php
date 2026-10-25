<?php

namespace Tests\Feature\Access;

use App\Models\LALog;
use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Concerns\ActsAsRoles;
use Tests\TestCase;

/**
 * Upload, rename, caption, publish and delete files in the Uploads manager, as every role.
 */
class UploadActionsTest extends TestCase
{
    use ActsAsRoles;

    /**
     * Files in storage/uploads before the test, to remove what the test adds.
     *
     * @var array<int, string>
     */
    protected $filesBefore = [];

    /**
     * Fresh DB with one user per role.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
        $this->createRoleUsers();
        $this->filesBefore = File::files(storage_path('uploads'));
    }

    /**
     * Remove uploaded files.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $before = array_map(fn ($file) => $file->getPathname(), $this->filesBefore);
        foreach (File::files(storage_path('uploads')) as $file) {
            if (! in_array($file->getPathname(), $before, true)) {
                File::delete($file->getPathname());
            }
        }
        File::deleteDirectory(storage_path('uploads/public'));

        parent::tearDown();
    }

    /**
     * The manager role uploads and manages its own files; read-only roles cannot; nobody but the Super Admin
     * touches files uploaded by others.
     *
     * @return void
     */
    public function test_upload_and_manage_files()
    {
        $upload = fn ($role, $name = 'photo.jpg') => $this->asRole($role)->post('/admin/upload_files', ['file' => UploadedFile::fake()->image($name)]);

        // Refused
        $upload('guest')->assertRedirect('/login');
        $upload('customer')->assertStatus(403);
        foreach (['admin', 'viewer'] as $role) {
            $upload($role)->assertJsonPath('status', 'failure')->assertJsonPath('message', 'Unauthorized Access');
        }
        $this->assertSame(1, Upload::count());

        // Upload
        $response = $upload('manager')->assertStatus(200)->assertJsonPath('status', 'success');
        $file = Upload::find($response->json('upload.id'));
        $this->assertSame($this->users['manager']->id, (int) $file->user_id);
        $this->assertFileExists($file->path);
        $this->assertSame(1, LALog::where('type', 'UPLOAD_CREATED')->where('context_id', $file->id)->count());
        $this->asRole('manager')->get('/admin/uploaded_files')->assertStatus(200)->assertJsonFragment(['id' => $file->id, 'name' => 'photo.jpg']);

        // Caption, name, visibility
        $edits = [
            ['/admin/uploads_update_caption', ['file_id' => $file->id, 'caption' => 'A caption']],
            ['/admin/uploads_update_filename', ['file_id' => $file->id, 'filename' => 'renamed.jpg']],
            ['/admin/uploads_update_public', ['file_id' => $file->id, 'public' => 'public']],
        ];
        foreach ($edits as [$url, $data]) {
            $this->asRole('viewer')->post($url, $data)->assertJsonPath('message', 'Unauthorized Access');
            $this->asRole('manager')->post($url, $data)->assertJsonPath('status', 'success');
        }
        $file->refresh();
        $this->assertSame(['A caption', 'renamed.jpg', 1], [$file->caption, $file->name, (int) $file->public]);
        $this->assertFileExists($file->path);
        $this->get('/files/'.$file->hash.'/renamed.jpg')->assertStatus(200);

        // A new name cannot move the file out of the uploads folder
        $this->asRole('manager')->post('/admin/uploads_update_filename', ['file_id' => $file->id, 'filename' => '../../public/moved.php'])
            ->assertJsonPath('status', 'success');
        $file->refresh();
        $this->assertSame('moved.php', $file->name);
        $this->assertSame(storage_path('uploads'), dirname($file->path));
        $this->assertFileDoesNotExist(public_path('moved.php'));
        $this->asRole('manager')->post('/admin/uploads_update_filename', ['file_id' => $file->id, 'filename' => '../'])
            ->assertJsonPath('message', 'Invalid file name');

        // Making it private again
        $this->asRole('manager')->post('/admin/uploads_update_public', ['file_id' => $file->id])->assertJsonPath('status', 'success');
        $this->assertSame(0, (int) $file->fresh()->public);

        // Files of others: only the Super Admin
        $seeded = Upload::where('id', '!=', $file->id)->first();
        $this->asRole('manager')->post('/admin/uploads_update_caption', ['file_id' => $seeded->id, 'caption' => 'Hijacked'])
            ->assertJsonPath('status', 'failure');
        $this->asRole('manager')->post('/admin/uploads_delete_file', ['file_id' => $seeded->id])->assertJsonPath('status', 'failure');
        $this->assertNotSame('Hijacked', $seeded->fresh()->caption);
        $this->asRole('super_admin')->post('/admin/uploads_update_caption', ['file_id' => $file->id, 'caption' => 'By SA'])
            ->assertJsonPath('status', 'success');

        // Delete
        $this->asRole('viewer')->post('/admin/uploads_delete_file', ['file_id' => $file->id])->assertJsonPath('message', 'Unauthorized Access');
        $this->asRole('manager')->post('/admin/uploads_delete_file', ['file_id' => $file->id])->assertJsonPath('status', 'success');
        $this->assertNull(Upload::find($file->id));
        $this->assertSame(1, LALog::where('type', 'UPLOAD_DELETED')->where('context_id', $file->id)->count());
    }

    /**
     * Uploaded HTML and SVG files are downloaded, not shown as pages of the site.
     *
     * @return void
     */
    public function test_html_and_svg_files_are_downloaded()
    {
        foreach (['page.html' => '<script>alert(1)</script>', 'image.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'] as $name => $content) {
            $response = $this->asRole('super_admin')->post('/admin/upload_files', [
                'file' => UploadedFile::fake()->createWithContent($name, $content), 'public' => 'public',
            ])->assertJsonPath('status', 'success');
            $file = Upload::find($response->json('upload.id'));

            $this->asRole('guest')->get('/files/'.$file->hash.'/'.$name)->assertStatus(200)->assertDownload($name);
        }

        $image = Upload::first();
        $this->get('/files/'.$image->hash.'/'.$image->name)->assertStatus(200)->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Refreshing upload paths (after copying a site) needs edit access to Uploads.
     *
     * @return void
     */
    public function test_update_local_upload_paths()
    {
        Upload::query()->update(['path' => '/old/server/storage/uploads/Hello-World-by-Tim-Bogdanov.jpg']);

        $this->asRole('guest')->get('/admin/update_local_upload_paths')->assertRedirect('/login');
        $this->asRole('customer')->get('/admin/update_local_upload_paths')->assertStatus(403);
        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->get('/admin/update_local_upload_paths')->assertRedirect('/admin');
        }
        $this->assertStringStartsWith('/old/server', Upload::first()->path);

        $this->asRole('manager')->get('/admin/update_local_upload_paths')->assertRedirect('/admin/uploads');
        $this->assertSame(storage_path('uploads/Hello-World-by-Tim-Bogdanov.jpg'), Upload::first()->path);
    }
}
