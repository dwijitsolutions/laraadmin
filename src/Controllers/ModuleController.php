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
use Dwij\Laraadmin\Helpers\LAHelper;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\CodeGenerator;
use App\Role;

class ModuleController extends Controller
{
	
	public function __construct() {
		// for authentication (optional)
		$this->middleware('auth');
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$modules = Module::all();
		
		return View('la.modules.index', [
			'modules' => $modules
		]);
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
		$module_id = Module::generateBase($request->name,$request->icon);
		
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
		$ftypes = ModuleFieldTypes::getFTypes2();
		$module = Module::find($id);
		$module = Module::get($module->name);
		
		$tables = LAHelper::getDBTables([]);
		$modules = LAHelper::getModuleNames([]);		
		
		// Get Module Access for all roles
		$roles = Module::getRoleAccess($id);
		
		return view('la.modules.show', [
			'no_header' => true,
			'no_padding' => "no-padding",
			'ftypes' => $ftypes,
			'tables' => $tables,
			'modules' => $modules,
			'roles' => $roles
		])->with('module', $module);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//
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
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//
	}
	
	/**
	 * Generate Modules CRUD + Model
	 *
	 * @param  int  $module_id
	 * @return \Illuminate\Http\Response
	 */
	public function generate_crud($module_id)
	{
		$module = Module::find($module_id);
		$module = Module::get($module->name);
		
		$config = CodeGenerator::generateConfig($module->name,$module->fa_icon);
		
		CodeGenerator::createController($config);
		CodeGenerator::createModel($config);
		CodeGenerator::createViews($config);
		CodeGenerator::appendRoutes($config);
		CodeGenerator::addMenu($config);

		// Set Module Generated = True
		$module = Module::find($module_id);
		$module->is_gen='1';
		$module->save();
	}
	
	/**
	 * Generate Modules Migrations
	 *
	 * @param  int  $module_id
	 * @return \Illuminate\Http\Response
	 */
	public function generate_migr($module_id)
	{
		$module = Module::find($module_id);
		$module = Module::get($module->name);
		CodeGenerator::generateMigration($module->name_db, true);
	}

	/**
	 * Generate Modules Migrations and CRUD Model
	 *
	 * @param  int  $module_id
	 * @return \Illuminate\Http\Response
	 */
	public function generate_migr_crud($module_id)
	{
		$module = Module::find($module_id);
		$module = Module::get($module->name);
		
		// Generate Migration
		CodeGenerator::generateMigration($module->name_db, true);
		
		// Create Config for Code Generation
		$config = CodeGenerator::generateConfig($module->name,$module->fa_icon);
		
		// Generate CRUD
		CodeGenerator::createController($config);
		CodeGenerator::createModel($config);
		CodeGenerator::createViews($config);
		CodeGenerator::appendRoutes($config);
		CodeGenerator::addMenu($config);
		
		// Set Module Generated = True
		$module = Module::find($module_id);
		$module->is_gen='1';
		$module->save();
	}

	/**
	 * Set the model view_column
	 *
	 * @param  int  $module_id
	 * @param string $column_name
	 * @return \Illuminate\Http\Response
	 */
	public function set_view_col($module_id, $column_name){
		$module = Module::find($module_id);
		$module->view_col=$column_name;
		$module->save();

		return redirect()->route(config('laraadmin.adminRoute') . '.modules.show', [$module_id]);
	}
	
	public function save_role_module_permissions(Request $request, $id)
	{
		$module = Module::find($id);
		$module = Module::get($module->name);
		
		$tables = LAHelper::getDBTables([]);
		$modules = LAHelper::getModuleNames([]);
		$roles = Role::all();
		
		$now = "now()";        
        
		foreach($roles as $role) {
			
			/* =============== role_module_fields =============== */

			foreach ($module->fields as $field) {
				$field_name = $field['colname'].'_'.$role->id;
				$field_value = $request->$field_name;
				if($field_value == 0) {
					$access = 'invisible';
				} else if($field_value == 1) {
					$access = 'readonly';
				} else if($field_value == 2) {
					$access = 'write';
				} 

				$query = DB::table('role_module_fields')->where('role_id', $role->id)->where('field_id', $field['id']);                    
				if($query->count() == 0) {
					DB::insert('insert into role_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field['id'], $access, $now, $now]);    
				} else {
					DB:: table('role_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access]);
				}
			}
			
			/* =============== role_module =============== */

			$module_name = 'module_'.$role->id;
			if(isset($request->$module_name)) {
				$view = 'module_view_'.$role->id;
				$create = 'module_create_'.$role->id;
				$edit = 'module_edit_'.$role->id;
				$delete = 'module_delete_'.$role->id;
				if(isset($request->$view)) {
					$view = 1;
				} else {
					$view = 0;
				}
				if(isset($request->$create)) {
					$create = 1;
				} else {
					$create = 0;
				}
				if(isset($request->$edit)) {
					$edit = 1;
				} else {
					$edit = 0;
				}
				if(isset($request->$delete)) {
					$delete = 1;
				} else {
					$delete = 0;
				}
				
				$query = DB::table('role_module')->where('role_id', $role->id)->where('module_id', $id);                    
				if($query->count() == 0) {
					DB::insert('insert into role_module (role_id, module_id, acc_view, acc_create, acc_edit, acc_delete, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?)', [$role->id, $id, $view, $create, $edit, $delete, $now, $now]);    
				} else {
					DB:: table('role_module')->where('role_id', $role->id)->where('module_id', $id)->update(['acc_view' => $view, 'acc_create' => $create, 'acc_edit' => $edit, 'acc_delete' => $delete]);
				}
			}
		}
        return redirect(config('laraadmin.adminRoute') . '/modules/'.$id);
	}
}
