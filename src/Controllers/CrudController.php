<?php namespace Dwijitso\Sbscrud\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class CrudController extends Controller
{
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		// dd(Config::get("contact.message"));
		// return view('contact::contact');
	}
}