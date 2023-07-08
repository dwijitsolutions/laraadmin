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

use App\Models\Department;

class DepartmentsController extends Controller
{
    public $show_action = true;

    /**
     * Display a listing of the Departments.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $module = LAModule::get('Departments');

        if (LAModule::hasAccess($module->id)) {
            if ($request->ajax() && !isset($request->_pjax)) {
                // TODO: Implement good Query Builder
                return Department::all();
            } else {
                return View('la.departments.index', [
                    'show_actions' => $this->show_action,
                    'listing_cols' => LAModule::getListingColumns('Departments'),
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
     * Show the form for creating a new department.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created department in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        if (LAModule::hasAccess("Departments", "create")) {
            if ($request->ajax() && !isset($request->quick_add)) {
                $request->merge((array)json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules("Departments", $request);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax() || isset($request->quick_add)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->messages()
                    , 400]);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $insert_id = LAModule::insert("Departments", $request);

            $department = Department::find($insert_id);

            // Add LALog
            LALog::make("Departments.DEPARTMENT_CREATED", [
                'title' => "Department Created",
                'module_id' => 'Departments',
                'context_id' => $department->id,
                'content' => $department,
                'user_id' => Auth::user()->id,
                'notify_to' => "[]"
            ]);

            if ($request->ajax() || isset($request->quick_add)) {
                return response()->json([
                    'status' => 'success',
                    'object' => $department,
                    'message' => 'Department updated successfully!',
                    'redirect' => url(config('laraadmin.adminRoute') . '/departments')
                ], 201);
            } else {
                return redirect()->route(config('laraadmin.adminRoute') . '.departments.index');
            }
        } else {
            if ($request->ajax() || isset($request->quick_add)) {
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
     * Display the specified department.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id department ID
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (LAModule::hasAccess("Departments", "view")) {
            $department = Department::find($id);
            if (isset($department->id)) {
                if ($request->ajax() && !isset($request->_pjax)) {
                    return $department;
                } else {
                    $module = LAModule::get('Departments');
                    $module->row = $department;

                    return view('la.departments.show', [
                        'module' => $module,
                        'view_col' => $module->view_col,
                        'no_header' => true,
                        'no_padding' => "no-padding"
                    ])->with('department', $department);
                }
            } else {
                if ($request->ajax() && !isset($request->_pjax)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return view('errors.404', [
                        'record_id' => $id,
                        'record_name' => ucfirst("department"),
                    ]);
                }
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
     * Show the form for editing the specified department.
     *
     * @param int $id department ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if (LAModule::hasAccess("Departments", "edit")) {
            $department = Department::find($id);
            if (isset($department->id)) {
                $module = LAModule::get('Departments');

                $module->row = $department;

                return view('la.departments.edit', [
                    'module' => $module,
                    'view_col' => $module->view_col,
                ])->with('department', $department);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("department"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Update the specified department in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id department ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (LAModule::hasAccess("Departments", "edit")) {
            if ($request->ajax()) {
                $request->merge((array)json_decode($request->getContent()));
            }
            $rules = LAModule::validateRules("Departments", $request, true);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $validator->messages()
                    ], 400);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $department_old = Department::find($id);

            if (isset($department_old->id)) {
                // Update Data
                LAModule::updateRow("Departments", $request, $id);

                $department_new = Department::find($id);

                // Add LALog
                LALog::make("Departments.DEPARTMENT_UPDATED", [
                    'title' => "Department Updated",
                    'module_id' => 'Departments',
                    'context_id' => $department_new->id,
                    'content' => [
                        'old' => $department_old,
                        'new' => $department_new
                    ],
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'object' => $department_new,
                        'message' => 'Department updated successfully!',
                        'redirect' => url(config('laraadmin.adminRoute') . '/departments')
                    ], 200);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.departments.index');
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
                        'record_name' => ucfirst("department"),
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
                return redirect(config('laraadmin.adminRoute') . "/");
            }
        }
    }

    /**
     * Remove the specified department from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id department ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        if (LAModule::hasAccess("Departments", "delete")) {
            $department = Department::find($id);
            if (isset($department->id)) {
                $department->delete();

                // Add LALog
                LALog::make("Departments.DEPARTMENT_DELETED", [
                    'title' => "Department Deleted",
                    'module_id' => 'Departments',
                    'context_id' => $department->id,
                    'content' => $department,
                    'user_id' => Auth::user()->id,
                    'notify_to' => "[]"
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Record Deleted successfully!',
                        'redirect' => url(config('laraadmin.adminRoute') . '/departments')
                    ], 204);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.departments.index');
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Record not found'
                    ], 404);
                } else {
                    return redirect()->route(config('laraadmin.adminRoute') . '.departments.index');
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
        $module = LAModule::get('Departments');
        $listing_cols = LAModule::getListingColumns('Departments');

        $values = DB::table('departments')->select($listing_cols)->whereNull('deleted_at');
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = LAModuleField::getModuleFields('Departments');

        for ($i = 0; $i < count($data->data); $i++) {
            $department = Department::find($data->data[$i]->id);

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
                    $data->data[$i]->$col = '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/departments/' . $data->data[$i]->id) . '">' . $data->data[$i]->$col . '</a>';
                }
                // else if($col == "author") {
                //    $data->data[$i]->$col;
                // }
            }

            if ($this->show_action) {
                $output = '';
                if (LAModule::hasAccess("Departments", "edit")) {
                    $output .= '<a '.config('laraadmin.ajaxload').' href="' . url(config('laraadmin.adminRoute') . '/departments/' . $data->data[$i]->id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
                }

                if (LAModule::hasAccess("Departments", "delete")) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.departments.destroy', $data->data[$i]->id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit" data-toggle="tooltip" title="Delete"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i]->dt_action = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }
}
