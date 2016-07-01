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
        $module_id = Module::generateBase($request->name);
        
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
        
        return view('la.modules.show', [
            'no_header' => true,
            'no_padding' => "no-padding",
            'ftypes' => $ftypes,
            'tables' => $tables,
            'modules' => $modules
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
        
        $config = CodeGenerator::generateConfig($module->name);
        
        CodeGenerator::createController($config);
        CodeGenerator::createModel($config);
        CodeGenerator::createViews($config);
        CodeGenerator::appendRoutes($config);
        CodeGenerator::addMenu($config);
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
}
