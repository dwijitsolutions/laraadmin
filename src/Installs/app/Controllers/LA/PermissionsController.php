<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Dwij\Laraadmin\Helpers\LAHelper;
use Zizaco\Entrust\EntrustFacade as Entrust;

use App\Permission;
use App\Role;

class PermissionsController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'name', 'display_name'];
	
	public function __construct() {
		// for authentication (optional)
		$this->middleware('auth');
		
		$module = Module::get('Permissions');
		$listing_cols_temp = array();
		foreach ($this->listing_cols as $col) {
			if($col == 'id') {
				$listing_cols_temp[] = $col;
			} else if(Module::hasFieldAccess($module->id, $module->fields[$col]['id'])) {
				$listing_cols_temp[] = $col;
			}
		}
		$this->listing_cols = $listing_cols_temp;
	}
	
	/**
	 * Display a listing of the Permissions.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Permissions');
		
		if(Module::hasAccess($module->id)) {
			return View('la.permissions.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new permission.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created permission in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Permissions", "create")) {
		
			$rules = Module::validateRules("Permissions", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
				
			$insert_id = Module::insert("Permissions", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.permissions.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified permission.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Permissions", "view")) {
			
			$permission = Permission::find($id);
			$module = Module::get('Permissions');
			$module->row = $permission;
			
			$roles = Role::all();
			
			return view('la.permissions.show', [
				'module' => $module,
				'view_col' => $this->view_col,
				'no_header' => true,
				'no_padding' => "no-padding",
				'roles' => $roles
			])->with('permission', $permission);
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified permission.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Permissions", "edit")) {
			
			$permission = Permission::find($id);
			
			$module = Module::get('Permissions');
			
			$module->row = $permission;
			
			return view('la.permissions.edit', [
				'module' => $module,
				'view_col' => $this->view_col,
			])->with('permission', $permission);
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified permission in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Permissions", "edit")) {
			
			$rules = Module::validateRules("Permissions", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Permissions", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.permissions.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified permission from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Permissions", "delete")) {
			Permission::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.permissions.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = DB::table('permissions')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Permissions');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/permissions/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Permissions", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/permissions/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Permissions", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.permissions.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
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
		if(Entrust::hasRole('SUPER_ADMIN')) {
			$permission = Permission::find($id);
			$module = Module::get('Permissions');
			$module->row = $permission;
			$roles = Role::all();
			
			foreach ($roles as $role) {
				$permi_role_id = 'permi_role_'.$role->id;
				$permission_set = $request->$permi_role_id;
				if(isset($permission_set)) {
					$query = DB::table('permission_role')->where('permission_id', $id)->where('role_id', $role->id);
					if($query->count() == 0) {
						DB::insert('insert into permission_role (permission_id, role_id) values (?, ?)', [$id, $role->id]);
					}
				} else {
					$query = DB::table('permission_role')->where('permission_id', $id)->where('role_id', $role->id);
					if($query->count() > 0) {
						DB::delete('delete from permission_role where permission_id = "'.$id.'" AND role_id = "'.$role->id.'" ');
					}
				}
			}
			return redirect(config('laraadmin.adminRoute') . '/permissions/'.$id);
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
}
