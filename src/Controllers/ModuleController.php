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
use Dwij\Laraadmin\Models\ModuleFields;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\CodeGenerator;
use App\Role;
use Schema;
use Dwij\Laraadmin\Models\Menu;

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
		$module_id = Module::generateBase($request->name, $request->icon);
		
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
	public function update(Request $request)
	{
		$module = Module::find($request->id);
        if(isset($module->id))
		{
			$module->label = ucfirst($request->label);
			$module->fa_icon = $request->icon;
			$module->save();		

			$menu = Menu::where('url', strtolower($module->name))->where('type', 'module')->first();
			if(isset($menu->id)) {
				$menu->name = ucfirst($request->label);
				$menu->icon = $request->icon;
				$menu->save();
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$module = Module::find($id);
		
		//Delete Menu
		$menuItems = Menu::where('name', $module->name)->first();
		if(isset($menuItems)) {
			$menuItems->delete();
		}	
		
		// Delete Module Fields
		$module_fields = ModuleFields::where('module',$module->id)->delete();
		
		// Delete Resource Views directory
		\File::deleteDirectory(resource_path('/views/la/' . $module->name_db));
		
		// Delete Controller
		\File::delete(app_path('/Http/Controllers/LA/'.$module->name.'Controller.php'));
		
		// Delete Model
		if($module->model == "User" || $module->model == "Role" || $module->model == "Permission") {
			\File::delete(app_path($module->model.'.php'));
		} else {
			\File::delete(app_path('Models/'.$module->model.'.php'));
		}
		
		// Modify Migration for Deletion
		// Find existing migration file
		$mfiles = scandir(base_path('database/migrations/'));
		$fileExistName = "";
		foreach ($mfiles as $mfile) {
			if(str_contains($mfile, "create_".$module->name_db."_table")) {
				$migrationClassName = ucfirst(camel_case("create_".$module->name_db."_table"));
				
				$templateDirectory = __DIR__.'/../stubs';
				$migrationData = file_get_contents($templateDirectory."/migration_removal.stub");
				$migrationData = str_replace("__migration_class_name__", $migrationClassName, $migrationData);
				$migrationData = str_replace("__db_table_name__", $module->name_db, $migrationData);
				file_put_contents(base_path('database/migrations/'.$mfile), $migrationData);
			}
		}
		
		// Delete Admin Routes
		if(LAHelper::laravel_ver() == 5.3) {
			$file_admin_routes = base_path("/routes/admin_routes.php");
		} else {
			$file_admin_routes = base_path("/app/Http/admin_routes.php");
		}
		while(LAHelper::getLineWithString($file_admin_routes, "LA\\".$module->name."Controller") != -1) {
			$line = LAHelper::getLineWithString($file_admin_routes, "LA\\".$module->name.'Controller');
			$fileData = file_get_contents($file_admin_routes);
			$fileData = str_replace($line, "", $fileData);
			file_put_contents($file_admin_routes, $fileData);
		}
		if(LAHelper::getLineWithString($file_admin_routes, "=== ".$module->name." ===") != -1) {
			$line = LAHelper::getLineWithString($file_admin_routes, "=== ".$module->name." ===");
			$fileData = file_get_contents($file_admin_routes);
			$fileData = str_replace($line, "", $fileData);
			file_put_contents($file_admin_routes, $fileData);
		}
		
		// Delete Table
		if (Schema::hasTable($module->name_db)) {
			Schema::drop($module->name_db);
		}
		
		// Delete Module
		$module->delete();
		
		$modules = Module::all();
		return redirect()->route(config('laraadmin.adminRoute') . '.modules.index', ['modules' => $modules]);
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
		
		// Give Default Full Access to Super Admin
		$role = Role::where("name", "SUPER_ADMIN")->first();
		Module::setDefaultRoleAccess($module->id, $role->id, "full");

		return response()->json([
			'status' => 'success'
		]);
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

		return response()->json([
			'status' => 'success'
		]);
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
		
		// Give Default Full Access to Super Admin
		$role = Role::where("name", "SUPER_ADMIN")->first();
		Module::setDefaultRoleAccess($module->id, $role->id, "full");

		return response()->json([
			'status' => 'success'
		]);
	}
/**
	 * Generate Modules Update(migrations and crud) not routes
	 *
	 * @param  int  $module_id
	 * @return \Illuminate\Http\Response
	 */
	public function generate_update($module_id)
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
		
		// Set Module Generated = True
		$module = Module::find($module_id);
		$module->is_gen='1';
		$module->save();

		return response()->json([
			'status' => 'success'
		]);
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
		
		$now = date("Y-m-d H:i:s");        
        
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
					DB::table('role_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access]);
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
					DB::table('role_module')->where('role_id', $role->id)->where('module_id', $id)->update(['acc_view' => $view, 'acc_create' => $create, 'acc_edit' => $edit, 'acc_delete' => $delete]);
				}
			}
		}
        return redirect(config('laraadmin.adminRoute') . '/modules/'.$id."#access");
	}
	
	public function save_module_field_sort(Request $request, $id)
	{
		$sort_array = $request->sort_array;
		
		foreach ($sort_array as $index => $field_id) {
			DB::table('module_fields')->where('id', $field_id)->update(['sort' => ($index + 1)]);
		}
		
		return response()->json([
			'status' => 'success'
		]);
	}

	public function get_module_files(Request $request, $module_id)
	{
		$module = Module::find($module_id);
		
		$arr = array();
		$arr[] = "app/Http/Controllers/LA/".$module->controller.".php";
		$arr[] = "app/Models/".$module->model.".php";
		$views = scandir(resource_path('views/la/'.$module->name_db));
		foreach ($views as $view) {
			if($view != "." && $view != "..") {
				$arr[] = "resources/views/la/".$view;
			}
		}
		// Find existing migration file
		$mfiles = scandir(base_path('database/migrations/'));
		$fileExistName = "";
		foreach ($mfiles as $mfile) {
			if(str_contains($mfile, "create_".$module->name_db."_table")) {
				$arr[] = 'database/migrations/' . $mfile;
			}
		}
		return response()->json([
			'files' => $arr
		]);
	}
}
