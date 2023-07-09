<?php
/***
 * Controller generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\Permission;
use App\Models\Role;
use Collective\Html\FormFacade as Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laraadmin\Entrust\EntrustFacade as Entrust;
use Yajra\DataTables\DataTables;

class PermissionsController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Permissions');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && ! isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Permission::all();
            } else {
                return View('la.permissions.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Permissions'),
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
     * Show the form for creating a new permission.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created permission in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess('Permissions', 'create')) {
            if ($request->ajax() && ! isset($request->quick_add)) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Permissions', $request);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax() || isset($request->quick_add)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->errors(), 400]);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $insert_id = LAModule::insert('Permissions', $request);

            $permission = Permission::find($insert_id);

            // Add LALog
            LALog::make('Permissions.PERMISSION_CREATED', [
                'title' => 'Permission Created',
                'module_id' => 'Permissions',
                'context_id' => $permission->id,
                'content' => $permission,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $permission,
                    'message' => 'Permission updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute').'/permissions')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute').'.permissions.index');
            }
        } else {
            if ($request->ajax() || isset($request->quick_add)) {
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
     * Display the specified permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id permission ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess('Permissions', 'view')) {
            $permission = Permission::find($id);
            if (isset($permission->id)) {
                if ($request->ajax() && ! isset($request->_pjax)) {
                    return $permission;
                } else {
                    $module = LAModule::get('Permissions');
                    $module->row = $permission;

                    $roles = Role::all();

                    return view('la.permissions.show', [
                        'module' => $module,
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => 'no-padding',
                        'roles' => $roles
                    ])->with('permission', $permission);
                }
            } else {
                if ($request->ajax() && ! isset($request->_pjax)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return view('errors.404', [
                        'record_id' => $id,
                        'record_name' => ucfirst('permission'),
                    ]);
                }
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
     * Show the form for editing the specified permission.
     *
     * @param int $id permission ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess('Permissions', 'edit')) {
            $permission = Permission::find($id);
            if (isset($permission->id)) {
                $module = LAModule::get('Permissions');

                $module->row = $permission;

                return view('la.permissions.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('permission', $permission);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('permission'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update the specified permission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id permission ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess('Permissions', 'edit')) {
            if ($request->ajax()) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Permissions', $request, true);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $permission_old = Permission::find($id);

            if (isset($permission_old->id)) {
                // Update Data
                LAModule::updateRow('Permissions', $request, $id);

                $permission_new = Permission::find($id);

                // Add LALog
                LALog::make('Permissions.PERMISSION_UPDATED', [
                    'title' => 'Permission Updated',
                    'module_id' => 'Permissions',
                    'context_id' => $permission_new->id,
                    'content' => [
                        'old' => $permission_old,
                        'new' => $permission_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $permission_new,
                        'message' => 'Permission updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/permissions')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.permissions.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return view('errors.404', [
                        'record_id' => $id,
                        'record_name' => ucfirst('permission'),
                    ]);
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
     * Remove the specified permission from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id permission ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess('Permissions', 'delete')) {
            $permission = Permission::find($id);
            if (isset($permission->id)) {
                $permission->delete();

                // Add LALog
                LALog::make('Permissions.PERMISSION_DELETED', [
                    'title' => 'Permission Deleted',
                    'module_id' => 'Permissions',
                    'context_id' => $permission->id,
                    'content' => $permission,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/permissions')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.permissions.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.permissions.index');
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function dtajax(Request $request)
    {
        $module = LAModule::get('Permissions');
        $listing_cols = LAModule::getListingColumns('Permissions');

        $values = DB::table('permissions')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Permissions');

        for ($i = 0; $i < count($data->data); $i++) {
            $permission = Permission::find($data->data[$i]->id);

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
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/permissions/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Permissions', 'edit')) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/permissions/'.$data->data[$i]->id.'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess('Permissions', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.permissions.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string) $output;
            }
        }
        $out->setData($data);

        return $out;
    }

    /**
     * Save the  permissions for role in permission view.
     *
     * @param  int  $id
     * @return Redirect to permisssions page
     */
    public function save_permissions(Request $request, $id)
    {
        if (Entrust::hasRole('SUPER_ADMIN')) {
            $permission = Permission::find($id);
            $module = LAModule::get('Permissions');
            $module->row = $permission;
            $roles = Role::all();

            foreach ($roles as $role) {
                $permi_role_id = 'permi_role_'.$role->id;
                $permission_set = $request->$permi_role_id;
                if (isset($permission_set)) {
                    $query = DB::table('permission_role')->where('permission_id', $id)->where('role_id', $role->id);
                    if ($query->count() == 0) {
                        DB::insert('insert into permission_role (permission_id, role_id) values (?, ?)', [$id, $role->id]);
                    }
                } else {
                    $query = DB::table('permission_role')->where('permission_id', $id)->where('role_id', $role->id);
                    if ($query->count() > 0) {
                        DB::delete('delete from permission_role where permission_id = "'.$id.'" AND role_id = "'.$role->id.'" ');
                    }
                }
            }

            return redirect(config('laraadmin.adminRoute').'/permissions/'.$id.'#tab-access');
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }
}
