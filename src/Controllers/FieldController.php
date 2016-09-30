<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace Dwij\Laraadmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Schema;

use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\Helpers\LAHelper;

class FieldController extends Controller
{
	
	public function __construct() {
		// for authentication (optional)
		// $this->middleware('auth');
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$module = Module::find($request->module_id);
		$module_id = $request->module_id;
		
		$field_id = ModuleFields::createField($request);
		
		return redirect()->route(config('laraadmin.adminRoute') . '.modules.show', [$module_id]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		// $ftypes = ModuleFieldTypes::getFTypes2();
		// $module = Module::find($id);
		// $module = Module::get($module->name);
		// return view('la.modules.show', [
		//     'no_header' => true,
		//     'no_padding' => "no-padding",
		//     'ftypes' => $ftypes
		// ])->with('module', $module);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$field = ModuleFields::find($id);
		
		$module = Module::find($field->module);
		$ftypes = ModuleFieldTypes::getFTypes2();
		
		$tables = LAHelper::getDBTables([]);
		
		return view('la.modules.field_edit', [
			'module' => $module,
			'ftypes' => $ftypes,
			'tables' => $tables
		])->with('field', $field);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$module_id = $request->module_id;
		
		ModuleFields::updateField($id, $request);
		
		return redirect()->route(config('laraadmin.adminRoute') . '.modules.show', [$module_id]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		// Get Context
		$field = ModuleFields::find($id);
		$module = Module::find($field->module);
		
		// Delete from Table
		Schema::table($module->name_db, function ($table) use ($field) {
			$table->dropColumn($field->colname);
		});

		// Delete Context
		$field->delete();
	}
	
	/**
	 * Check unique values for perticular field
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function check_unique_val(Request $request, $field_id)
	{
		$valExists = false;
		
		// Get Field
		$field = ModuleFields::find($field_id);
		// Get Module
		$module = Module::find($field->module);
		
		// echo $module->name_db." ".$field->colname." ".$request->field_value;
		$rowCount = DB::table($module->name_db)->where($field->colname, $request->field_value)->where("id", "!=", $request->row_id)->whereNull('deleted_at')->count();
		
		if($rowCount > 0) {
			$valExists = true;
		}
		
		return response()->json(['exists' => $valExists]);
	}
}
