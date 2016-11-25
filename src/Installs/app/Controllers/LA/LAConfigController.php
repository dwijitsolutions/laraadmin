<?php
/**
 * Controller generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
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
		'Top Navigation Layout' => 'layout-top-nav',
		'Sidebar Collapse Layout' => 'sidebar-collapse',
		'Mini Sidebar Layout' => 'sidebar-mini'
	];

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
		foreach(['sidebar_search', 'show_messages', 'show_notifications', 'show_tasks', 'show_rightsidebar'] as $key) {
			if(!isset($all[$key])) {
				$all[$key] = 0;
			} else {
				$all[$key] = 1;
			}
		}
		foreach($all as $key => $value) {
			LAConfigs::where('key', $key)->update(['value' => $value]);
		}
		
		return redirect(config('laraadmin.adminRoute')."/la_configs");
	}	
}
