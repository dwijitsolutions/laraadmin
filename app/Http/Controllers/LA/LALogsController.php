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

class LALogsController extends Controller
{
    public $show_action = false;

    /**
     * Display a listing of the LA_logs.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('LA_logs');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && !isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return LALog::all();
            } else {
                return View('la.la_logs.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('LA_logs'),
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
     * Display the specified la_log.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id la_log ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        return redirect()->route(config('laraadmin.adminRoute') . '.la_logs.index');
    }

    /**
     * Server side Datatable fetch via Ajax
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('LA_logs');
        $listing_cols = LAModule::getListingColumns('LA_logs');
        $listing_cols[] = "created_at";

        $values = DB::table('la_logs')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('LA_logs');

        for ($i = 0; $i < count($data->data); $i++) {
            $la_log = LALog::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                $popup_value = $data->data[$i]->$col;
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, "@")) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                /*
                if($col == $module->view_col) {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/la_logs/' . $data->data[$i]->id) . '">' . $data->data[$i]->$col . '</a>';
                }
                */
                if ($col == "id") {
                    $data->data[$i]->$col = $data->data[$i]->$col." <a class='col_exp' log_id='".$data->data[$i]->$col."'></a>";
                } elseif ($col == "user_id") {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/users/' . $popup_value) . '">' . $data->data[$i]->$col . '</a>';
                } elseif ($col == "module_id" && isset($popup_value) && $popup_value != 0) {
                    $module = LAModule::find($popup_value);
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/' . $module->name_db) . '">' . $data->data[$i]->$col . '</a>';
                } elseif ($col == "notify_to") {
                    if ($data->data[$i]->$col != "[]") {
                    } else {
                        $data->data[$i]->$col = "";
                    }
                }
            }

            if ($this->show_action) {
                $output = '';
                $data->data[$i]->dt_action = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }

    /**
     * Get LALog Details via Ajax
     */
    public function get_lalog_details(Request $request, $id)
    {
        $lalog = LALog::find($id);

        return response()->json([
            'status' => 'success',
            'lalog' => $lalog
        ]);
    }
}
