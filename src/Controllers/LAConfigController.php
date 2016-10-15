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
use Dwij\Laraadmin\Models\LAConfigs;

class LAConfigController extends Controller
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
		$configs = LAConfigs::all();
		$skin_array = [
			'White Skin' => 'skin-white',
			'Blue Skin' => 'skin-blue',
			'Black Skin' => 'skin-black',
			'Purple Skin' => 'skin-purple',
			'Yellow Sking' => 'skin-yellow',
			'Red Skin' => 'skin-red',
			'Green Skin' => 'skin-green'
		];
		$layout_array = [
			'Fixed Layout' => 'fixed',
			'Boxed Layout' => 'layout-boxed',
			'Top Navigation Layout' => 'layout-top-nav',
			'Sidebar Collapse Layout' => 'sidebar-collapse',
			'Mini Sidebar Layout' => 'sidebar-mini'
		];
		return View('la.la_configs.index', [
			'configs' => $configs,
			'skins' => $skin_array,
			'layouts' => $layout_array
		]);
	}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$all = $request->all();
		if(!isset($all['sidebar_search'])) {
			$all['sidebar_search'] = "off";
		}
		if(!isset($all['show_messages'])) {
			$all['show_messages'] = "off";
		}
		if(!isset($all['show_notifications'])) {
			$all['show_notifications'] = "off";
		}
		if(!isset($all['show_tasks'])) {
			$all['show_tasks'] = "off";
		}
		if(!isset($all['show_rightsidebar'])) {
			$all['show_rightsidebar'] = "off";
		}

		foreach($all as $key => $value) {
			LAConfigs::where('key',$key)->update(['value'=>$value]);
		}
		
		$configs = LAConfigs::all();

		$skin_array = [
			'White Skin' => 'skin-white',
			'Blue Skin' => 'skin-blue',
			'Black Skin' => 'skin-black',
			'Purple Skin' => 'skin-purple',
			'Yellow Sking' => 'skin-yellow',
			'Red Skin' => 'skin-red',
			'Green Skin' => 'skin-green'
		];

		$layout_array = [
			'Fixed Layout' => 'fixed',
			'Boxed Layout' => 'layout-boxed',
			'Top Navigation Layout' => 'layout-top-nav',
			'Sidebar Collapse Layout' => 'sidebar-collapse',
			'Mini Sidebar Layout' => 'sidebar-mini'
		];

		return View('la.la_configs.index', [
			'configs' => $configs,
			'skins' => $skin_array,
			'layouts' => $layout_array
		]);

	}
	
	
}
