<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace Dwij\Laraadmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;

use Dwij\Laraadmin\Models\Menu;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\Helpers\LAHelper;

class MenuController extends Controller
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
        $menuItems = Menu::where("parent", 0)->get();
        
        return View('la.menus.index', [
            'menus' => $menuItems,
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
        $name = Input::get('name');
        $url = Input::get('url');
        $icon = Input::get('icon');
        $type = Input::get('type');

        if($type == "module") {
            $module_id = Input::get('module_id');
            $module = Module::find($module_id);
            if(isset($module->id)) {
                $name = $module->name;
                $url = $module->name_db;
                $icon = $module->fa_icon;
            } else {
                return response()->json([
                    "status" => "failure",
                    "message" => "Module does not exists"
                ], 200);
            }
        }
        Menu::create([
            "name" => $name,
            "url" => $url,
            "icon" => $icon,
            "type" => $type,
            "parent" => 0
        ]);
        if($type == "module") {
            return response()->json([
                "status" => "success"
            ], 200);
        } else {
            return redirect(config('laraadmin.adminRoute').'/la_menus');
        }
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
        // $module = Module::find($field->module);
        
        $field = ModuleFields::find($id);
        $field->colname = $request->colname;
        $field->label = $request->label;
        $field->module = $request->module_id;
        $field->field_type = $request->field_type;
        if($request->readonly) {
            $field->readonly = true;
        } else {
            $field->readonly = false;
        }
        $field->defaultvalue = $request->defaultvalue;
        $field->minlength = $request->minlength;
        $field->maxlength = $request->maxlength;
        if($request->required) {
            $field->required = true;
        } else {
            $field->required = false;
        }
        $field->popup_vals = $request->popup_vals;
        $field->save();
        return redirect()->action('LA\ModuleController@show', [$module_id]);
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
}
