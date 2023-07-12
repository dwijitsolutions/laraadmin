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
use App\Models\Customer;
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
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CustomersController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Customers.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Customers');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && ! isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Customer::all();
            } else {
                return View('la.customers.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Customers'),
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
     * Show the form for creating a new customer.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created customer in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess('Customers', 'create')) {
            if ($request->ajax() && ! isset($request->quick_add)) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Customers', $request);

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

            $insert_id = LAModule::insert('Customers', $request);

            $customer = Customer::find($insert_id);

            // Add LALog
            LALog::make('Customers.CUSTOMER_CREATED', [
                'title' => 'Customer '.$customer->name.' Created',
                'module_id' => 'Customers',
                'context_id' => $customer->id,
                'content' => $customer,
                'user_id' => Auth::user()->id,
                'notify_to' => '[]'
            ]);

            // Create User
            if (isset($request->create_user)) {
                // Check if User Already Present
                if (User::where('email', $request->email_primary)->first()) {
                    // generate password
                    $password = LAHelper::gen_password();

                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email_primary,
                        'password' => bcrypt($password),
                        'context_id' => $insert_id,
                    ]);

                    // attach customer role
                    $role = Role::where('name', 'CUSTOMER')->first();
                    $user->attachRole($role);

                    // Add LALog
                    LALog::make('Users.USER_CREATED', [
                        'title' => 'User/Customer '.$user->name.' Created',
                        'module_id' => 'Users',
                        'context_id' => $user->id,
                        'content' => $user,
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);

                    if (LAHelper::is_mail()) {
                        // Send mail to User his Password
                        Mail::send('emails.send_login_cred', ['user' => $user, 'password' => $password], function ($m) use ($user) {
                            $m->from('hello@laraadmin.com', 'LaraAdmin');
                            $m->to($user->email, $user->name)->subject('LaraAdmin - Your Login Credentials');
                        });
                    } else {
                        Log::info('User created: username: '.$user->email.' Password: '.$password);
                    }
                }
            }

            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $customer,
                    'message' => 'Customer updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute').'/customers')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute').'.customers.index');
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
     * Display the specified customer.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id customer ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess('Customers', 'view')) {
            $customer = Customer::find($id);
            if (isset($customer->id)) {
                if ($request->ajax() && ! isset($request->_pjax)) {
                    return $customer;
                } else {
                    $module = LAModule::get('Customers');
                    $module->row = $customer;

                    return view('la.customers.show', [
                        'module' => $module,
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => 'no-padding'
                    ])->with('customer', $customer);
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
                        'record_name' => ucfirst('customer'),
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
     * Show the form for editing the specified customer.
     *
     * @param int $id customer ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess('Customers', 'edit')) {
            $customer = Customer::find($id);
            if (isset($customer->id)) {
                $module = LAModule::get('Customers');

                $module->row = $customer;

                return view('la.customers.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('customer', $customer);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst('customer'),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute').'/');
        }
    }

    /**
     * Update the specified customer in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id customer ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess('Customers', 'edit')) {
            if ($request->ajax()) {
                $request->merge((array) json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules('Customers', $request, true);

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

            $customer_old = Customer::find($id);

            if (isset($customer_old->id)) {
                // Update Data
                LAModule::updateRow('Customers', $request, $id);

                $customer_new = Customer::find($id);

                // Add LALog
                LALog::make('Customers.CUSTOMER_UPDATED', [
                    'title' => 'Customer '.$customer_new->name.' Updated',
                    'module_id' => 'Customers',
                    'context_id' => $customer_new->id,
                    'content' => [
                        'old' => $customer_old,
                        'new' => $customer_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                // Update User
                $user_old = User::get($id, 'Customer');
                $user = User::get($id, 'Customer');

                if (isset($user_old->id)) {
                    $user->name = $request->name;
                    $user->email = $request->email_primary;
                    $user->save();

                    // Add LALog
                    LALog::make('Users.USER_UPDATED', [
                        'title' => 'User/Customer '.$user->name.' Updated',
                        'module_id' => 'Users',
                        'context_id' => $user->id,
                        'content' => [
                            'old' => $user_old,
                            'new' => $user
                        ],
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);
                }

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $customer_new,
                        'message' => 'Customer updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/customers')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.customers.index');
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
                        'record_name' => ucfirst('customer'),
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
     * Remove the specified customer from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id customer ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess('Customers', 'delete')) {
            $customer = Customer::find($id);
            if (isset($customer->id)) {
                $customer->delete();

                // Add LALog
                LALog::make('Customers.CUSTOMER_DELETED', [
                    'title' => 'Customer '.$customer->name.' Deleted',
                    'module_id' => 'Customers',
                    'context_id' => $customer->id,
                    'content' => $customer,
                    'user_id' => Auth::user()->id,
                    'notify_to' => '[]'
                ]);

                // Delete User
                $user = User::get($id, 'Customer');
                if (isset($user->id)) {
                    $user->delete();

                    // Add LALog
                    LALog::make('Users.USER_DELETED', [
                        'title' => 'User/Customer '.$user->name.' Deleted',
                        'module_id' => 'Users',
                        'context_id' => $user->id,
                        'content' => $user,
                        'user_id' => Auth::user()->id,
                        'notify_to' => '[]'
                    ]);
                }

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute').'/customers')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.customers.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute').'.customers.index');
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
        $module = LAModule::get('Customers');
        $listing_cols = LAModule::getListingColumns('Customers');

        $values = DB::table('customers')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Customers');

        for ($i = 0; $i < count($data->data); $i++) {
            $customer = Customer::find($data->data[$i]->id);

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
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/customers/'.$data->data[$i]->id).'">'.$data->data[$i]->$col.'</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess('Customers', 'edit')) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="'.url(config('laraadmin.adminRoute').'/customers/'.$data->data[$i]->id.'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess('Customers', 'delete')) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute').'.customers.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string) $output;
            }
        }
        $out->setData($data);

        return $out;
    }
}
