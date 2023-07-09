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

class RolesController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Roles.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Roles');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && ! isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Role::all();
            } else {
                return View('la.roles.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Roles'),
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
     * Display a hierarchy of the Roles.
     *
     * @return mixed
     */
    public function hierarchy_view()
    {
        $module = LAModule::get('Roles');
        $roles = Role::orderBy('id', 'ASC')->orderBy('parent', 'ASC')->get();
        $parent_roles = Role::where('parent', null)->orderBy('id', 'ASC')->get();
        if (LAModule::hasAccess($module->id)) {
            return View('la.roles.hierarchy', [
                'show_actions' => $this->show_action,
                'listing_cols' => LAModule::getListingColumns('Roles'),
                'module' => $module,
                'parent_roles' => $parent_roles
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update Menu Hierarchy.
     *
     * @return mixed
     */
    public function update_role_hierarchy(Request $request)
    {
        $parents = $request->input('jsonData');
        $parent_id = null;

        for ($i = 0; $i < count($parents); $i++) {
            $this->apply_role_hierarchy($parents[$i], $i + 1, $parent_id);
        }

        return $parents;
    }

    /**
     * Save role hierarchy Recursively.
     *
     * @param $roleItem role Item Array
     * @param $num Hierarchy number
     * @param $parent_id Parent ID
     */
    public function apply_role_hierarchy($roleItem, $num, $parent_id)
    {
        // echo "apply_hierarchy: ".json_encode($roleItem)." - ".$num." - ".$parent_id."  <br><br>\n\n";
        $role = Role::find($roleItem['id']);
        $role->parent = $parent_id;
        $role->save();

        // apply hierarchy to children if exists
        if (isset($roleItem['children'])) {
            for ($i = 0; $i < count($roleItem['children']); $i++) {
                $this->apply_role_hierarchy($roleItem['children'][$i], $i + 1, $roleItem['id']);
            }
        }
    }

    /**
     * Show the form for creating a new role.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created role in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess('Roles', 'create')) {
            if ($request->ajax() && ! isset($request->quick_add)) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Roles', $request);

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

            $request->merge([
                'name' => str_replace(' ', '_', strtoupper(trim($request->name)))
            ]);

            $insert_id = LAModule::insert('Roles', $request);

            $modules = LAModule::all();
            foreach ($modules as $module) {
                LAModule::setDefaultRoleAccess($module->id, $insert_id, 'readonly');
            }

            $role = Role::find($insert_id);
            $perm = Permission::where('name', 'ADMIN_PANEL')->first();
            $role->attachPermission($perm);

            // Add LALog
            LALog::make('Roles.ROLE_CREATED', [
                'title' => 'Role Created',
                'module_id' => 'Roles',
                'context_id' => $role->id,
                'content' => $role,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $role,
                    'message' => 'Role updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute').'/roles')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute').'.roles.index');
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
     * Display the specified role.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id role ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess('Roles', 'view')) {
            $role = Role::find($id);
            if (isset($role->id)) {
                if ($request->ajax() && ! isset($request->_pjax)) {
                    return $role;
                } else {
                    $module = LAModule::get('Roles');
                    $module->row = $role;

                    $modules_arr = DB::table('la_modules')->get();
                    $modules_access = [];
                    foreach ($modules_arr as $module_obj) {
                        $module_obj->accesses = LAModule::getRoleAccess($module_obj->id, $id)[0];
                        $modules_access[] = $module_obj;
                    }

                    return view('la.roles.show', [
                        'module' => $module,
                        'module_users' => LAModule::get('Users'),
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => 'no-padding',
                        'modules_access' => $modules_access
                    ])->with('role', $role);
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
                        'record_name' => ucfirst('role'),
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
     * Show the form for editing the specified role.
     *
     * @param int $id role ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess('Roles', 'edit')) {
            $role = Role::find($id);
            if (isset($role->id)) {
                $module = LAModule::get('Roles');

                $module->row = $role;

                return view('la.roles.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('role', $role);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('role'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update the specified role in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id role ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess('Roles', 'edit')) {
            if ($request->ajax()) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Roles', $request, true);

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

            $request->merge([
                'name' => str_replace(' ', '_', strtoupper(trim($request->name)))
            ]);

            if ($request->name == 'SUPER_ADMIN') {
                $request->merge([
                    'parent' => 1
                ]);
            }

            $role_old = Role::find($id);

            if (isset($role_old->id)) {
                // Update Data
                LAModule::updateRow('Roles', $request, $id);

                $role_new = Role::find($id);

                // Add LALog
                LALog::make('Roles.ROLE_UPDATED', [
                    'title' => 'Role Updated',
                    'module_id' => 'Roles',
                    'context_id' => $role_new->id,
                    'content' => [
                        'old' => $role_old,
                        'new' => $role_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $role_new,
                        'message' => 'Role updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/roles')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.roles.index');
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
                        'record_name' => ucfirst('role'),
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
     * Remove the specified role from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id role ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess('Roles', 'delete')) {
            $role = Role::find($id);
            if (isset($role->id)) {
                $role->delete();

                // Add LALog
                LALog::make('Roles.ROLE_DELETED', [
                    'title' => 'Role Deleted',
                    'module_id' => 'Roles',
                    'context_id' => $role->id,
                    'content' => $role,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/roles')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.roles.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.roles.index');
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
        $module = LAModule::get('Roles');
        $listing_cols = LAModule::getListingColumns('Roles');

        $values = DB::table('roles')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Roles');

        for ($i = 0; $i < count($data->data); $i++) {
            $role = Role::find($data->data[$i]->id);

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
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/roles/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Roles', 'edit')) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/roles/'.$data->data[$i]->id.'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess('Roles', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.roles.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string) $output;
            }
        }
        $out->setData($data);

        return $out;
    }

    public function save_module_role_permissions(Request $request, $id)
    {
        if (Entrust::hasRole('SUPER_ADMIN')) {
            $role = Role::find($id);
            $module = LAModule::get('Roles');
            $module->row = $role;

            $modules_arr = DB::table('la_modules')->get();
            $modules_access = [];
            foreach ($modules_arr as $module_obj) {
                $module_obj->accesses = LAModule::getRoleAccess($module_obj->id, $id)[0];
                $modules_access[] = $module_obj;
            }

            $now = date('Y-m-d H:i:s');

            foreach ($modules_access as $module) {
                /* =============== role_la_module_fields =============== */

                foreach ($module->accesses->fields as $field) {
                    $field_name = $field['colname'].'_'.$module->id.'_'.$role->id;
                    $field_value = $request->$field_name;
                    if ($field_value == 0) {
                        $access = 'invisible';
                    } elseif ($field_value == 1) {
                        $access = 'readonly';
                    } elseif ($field_value == 2) {
                        $access = 'write';
                    }

                    $query = DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id']);
                    if ($query->count() == 0) {
                        DB::insert('insert into role_la_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field['id'], $access, $now, $now]);
                    } else {
                        DB::table('role_la_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access]);
                    }
                }

                /* =============== role_la_module =============== */

                $module_name = 'module_'.$module->id;
                if (isset($request->$module_name)) {
                    $view = 'module_view_'.$module->id;
                    $create = 'module_create_'.$module->id;
                    $edit = 'module_edit_'.$module->id;
                    $delete = 'module_delete_'.$module->id;
                    if (isset($request->$view)) {
                        $view = 1;
                    } else {
                        $view = 0;
                    }
                    if (isset($request->$create)) {
                        $create = 1;
                    } else {
                        $create = 0;
                    }
                    if (isset($request->$edit)) {
                        $edit = 1;
                    } else {
                        $edit = 0;
                    }
                    if (isset($request->$delete)) {
                        $delete = 1;
                    } else {
                        $delete = 0;
                    }

                    $query = DB::table('role_la_module')->where('role_id', $id)->where('module_id', $module->id);
                    if ($query->count() == 0) {
                        DB::insert('insert into role_la_module (role_id, module_id, acc_view, acc_create, acc_edit, acc_delete, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?)', [$id, $module->id, $view, $create, $edit, $delete, $now, $now]);
                    } else {
                        DB::table('role_la_module')->where('role_id', $id)->where('module_id', $module->id)->update(['acc_view' => $view, 'acc_create' => $create, 'acc_edit' => $edit, 'acc_delete' => $delete]);
                    }
                } else {
                    DB::table('role_la_module')->where('role_id', $id)->where('module_id', $module->id)->update(['acc_view' => 0, 'acc_create' => 0, 'acc_edit' => 0, 'acc_delete' => 0]);
                }
            }

            return redirect(config('laraadmin.adminRoute').'/roles/'.$id.'#tab-access');
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }
}
