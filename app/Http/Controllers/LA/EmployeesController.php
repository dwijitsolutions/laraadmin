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
use App\Models\Employee;
use App\Models\LAConfig;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\Role;
use App\Models\User;
use Collective\Html\FormFacade as Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeesController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Employees.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Employees');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && ! isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Employee::all();
            } else {
                return View('la.employees.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Employees'),
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
     * Show the form for creating a new employee.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created employee in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess('Employees', 'create')) {
            if ($request->ajax() && ! isset($request->quick_add)) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Employees', $request);

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

            $insert_id = LAModule::insert('Employees', $request);

            $employee = Employee::find($insert_id);

            // Add LALog
            LALog::make('Employees.EMPLOYEE_CREATED', [
                'title' => 'Employee '.$employee->name.' Created',
                'module_id' => 'Employees',
                'context_id' => $employee->id,
                'content' => $employee,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            // generate password
            $password = LAHelper::gen_password();

            // Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email_primary,
                'password' => bcrypt($password),
                'context_id' => $insert_id
            ]);

            // update user role
            $roles = (array) $request->{'role[]'};
            foreach ($roles as $role) {
                $role = Role::find($role);
                $user->attachRole($role);
            }

            LALog::make('Users.USER_CREATED', [
                'title' => 'User '.$user->name.' Created',
                'module_id' => 'Users',
                'context_id' => $user->id,
                'content' => $user,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            if (LAHelper::is_mail()) {
                // Send mail to User for Password
                Mail::send('emails.send_login_cred', ['user' => $user, 'password' => $password], function ($m) use ($user) {
                    $m->from(LAConfig::getByKey('default_email'), LAConfig::getByKey('sitename'));
                    $m->to($user->email, $user->name)->subject(LAConfig::getByKey('sitename').' - Your Login Credentials');
                });
            } else {
                Log::info('User created: username: '.$user->email.' Password: '.$password);
            }
            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $employee,
                    'message' => 'Employee updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute').'/employees')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute').'.employees.index');
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
     * Display the specified employee.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id employee ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess('Employees', 'view')) {
            $employee = Employee::find($id);
            if (isset($employee->id)) {
                if ($request->ajax() && ! isset($request->_pjax)) {
                    return $employee;
                } else {
                    $module = LAModule::get('Employees');
                    $module->row = $employee;

                    // Get User Table Information
                    $user = User::get($id, 'Employee');

                    return view('la.employees.show', [
                        'user' => $user,
                        'module' => $module,
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => 'no-padding'
                    ])->with('employee', $employee);
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
                        'record_name' => ucfirst('employee'),
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
     * Show the form for editing the specified employee.
     *
     * @param int $id employee ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess('Employees', 'edit')) {
            $employee = Employee::find($id);
            if (isset($employee->id)) {
                $module = LAModule::get('Employees');

                $module->row = $employee;

                // Get User Table Information
                $user = User::get($id, 'Employee');

                return view('la.employees.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                    'user' => $user,
                ])->with('employee', $employee);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('employee'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update the specified employee in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id employee ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess('Employees', 'edit')) {
            if ($request->ajax()) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Employees', $request, true);

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

            $employee_old = Employee::find($id);

            if (isset($employee_old->id)) {
                // Update Data
                LAModule::updateRow('Employees', $request, $id);

                $employee_new = Employee::find($id);

                // Add LALog
                LALog::make('Employees.EMPLOYEE_UPDATED', [
                    'title' => 'Employee '.$employee_new->name.' Updated',
                    'module_id' => 'Employees',
                    'context_id' => $employee_new->id,
                    'content' => [
                        'old' => $employee_old,
                        'new' => $employee_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                // Update User
                $user_old = User::get($id, 'Employee');
                $user = User::get($id, 'Employee');
                $user->name = $request->name;
                $user->email = $request->email_primary;
                $user->save();

                // update user role
                $user->detachRoles();
                $key = 'role[]';
                $roles = (array) $request->{'role[]'};
                foreach ($roles as $role) {
                    $role = Role::find($role);
                    $user->attachRole($role);
                }

                LALog::make('Users.USER_UPDATED', [
                    'title' => 'User '.$user->name.' Updated',
                    'module_id' => 'Users',
                    'context_id' => $user->id,
                    'content' => [
                        'old' => $user_old,
                        'new' => $user
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $employee_new,
                        'message' => 'Employee updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/employees')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.employees.index');
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
                        'record_name' => ucfirst('employee'),
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
     * Remove the specified employee from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id employee ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess('Employees', 'delete')) {
            $employee = Employee::find($id);
            if (isset($employee->id)) {
                $employee->delete();

                // Add LALog
                LALog::make('Employees.EMPLOYEE_DELETED', [
                    'title' => 'Employee '.$employee->name.' Deleted',
                    'module_id' => 'Employees',
                    'context_id' => $employee->id,
                    'content' => $employee,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                // Delete User
                $user = User::get($id, 'Employee');
                if (isset($user->id)) {
                    $user->delete();
                }
                LALog::make('Users.USER_DELETED', [
                    'title' => 'User '.$user->name.' Deleted',
                    'module_id' => 'Users',
                    'context_id' => $user->id,
                    'content' => $user,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/employees')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.employees.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.employees.index');
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
        $module = LAModule::get('Employees');
        $listing_cols = LAModule::getListingColumns('Employees');

        $values = DB::table('employees')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Employees');

        for ($i = 0; $i < count($data->data); $i++) {
            $employee = Employee::find($data->data[$i]->id);

            for ($j = 0; $j < count($listing_cols); $j++) {
                $col = $listing_cols[$j];
                if (isset($fields_popup[$col]) && $fields_popup[$col]->field_type_str == 'Image') {
                    if ($data->data[$i]->$col != 0) {
                        $img = \App\Models\Upload::find($data->data[$i]->$col);
                        if (isset($img->name)) {
                            $data->data[$i]->$col = '<img class="img-circle" src="'.$img->url().'?s=50">';
                        } else {
                            $data->data[$i]->$col = '<i class="menu-icon fa fa-user bg-red"></i>';
                        }
                    } else {
                        $data->data[$i]->$col = '';
                    }
                }
                if (isset($fields_popup[$col]) && str_starts_with($fields_popup[$col]->popup_vals, '@')) {
                    if ($col == $module->view_col) {
                        $data->data[$i]->$col = LAModuleField::getFieldValue($fields_popup[$col], $data->data[$i]->$col);
                    } else {
                        $data->data[$i]->$col = LAModuleField::getFieldLink($fields_popup[$col], $data->data[$i]->$col);
                    }
                }
                if ($col == $module->view_col) {
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/employees/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Employees', 'edit')) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/employees/'.$data->data[$i]->id.'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess('Employees', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.employees.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
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
     * Change Employee Password.
     *
     * @return
     */
    public function change_password($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
            'password_confirmation' => 'required|min:6|same:password'
        ]);

        if ($validator->fails()) {
            return Redirect::to(config('laraadmin.adminRoute').'/employees/'.$id)->withErrors($validator);
        }

        $employee = Employee::find($id);
        $user = User::get($employee->id, 'Employee');
        $user->password = bcrypt($request->password);
        $user->save();

        Session::flash('success_message', 'Password is successfully changed');

        // Send mail to User his new Password
        if (LAHelper::is_mail()) {
            // Send mail to User his new Password
            Mail::send('emails.send_login_cred_change', ['user' => $user, 'password' => $request->password], function ($m) use ($user) {
                $m->from(LAConfig::getByKey('default_email'), LAConfig::getByKey('sitename'));
                $m->to($user->email, $user->name)->subject('LaraAdmin - Login Credentials chnaged');
            });
        } else {
            Log::info('User change_password: username: '.$user->email.' Password: '.$request->password);
        }

        return redirect(config('laraadmin.adminRoute').'/employees/'.$id.'#tab-account-settings');
    }
}
