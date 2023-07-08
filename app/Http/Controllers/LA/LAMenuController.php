<?php
/**
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Http\Controllers\LA;

use App\Helpers\LAHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\LALog;
use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

/**
 * Class LAMenuController
 * @package App\Http\Controllers\LA
 *
 * Works after managing Menus and their hierarchy
 */
class LAMenuController extends Controller
{
    public function __construct()
    {
        // for authentication (optional)
        // $this->middleware('auth');
    }

    /**
     * Display a listing of Menus
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modules = LAModule::all();
        // Send Menus with No Parent to Views
        $menuItems = LAMenu::where("parent", 0)->orderBy('hierarchy', 'asc')->get();

        return View('la.la_menus.index', [
            'menus' => $menuItems,
            'modules' => $modules
        ]);
    }

    /**
     * Store a newly created Menu in Database
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->input('name');
        $url = $request->input('url');
        $icon = $request->input('icon');
        $type = $request->input('type');

        if ($type == "module") {
            $module_id = $request->input('module_id');
            $module = LAModule::find($module_id);
            if (isset($module->id)) {
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
        $menu = LAMenu::create([
            "name" => $name,
            "url" => $url,
            "icon" => $icon,
            "type" => $type,
            "parent" => 0
        ]);

        // Set Menu Access For All Role
        $menu->set_access_to('all');

        if ($type == "module") {
            return response()->json([
                "status" => "success"
            ], 200);
        } else {
            return redirect(config('laraadmin.adminRoute') . '/la_menus');
        }
    }

    /**
     * Display the specified Menu.
     *
     * @param int $id Menu ID
     * @return mixed
     */
    public function show($id)
    {
        $menu = LAMenu::find($id);
        $roles = Role::all();

        return view('la.la_menus.show', [
            'view_col' => true,
            'no_header' => true,
            'roles' => $roles,
            'no_padding' => "no-padding"
        ])->with('menu', $menu);
    }

    /**
     * Update Custom Menu
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $url = $request->input('url');
        $icon = $request->input('icon');
        $type = $request->input('type');

        $menu = LAMenu::find($id);
        $menu->name = $name;
        $menu->url = $url;
        $menu->icon = $icon;
        $menu->save();

        return redirect(config('laraadmin.adminRoute') . '/la_menus');
    }

    /**
     * Remove the specified Menu from database
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        LAMenu::find($id)->delete();

        // Redirecting to index() method for Listing
        return redirect()->route(config('laraadmin.adminRoute') . '.la_menus.index');
    }

    /**
     * Update Menu Hierarchy
     *
     * @return mixed
     */
    public function update_hierarchy(Request $request)
    {
        $parents = $request->input('jsonData');
        $parent_id = 0;

        for ($i = 0; $i < count($parents); $i++) {
            $this->apply_hierarchy($parents[$i], $i + 1, $parent_id);
        }

        return $parents;
    }

    /**
     * Save Menu hierarchy Recursively
     *
     * @param $menuItem Menu Item Array
     * @param $num Hierarchy number
     * @param $parent_id Parent ID
     */
    public function apply_hierarchy($menuItem, $num, $parent_id)
    {
        // echo "apply_hierarchy: ".json_encode($menuItem)." - ".$num." - ".$parent_id."  <br><br>\n\n";
        $menu = LAMenu::find($menuItem['id']);
        $menu->parent = $parent_id;
        $menu->hierarchy = $num;
        $menu->save();

        // apply hierarchy to children if exists
        if (isset($menuItem['children'])) {
            for ($i = 0; $i < count($menuItem['children']); $i++) {
                $this->apply_hierarchy($menuItem['children'][$i], $i + 1, $menuItem['id']);
            }
        }
    }

    /**
     * Save Role access of Menu
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Menu ID
     * @return json
     */
    public function la_menus_save_role_permissions(Request $request, $id)
    {
        $menu = LAMenu::find($id);
        $roles = Role::all();
        if (isset($menu->id)) {
            if (isset($request->roles) && is_array($request->roles)) {
                // Give Access
                $menu->set_access_to($request->roles);
            } else {
                $menu->set_access_to('remove_all');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Menu Access added'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access'
            ]);
        }
    }
}
