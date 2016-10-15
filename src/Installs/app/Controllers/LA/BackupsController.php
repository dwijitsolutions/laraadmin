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
use Artisan;
use App\Models\Backup;

class BackupsController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'name', 'file_name'];
	public $backup_filepath = "/storage/app/http---localhost/";
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Backups', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Backups', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Backups.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Backups');
		
		if(Module::hasAccess($module->id)) {
			return View('la.backups.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Create Backup using Spatie Backup Library
	 *
	 * @return \Illuminate\Http\Request
	 */
	public function create_backup_ajax(Request $request)
	{
		if(Module::hasAccess("Backups", "create")) {
			
			$exitCode = Artisan::call('backup:run');
			$outputStr = Artisan::output();
			
			if($this->getLineWithString($outputStr, "Copying ") == -1) {
				if($this->getLineWithString($outputStr, "mysqldump: No such file or directory") != -1) {
					return response()->json([
						'status' => 'failed',
						'message' => "Configure dump_command_path in config/database.php. Check console for error details.",
						'exitCode' => $exitCode,
						'output' => $outputStr
					]);
				}
				return response()->json([
					'status' => 'failed',
					'message' => "Error while creating Backup.",
					'exitCode' => $exitCode,
					'output' => $outputStr
				]);
			} else {
				$dataStr = $this->getLineWithString($outputStr, "Copying ");
				$dataStr = str_replace("Copying ", "", $dataStr);
				$dataStr = substr($dataStr, 0, strpos($dataStr, ")"));
				
				$file_name = substr($dataStr, 0, strpos($dataStr, "(") - 1);
				$name = str_replace(".zip", "", $file_name);
				$backup_size = substr($dataStr, strpos($dataStr, "(") + 7);
				
				$request->name = $name;
				$request->file_name = $file_name;
				$request->backup_size = $backup_size;
				$insert_id = Module::insert("Backups", $request);
				
				return response()->json([
					'status' => 'success',
					'message' => 'Backup successfully created.',
					'insert_id' => $insert_id,
					'exitCode' => $exitCode,
					'output' => $outputStr
				]);
			}
		} else {
			return response()->json([
				'status' => 'failed',
				'message' => 'No rights to create Backup.'
			]);
		}
	}

	/**
	 * Remove the specified backup from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Backups", "delete")) {
			$backup = Backup::find($id);
			$path = str_replace("/storage", "", $this->backup_filepath. $backup->file_name);

			unlink(storage_path($path));
			
			$backup->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.backups.index');
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
		$values = DB::table('backups')->select($this->listing_cols)->orderBy('created_at', 'desc')->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Backups');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/backups/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				} else if($col == "file_name") {
				   $data->data[$i][$j] = $this->backup_filepath.$data->data[$i][$j];
				}
			}
			
			if($this->show_action) {
				$output = '';
				$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/downloadBackup/'.$data->data[$i][0]).'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-download"></i></a>';
				
				if(Module::hasAccess("Backups", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.backups.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function downloadBackup($id) {
		$module = Module::get('Backups');
		if(Module::hasAccess($module->id)) {
			$backup = Backup::find($id);

			$path = str_replace("/storage", "", $this->backup_filepath.$backup->file_name);

			return response()->download(storage_path($path));
		} else {
			return response()->json([
				'status' => 'failed',
				'message' => 'No rights to download Backup.'
			]);
		}
	}

	private function getLineWithString($content, $str) {
		$lines = explode(PHP_EOL, $content);
		foreach ($lines as $lineNumber => $line) {
			if (strpos($line, $str) !== false) {
				return $line;
			}
		}
		return -1;
	}
}
