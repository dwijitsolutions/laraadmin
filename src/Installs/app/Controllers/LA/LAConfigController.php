<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Dwij\Laraadmin\Models\LAConfigs;

class LAConfigController extends Controller
{
	var $skin_array = [
		'White Skin' => 'skin-white',
		'Blue Skin' => 'skin-blue',
		'Black Skin' => 'skin-black',
		'Purple Skin' => 'skin-purple',
		'Yellow Sking' => 'skin-yellow',
		'Red Skin' => 'skin-red',
		'Green Skin' => 'skin-green'
	];

	var $layout_array = [
		'Fixed Layout' => 'fixed',
		'Boxed Layout' => 'layout-boxed',
		'Sidebar Collapse Layout' => 'sidebar-collapse',
		'Mini Sidebar Layout' => 'sidebar-mini'
	];
	// 'Top Navigation Layout' => 'layout-top-nav', // Not Working well with application structure

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$configs = LAConfigs::getAll();
		
		return View('la.la_configs.index', [
			'configs' => $configs,
			'skins' => $this->skin_array,
			'layouts' => $this->layout_array
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
			LAConfigs::where('key', $key)->update(['value' => $value]);
		}
		
		return redirect(config('laraadmin.adminRoute')."/la_configs");
	}	
}
