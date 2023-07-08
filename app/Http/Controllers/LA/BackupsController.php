<?php
/**
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Collective\Html\FormFacade as Form;
use App\Helpers\LAHelper;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LALog;
use Illuminate\Support\Facades\Artisan;

use App\Models\Backup;

class BackupsController extends Controller
{
    public $show_action = true;
    public $backup_filepath = "/storage/app/http---localhost/";

    /**
     * Display a listing of the Backups.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Backups');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && !isset($request->_pjax)) {
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
            if ($request->ajax() && !isset($request->_pjax)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Create Backup using Spatie Backup Library
     *
     * @return \Illuminate\Http\Request
     */
    public function create_backup_ajax(Request $request)
    {
        if (LAModule::hasAccess("Backups", "create")) {
            $exitCode = Artisan::call('backup:run');
            $outputStr = Artisan::output();

            if (LAHelper::getLineWithString2($outputStr, "Copying ") == -1) {
                if (LAHelper::getLineWithString2($outputStr, "mysqldump: No such file or directory") != -1) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "Configure dump_command_path in config/database.php. Check console for error details.",
                        'exitCode' => $exitCode,
                        'output' => $outputStr
                    ]);
                }
                return response()->json([
                    'status' => 'failed',
                    'message' => "Error while creating Backup.",
                    'exitCode' => $exitCode,
                    'output' => $outputStr
                ]);
            } else {
                $dataStr = LAHelper::getLineWithString2($outputStr, "Copying ");
                $dataStr = str_replace("Copying ", "", $dataStr);
                $dataStr = substr($dataStr, 0, strpos($dataStr, ")"));

                $file_name = substr($dataStr, 0, strpos($dataStr, "(") - 1);
                $name = str_replace(".zip", "", $file_name);
                $backup_size = substr($dataStr, strpos($dataStr, "(") + 7);


                $request->merge([
                    'name' => $name,
                    'file_name' => $file_name,
                    'backup_size' => $backup_size,
                ]);
                $insert_id = LAModule::insert("Backups", $request);

                $backup = Backup::find($insert_id);

                // Add LALog
                LALog::make("Backups.BACKUP_CREATED", [
                    'title' => "Backup Created",
                    'module_id' => 'Backups',
                    'context_id' => $backup->id,
                    'content' => $backup,
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Backup successfully created.',
                    'insert_id' => $insert_id,
                    'exitCode' => $exitCode,
                    'output' => $outputStr
                ]);
            }
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
     * @param \Illuminate\Http\Request $request
     * @param int $id backup ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess("Backups", "delete")) {
            $backup = Backup::find($id);
            if (isset($backup->id)) {
                $path = str_replace("/storage", "", $this->backup_filepath. $backup->file_name);

                unlink(storage_path($path));

                $backup->delete();

                // Add LALog
                LALog::make("Backups.BACKUP_DELETED", [
                    'title' => "Backup Deleted",
                    'module_id' => 'Backups',
                    'context_id' => $backup->id,
                    'content' => $backup,
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute') . '/backups')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.backups.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.backups.index');
                }
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized Access'
                ], 403);
            } else {
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Server side Datatable fetch via Ajax
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('Backups');
        $listing_cols = LAModule::getListingColumns('Backups');

        $values = DB::table('backups')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Backups');

        for ($i = 0; $i < count($data->data); $i++) {
            $backup = Backup::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, "@")) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a href="'.url(config('laraadmin.adminRoute') . '/downloadBackup/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                } elseif ($col == "file_name") {
                    $data->data[$i]->$col = $this->backup_filepath.$data->data[$i]->$col;
                }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess("Backups", "edit")) {
                    $output .= '<a href="'.url(config('laraadmin.adminRoute') . '/downloadBackup/'.$data->data[$i]->id).'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-download"></i></a>';
                }

                if (LAModule::hasAccess("Backups", "delete")) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.backups.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }

    public function downloadBackup($id)
    {
        $module = LAModule::get('Backups');
        if (LAModule::hasAccess($module->id)) {
            $backup = Backup::find($id);

            $path = str_replace("/storage", "", $this->backup_filepath.$backup->file_name);

            return response()->download(storage_path($path));
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'No rights to download Backup.'
            ]);
        }
    }
}
