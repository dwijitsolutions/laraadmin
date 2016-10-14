<?php
/**
 * Command for LaraAdmin Installation
 * Help: http://laraadmin.com
 */

namespace Dwij\Laraadmin\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Helpers\LAHelper;
use Eloquent;
use DB;

class LACreateAdmin extends Command
{
	/**
	 * The command signature.
	 *
	 * @var string
	 */
	protected $signature = 'la:create_admin';

	/**
	 * The command description.
	 *
	 * @var string
	 */
	protected $description = 'Create Admin after LaraAdmin installation';
	
	protected $from;
	protected $to;

	var $modelsInstalled = ["User", "Role", "Permission", "Employee", "Department", "Upload", "Organization", "Backup"];
	
	/**
	 * Generate Whole structure for /admin
	 *
	 * @return mixed
	 */
	public function handle()
	{
		try {
			//-_custom_module_namespace__
				// Checking database
				$this->line('Checking database...');
				DB::connection()->reconnect();
				$user = \App\MODULES\User::where('context_id', "1")->first();
				if(!isset($user['id'])) {
					$data = array();
					$data['name']     = $this->ask('Super Admin name');
					$data['email']    = $this->ask('Super Admin email');
					$data['password'] = bcrypt($this->secret('Super Admin password'));
					$data['context_id']  = "1";
					$data['type']  = "Employee";
					$user = \App\MODULES\User::create($data);
					
					// TODO: This is Not Standard. Need to find alternative
					Eloquent::unguard();
					
					\App\MODULES\Employee::create([
						'name' => $data['name'],
						'designation' => "Super Admin",
						'mobile' => "8888888888",
						'mobile2' => "",
						'email' => $data['email'],
						'gender' => 'Male',
						'dept' => "1",
						'city' => "Pune",
						'address' => "Karve nagar, Pune 411030",
						'about' => "About user / biography",
						'date_birth' => date("Y-m-d"),
						'date_hire' => date("Y-m-d"),
						'date_left' => date("Y-m-d"),
						'salary_cur' => 0,
					]);
					
					$this->info("Super Admin User '".$data['name']."' successfully created. ");
				} else {
					$this->info("Super Admin User '".$user['name']."' exists. ");
				}
				$role = \App\MODULES\Role::whereName('SUPER_ADMIN')->first();
				$user->attachRole($role);
				$this->info("You can now login from yourdomain.com/".config('laraadmin.adminRoute')." !!!\n");
				$module_namespace = '';
				if(config('laraadmin.models_folder')!=''){
		        	$module_namespace = ''.str_replace('/','\\',config('laraadmin.models_folder'));
				}
				$la_create_admin =  $this->openFile('vendor/dwij/laraadmin/src/Commands/LACreateAdmin.php');
				$la_create_admin = str_replace($module_namespace,'__custom_module_namespace__', $la_create_admin);
				file_put_contents('vendor/dwij/laraadmin/src/Commands/LACreateAdmin.php', $la_create_admin);

				return true;
		}
	catch (Exception $e) {
			$msg = $e->getMessage();
			if (strpos($msg, 'SQLSTATE') !== false) {
				throw new Exception("LAInstall: Database is not connected. Connect database (.env) and run 'la:install' again.\n".$msg, 1);
			} else {
				$this->error("LAInstall::handle exception: ".$e);
				throw new Exception("LAInstall::handle Unable to install : ".$msg, 1);
			}
		}
	}
	
	private function openFile($from) {
		$md = file_get_contents($from);
		return $md;
	}
	
	private function writeFile($from, $to) {
		$md = file_get_contents($from);
		file_put_contents($to, $md);
	}
	
	private function getLineWithString($fileName, $str) {
		$lines = file($fileName);
		foreach ($lines as $lineNumber => $line) {
			if (strpos($line, $str) !== false) {
				return $line;
			}
		}
		return -1;
	}

	private function copyFolder($from, $to) {
		// $this->info("copyFolder: ($from, $to)");
		LAHelper::recurse_copy($from, $to);
	}
	
	private function replaceFolder($from, $to) {
		// $this->info("replaceFolder: ($from, $to)");
		if(file_exists($to)) {
			LAHelper::recurse_delete($to);
		}
		LAHelper::recurse_copy($from, $to);
	}
	
	private function copyFile($from, $to) {
		// $this->info("copyFile: ($from, $to)");
		if(!file_exists(dirname($to))) {
			$this->info("mkdir: (".dirname($to).")");
			mkdir(dirname($to));
		}
		copy($from, $to);
	}
	
	private function appendFile($from, $to) {
		// $this->info("appendFile: ($from, $to)");
		
		$md = file_get_contents($from);
		
		file_put_contents($to, $md, FILE_APPEND);
	}
	
	// TODO:Method not working properly
	private function fileContains($filePath, $text) {
		$fileData = file_get_contents($filePath);
		if (strpos($fileData, $text) === false ) {
			return true;
		} else {
			return false;
		}
	}
}
