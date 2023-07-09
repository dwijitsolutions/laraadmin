<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/***
 * LaraAdmin Module Field Controller
 */
class LAModuleFieldController extends Controller
{
    /**
     * Store a newly created Module Field via "Module Manager".
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $module = LAModule::find($request->module_id);
        $module_id = $request->module_id;

        $field_id = LAModuleField::createField($request);

        // Give Default Full Access to Super Admin
        $role = Role::where('name', 'SUPER_ADMIN')->first();
        LAModule::setDefaultFieldRoleAccess($field_id, $role->id, 'full');

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.show', [$module_id]);
    }

    /**
     * Show the form for editing of Module Field via "Module Manager".
     *
     * @param $id Field's ID to be Edited
     * @return $this
     */
    public function edit($id)
    {
        $field = LAModuleField::find($id);

        $module = LAModule::find($field->module);
        $ftypes = LAModuleFieldType::getFTypes2();

        $tables = LAHelper::getDBTables([]);

        return view('la.la_modules.field_edit', [
            'module' => $module,
            'ftypes' => $ftypes,
            'tables' => $tables
        ])->with('field', $field);
    }

    /**
     * Update the specified Module Field via "Module Manager".
     *
     * @param Request $request
     * @param $id Field's ID to be Updated
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $module_id = $request->module_id;

        LAModuleField::updateField($id, $request);

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.show', [$module_id]);
    }

    /**
     * Remove the specified Module Field from Database Context + Table.
     *
     * @param $id Field's ID to be Destroyed
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Get Context
        $field = LAModuleField::find($id);
        $module = LAModule::find($field->module);

        // Delete Field
        LAModuleField::deleteField($module->name, $field->colname);

        return redirect()->route(config('laraadmin.adminRoute').'.la_modules.show', [$module->id]);
    }

    /**
     * Check unique values for particular field.
     *
     * @param Request $request
     * @param $field_id Field ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_unique_val(Request $request, $field_id)
    {
        $valExists = false;

        // Get Field
        $field = LAModuleField::find($field_id);
        // Get Module
        $module = LAModule::find($field->module);

        // echo $module->name_db." ".$field->colname." ".$request->field_value;
        $rowCount = DB::table($module->name_db)->where($field->colname, $request->field_value)->where('id', '!=', $request->row_id)->whereNull('deleted_at')->count();

        if ($rowCount > 0) {
            $valExists = true;
        }

        return response()->json(['exists' => $valExists]);
    }

    /**
     * Save column visibility in listing/index view.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function module_field_listing_show_ajax(Request $request)
    {
        if ($request->state == 'true') {
            $state = 1;
        } else {
            $state = 0;
        }
        $module_field = LAModuleField::find($request->listid);
        if (isset($module_field->id)) {
            $module_field->listing_col = $state;
            $module_field->save();

            return response()->json(['status' => 'success', 'message' => 'Module field listing visibility saved to '.$state]);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Module field not found']);
        }
    }
}
