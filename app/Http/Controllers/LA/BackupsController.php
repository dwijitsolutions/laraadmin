<?php

/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\LAModuleField;
use Collective\Html\FormFacade as Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;
use Yajra\DataTables\DataTables;

class BackupsController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Backups.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Backups');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && ! isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Backup::all();
            } else {
                return View('la.backups.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Backups'),
                    'module' => $module
                ]);
            }
        } else {
            if ($request->ajax() && ! isset($request->_pjax)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute').'/');
            }
        }
    }

    /**
     * Create Backup using Spatie Backup Library.
     *
     * @return Request
     */
    public function create_backup_ajax(Request $request)
    {
        if (LAModule::hasAccess('Backups', 'create')) {
            $exitCode = Artisan::call('backup:run');
            $outputStr = Artisan::output();

            // The backup just made is the newest one on the backup disk (config/backup.php)
            $destination = BackupDestination::create(config('backup.backup.destination.disks')[0], config('backup.backup.name'));
            $newest = $exitCode == 0 ? $destination->newestBackup() : null;
            if (! isset($newest)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => LAHelper::getLineWithString2($outputStr, 'mysqldump') != -1
                        ? 'Configure dump_binary_path in config/database.php. Check console for error details.'
                        : 'Error while creating Backup.',
                    'exitCode' => $exitCode,
                    'output' => $outputStr
                ]);
            }

            $request->merge([
                'name' => basename($newest->path(), '.zip'),
                'file_name' => $newest->path(),
                'backup_size' => Format::humanReadableSize($newest->sizeInBytes()),
            ]);
            $insert_id = LAModule::insert('Backups', $request);

            $backup = Backup::find($insert_id);

            LALog::make('Backups.BACKUP_CREATED', [
                'title' => 'Backup Created',
                'module_id' => 'Backups',
                'context_id' => $backup->id,
                'content' => $backup,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Backup successfully created.',
                'insert_id' => $insert_id,
                'exitCode' => $exitCode,
                'output' => $outputStr
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No rights to create Backup.'
            ]);
        }
    }

    /**
     * Remove the specified backup from storage.
     *
     * @param Request $request
     * @param int $id backup ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess('Backups', 'delete')) {
            $backup = Backup::find($id);
            if (isset($backup->id)) {
                $file = $this->backupFile($backup);
                if (isset($file)) {
                    $file[0]->delete($file[1]);
                }

                $backup->delete();

                // Add LALog
                LALog::make('Backups.BACKUP_DELETED', [
                    'title' => 'Backup Deleted',
                    'module_id' => 'Backups',
                    'context_id' => $backup->id,
                    'content' => $backup,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/backups')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.backups.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.backups.index');
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute').'/');
            }
        }
    }

    /**
     * Server side Datatable fetch via Ajax.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        if (! LAModule::hasAccess('Backups', 'view')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access'
            ], 403);
        }

        $module = LAModule::get('Backups');
        $listing_cols = LAModule::getListingColumns('Backups');

        $values = DB::table('backups')->select($listing_cols)->whereNull('deleted_at');
        $out = DataTables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Backups');

        for ($i = 0; $i < count($data->data); $i++) {
            $backup = Backup::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, '@')) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a href="'.url(config('laraadmin.adminRoute').'/downloadBackup/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                } elseif ($col == 'file_name') {
                    $data->data[$i]->$col = e($data->data[$i]->$col);
                }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Backups', 'edit')) {
                    $output .= '<a href="'.url(config('laraadmin.adminRoute').'/downloadBackup/'.$data->data[$i]->id).'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-download"></i></a>';
                }

                if (LAModule::hasAccess('Backups', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.backups.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string) $output;
            }
        }
        $out->setData($data);

        return $out;
    }

    public function downloadBackup($id)
    {
        // A backup holds the whole database (user accounts included), so viewing the list is not enough
        if (LAModule::hasAccess('Backups', 'create')) {
            $backup = Backup::find($id);
            $file = isset($backup->id) ? $this->backupFile($backup) : null;
            if (! isset($file)) {
                abort(404);
            }

            return $file[0]->download($file[1], basename($file[1]));
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No rights to download Backup.'
            ], 403);
        }
    }

    /**
     * Disk and path of a backup's file, or null when the file is missing.
     *
     * Backups are on the disk configured in config/backup.php. Older LaraAdmin versions kept them in storage/backups.
     *
     * @param Backup $backup
     * @return array|null [Filesystem, path]
     */
    protected function backupFile($backup)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($backup->file_name != '' && $disk->exists($backup->file_name)) {
            return [$disk, $backup->file_name];
        }
        $legacy = Storage::build(['driver' => 'local', 'root' => storage_path('backups')]);
        if ($backup->file_name != '' && $legacy->exists(basename($backup->file_name))) {
            return [$legacy, basename($backup->file_name)];
        }

        return null;
    }
}
