<?php

namespace Tests\Feature\Access;

use App\Models\Backup;
use App\Models\LALog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsRoles;
use Tests\TestCase;

/**
 * Create, download and delete backups (spatie/laravel-backup, needs mysqldump), as every role.
 */
class BackupActionsTest extends TestCase
{
    use ActsAsRoles;

    /**
     * Backup files present before the test.
     *
     * @var array<int, string>
     */
    protected $backupsBefore = [];

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

        $this->backupsBefore = $this->backupDisk()->allFiles(config('backup.backup.name'));
    }

    /**
     * Remove backup files made by the test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        // Only the backups made by the test: the folder may hold real backups of this install
        $disk = $this->backupDisk();
        $disk->delete(array_diff($disk->allFiles(config('backup.backup.name')), $this->backupsBefore));
        if ($disk->allFiles(config('backup.backup.name')) === []) {
            $disk->deleteDirectory(config('backup.backup.name'));
        }
        File::delete(storage_path('backups/legacy-backup.zip'));
        if (File::isDirectory(storage_path('backups')) && File::allFiles(storage_path('backups')) === []) {
            File::deleteDirectory(storage_path('backups'));
        }

        parent::tearDown();
    }

    /**
     * The manager role creates, downloads and deletes a backup; read-only roles can list backups but not download
     * them.
     *
     * @return void
     */
    public function test_backups()
    {
        if (! $this->mysqldumpAvailable()) {
            $this->markTestSkipped('mysqldump is not installed');
        }

        // Create
        $this->asRole('guest')->post('/admin/create_backup_ajax')->assertRedirect('/login');
        $this->asRole('customer')->post('/admin/create_backup_ajax')->assertStatus(403);
        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->post('/admin/create_backup_ajax')->assertJsonPath('message', 'No rights to create Backup.');
        }
        $this->assertSame(0, Backup::count());

        $response = $this->asRole('manager')->post('/admin/create_backup_ajax');
        $this->assertSame('success', $response->json('status'), (string) $response->json('output'));
        $backup = Backup::find($response->json('insert_id'));
        $disk = $this->backupDisk();
        $this->assertTrue($disk->exists($backup->file_name));
        $this->assertStringEndsWith('.zip', $backup->file_name);
        $this->assertSame(1, LALog::where('type', 'BACKUP_CREATED')->where('context_id', $backup->id)->count());
        $this->asRole('viewer')->get('/admin/backup_dt_ajax')->assertStatus(200)->assertJsonPath('recordsTotal', 1);

        // Download
        $this->asRole('viewer')->get('/admin/downloadBackup/'.$backup->id)->assertStatus(403);
        $this->asRole('manager')->get('/admin/downloadBackup/'.$backup->id)->assertStatus(200)->assertDownload(basename($backup->file_name));
        $this->asRole('manager')->get('/admin/downloadBackup/9999')->assertStatus(404);

        // Delete
        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->delete('/admin/backups/'.$backup->id)->assertRedirect('/admin');
        }
        $this->assertTrue($disk->exists($backup->file_name));

        $this->asRole('manager')->delete('/admin/backups/'.$backup->id)->assertRedirect('/admin/backups');
        $this->assertFalse($disk->exists($backup->file_name));
        $this->assertNull(Backup::find($backup->id));
    }

    /**
     * Backups made by older LaraAdmin versions (in storage/backups) can still be downloaded and deleted.
     *
     * @return void
     */
    public function test_legacy_backups()
    {
        File::ensureDirectoryExists(storage_path('backups'));
        File::put(storage_path('backups/legacy-backup.zip'), 'zip');
        $backup = new Backup;
        $backup->forceFill(['name' => 'legacy-backup', 'file_name' => 'legacy-backup.zip', 'backup_size' => '3 B'])->save();

        $this->asRole('super_admin')->get('/admin/downloadBackup/'.$backup->id)->assertStatus(200)->assertDownload('legacy-backup.zip');
        $this->asRole('super_admin')->delete('/admin/backups/'.$backup->id)->assertRedirect('/admin/backups');
        $this->assertFileDoesNotExist(storage_path('backups/legacy-backup.zip'));

        // A backup row whose file is gone is still deleted
        $missing = new Backup;
        $missing->forceFill(['name' => 'gone', 'file_name' => 'gone.zip', 'backup_size' => '1 B'])->save();
        $this->asRole('super_admin')->get('/admin/downloadBackup/'.$missing->id)->assertStatus(404);
        $this->asRole('super_admin')->delete('/admin/backups/'.$missing->id)->assertRedirect('/admin/backups');
        $this->assertNull(Backup::find($missing->id));
    }

    /**
     * The disk backups are stored on (config/backup.php).
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function backupDisk()
    {
        return Storage::disk(config('backup.backup.destination.disks')[0]);
    }

    /**
     * Whether mysqldump can be run.
     *
     * @return bool
     */
    protected function mysqldumpAvailable()
    {
        exec('mysqldump --version 2>&1', $output, $exitCode);

        return $exitCode === 0;
    }
}
