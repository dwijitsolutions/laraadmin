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
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Collective\Html\FormFacade as Form;
use App\Models\LAModule;
use App\Models\LAModuleField;

use App\Models\User;
use App\Models\Role;

class UsersController extends Controller
{
    public $show_action = false;

    /**
     * Display a listing of the Users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = LAModule::get('Users');

        if (LAModule::hasAccess($module->id)) {
            return View('la.users.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => LAModule::getListingColumns('Users'),
                'module' => $module
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (LAModule::hasAccess("Users", "view")) {
            $user = User::findOrFail($id);
            $context = $user->context();
            if (isset($user->id) && isset($context->id)) {
                if ($context->context_type == "Employee") {
                    return redirect(config('laraadmin.adminRoute') . '/employees/'.$context->id.'?_pjax=%23content-wrapper');
                } elseif ($context->context_type == "Customer") {
                    return redirect(config('laraadmin.adminRoute') . '/clients/'.$context->id.'?_pjax=%23content-wrapper');
                }
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("user"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('Users');
        $listing_cols = LAModule::getListingColumns('Users');

        $listing_cols_arr = array();

        foreach ($listing_cols as $listing_col) {
            $listing_cols_arr[] = "users.".$listing_col;
        }

        if (isset($request->filter_column) && $request->filter_column == "role_id" && $request->filter_column_value) {
            $values = Role::find($request->filter_column_value)->users()->select($listing_cols_arr);
        } else {
            $values = DB::table('users')->select($listing_cols)->whereNull('deleted_at');
        }
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Users');

        for ($i=0; $i < count($data->data); $i++) {
            $user = User::find($data->data[$i]->id);

            for ($j=0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, "@")) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute') . '/users/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }
        }
        $out->setData($data);
        return $out;
    }
}
