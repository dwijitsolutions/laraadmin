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


class LAInstall extends Command
{
	/**
	 * The command signature.
	 *
	 * @var string
	 */
	protected $signature = 'la:install';

	/**
	 * The command description.
	 *
	 * @var string
	 */
	protected $description = 'Install LaraAdmin Package. Generate whole structure for /admin.';
	
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
			$this->info('LaraAdmin installation started...');
			
			$from = base_path('vendor/dwij/laraadmin/src/Installs');
			$to = base_path();
			
			$this->info('from: '.$from." to: ".$to);
			$db_data = array();
			if ($this->confirm("Do you wish to set your DB config in the .env file ?", true)) {
				$this->line("DB Assistant Initiated....");
				
				
				if(LAHelper::laravel_ver() == 5.3) {
					$db_data['dbhost'] = $this->ask('Database Host');
					$db_data['dbport'] = $this->ask('Database Port');
				}
				$db_data['db'] = $this->ask('Database Name');
				$db_data['dbuser'] = $this->ask('Database User');
				$db_data['dbpass'] = $this->ask('Database Password');
				
				$envfile =  $this->openFile('.env');
				
				$dbline = $this->getLineWithString('.env', 'DB_DATABASE=');
				$dbuserline = $this->getLineWithString('.env', 'DB_USERNAME=');
				$dbpassline = $this->getLineWithString('.env', 'DB_PASSWORD=');
				$dbhostline = $this->getLineWithString('.env','DB_HOST=');

				if(LAHelper::laravel_ver() == 5.3) {					
					$dbportline = $this->getLineWithString('.env','DB_PORT=');
					$envfile = str_replace($dbportline, "DB_PORT=".$db_data['dbport']."\n",$envfile);
					config(['env.DB_PORT' => $db_data['dbport']]);
				}
				$envfile = str_replace($dbhostline, "DB_HOST=".$db_data['dbhost']."\n",$envfile);
				$envfile = str_replace($dbline, "DB_DATABASE=".$db_data['db']."\n",$envfile);
				$envfile = str_replace($dbuserline, "DB_USERNAME=".$db_data['dbuser']."\n",$envfile);
				$envfile = str_replace($dbpassline, "DB_PASSWORD=".$db_data['dbpass']."\n",$envfile);
				file_put_contents('.env', $envfile);
				/*
				@slozano95 Trying to set enviromental variables at runtime, will check that later 
				config(['env.DB_HOST' => $db_data['dbhost']]);
				config(['env.DB_DATABASE' => $db_data['db']]);
				config(['env.DB_USERNAME' => $db_data['dbuser']]);
				config(['env.DB_PASSWORD' => $db_data['dbpass']]);
				*/
				
				$this->line("\n".'You might need to run php artisan la:install again for changes to take effect');	
			}
			
			if ($this->confirm("LaraAdmin requires an Array as CACHE_DRIVER, Do you wish to set your CACHE_DRIVER to ARRAY ?", true)) {
				$envfile =  $this->openFile('.env');
				$cachedriverline = $this->getLineWithString('.env','CACHE_DRIVER=');
				$envfile = str_replace($cachedriverline, "CACHE_DRIVER=array\n",$envfile);
				file_put_contents('.env', $envfile);
				$this->line("\n".'You might need to run php artisan la:install again for changes to take effect');
				//runtime setting
				//@slozano95 Trying to set enviromental variables at runtime, will check that later 
				//config(['env.CACHE_DRIVER' => 'array']);
			}
			
			if ($this->confirm("This process may change/append to the following of your existing project files:"
					."\n\n\t app/Http/routes.php"
					."\n\t app/User.php"
					."\n\t database/migrations/2014_10_12_000000_create_users_table.php"
					."\n\t gulpfile.js"
					."\n\n Please take backup or use git. Do you wish to continue ?", true)) {
				
				// Controllers
				$this->line("\n".'Generating Controllers...');
				$this->copyFolder($from."/app/Controllers/Auth", $to."/app/Http/Controllers/Auth");
				$this->replaceFolder($from."/app/Controllers/LA", $to."/app/Http/Controllers/LA");
				if(LAHelper::laravel_ver() == 5.3) {
					$this->copyFile($from."/app/Controllers/Controller.5.3.php", $to."/app/Http/Controllers/Controller.php");
				} else {
					$this->copyFile($from."/app/Controllers/Controller.php", $to."/app/Http/Controllers/Controller.php");
				}
				$this->copyFile($from."/app/Controllers/HomeController.php", $to."/app/Http/Controllers/HomeController.php");
				
				
				// Config
				$this->line('Generating Config...');
				$this->copyFile($from."/config/laraadmin.php", $to."/config/laraadmin.php");
				
				// Models
				$this->line('Generating Models...');
				if(!file_exists($to."/app/Models")) {
					$this->info("mkdir: (".$to."/app/Models)");
					mkdir($to."/app/Models");
				}
				foreach ($this->modelsInstalled as $model) {
					if($model=="User" || $model=="Role" || $model=="Permission"){
						$this->copyFile($from."/app/Models/".$model.".php", $to."/app/".$model.".php");
					}else{
						$this->copyFile($from."/app/Models/".$model.".php", $to."/app/Models/".$model.".php");
					}
				}
				
				
				//Custom Admin Route
				$this->line("Default admin route is domain.com/admin");
				if ($this->confirm('Would you like to customize this route?', true)) {
					$custom_admin_route = $this->ask('Custom admin route:');
					$laconfigfile =  $this->openFile($to."/config/laraadmin.php");
					$arline = $this->getLineWithString($to."/config/laraadmin.php","'adminRoute' => 'admin',");
					$laconfigfile = str_replace($arline, "'adminRoute' => '".$custom_admin_route."',",$laconfigfile);
					file_put_contents($to."/config/laraadmin.php", $laconfigfile);
					config(['laraadmin.adminRoute' => $custom_admin_route]);
				}
				
				
				// Generate Uploads / Thumbnails folders in /storage
				$this->line('Generating Uploads / Thumbnails folders...');
				if(!file_exists($to."/storage/uploads")) {
					$this->info("mkdir: (".$to."/storage/uploads)");
					mkdir($to."/storage/uploads");
				}
				if(!file_exists($to."/storage/thumbnails")) {
					$this->info("mkdir: (".$to."/storage/thumbnails)");
					mkdir($to."/storage/thumbnails");
				}
								
				// la-assets
				$this->line('Generating LaraAdmin Public Assets...');
				$this->replaceFolder($from."/la-assets", $to."/public/la-assets");
				// Use "git config core.fileMode false" for ignoring file permissions

				// check CACHE_DRIVER to be array or else
				// It is required for Zizaco/Entrust
				// https://github.com/Zizaco/entrust/issues/468
				$driver_type = env('CACHE_DRIVER');
				if($driver_type != "array") {
					throw new Exception("Please set Cache Driver to array in .env (Required for Zizaco\Entrust) and run la:install again:"
							."\n\n\tCACHE_DRIVER=array\n\n", 1);
				}
				
				// migrations
				$this->line('Generating migrations...');
				$this->copyFolder($from."/migrations", $to."/database/migrations");
				
				$this->line('Copying seeds...');
				$this->copyFile($from."/seeds/DatabaseSeeder.php", $to."/database/seeds/DatabaseSeeder.php");
						
	
				// resources
				$this->line('Generating resources: assets + views...');
				$this->copyFolder($from."/resources/assets", $to."/resources/assets");
				$this->copyFolder($from."/resources/views", $to."/resources/views");
				
				// Checking database
				$this->line('Checking database...');
				DB::connection()->reconnect();

				// Running migrations...
				$this->line('Running migrations...');
				$this->call('clear-compiled');
				$this->call('cache:clear');
				$this->info(exec('composer dump-autoload'));
				
				$this->call('migrate:refresh');
				// $this->call('migrate:refresh', ['--seed']);
				
				// $this->call('db:seed', ['--class' => 'LaraAdminSeeder']);

				// $this->line('Running seeds...');
				// $this->info(exec('composer dump-autoload'));
				$this->call('db:seed');
				// Install Spatie Backup
				$this->call('vendor:publish', ['--provider' => 'Spatie\Backup\BackupServiceProvider']);

				// Edit config/database.php for Spatie Backup Configuration
				if($this->getLineWithString('config/database.php', "dump_command_path") == -1) {
					$newDBConfig = "            'driver' => 'mysql',\n"
						."            'dump_command_path' => '/opt/lampp/bin', // only the path, so without 'mysqldump' or 'pg_dump'\n"
						."            'dump_command_timeout' => 60 * 5, // 5 minute timeout\n"
						."            'dump_using_single_transaction' => true, // perform dump using a single transaction\n";
					
					$envfile =  $this->openFile('config/database.php');
					$mysqldriverline = $this->getLineWithString('config/database.php', "'driver' => 'mysql'");
					$envfile = str_replace($mysqldriverline, $newDBConfig, $envfile);
					file_put_contents('config/database.php', $envfile);
				}
				
				// Routes
				$this->line('Appending routes...');
				//if(!$this->fileContains($to."/app/Http/routes.php", "laraadmin.adminRoute")) {
				if(LAHelper::laravel_ver() == 5.3) {
					$this->appendFile($from."/app/routes.php", $to."/routes/web.php");
					$this->copyFile($from."/app/admin_routes.php", $to."/routes/admin_routes.php");
				} else {
					$this->appendFile($from."/app/routes.php", $to."/app/Http/routes.php");
					$this->copyFile($from."/app/admin_routes.php", $to."/app/Http/admin_routes.php");
				}
				// Utilities 
				$this->line('Generating Utilities...');
				// if(!$this->fileContains($to."/gulpfile.js", "admin-lte/AdminLTE.less")) {
				$this->appendFile($from."/gulpfile.js", $to."/gulpfile.js");
				
				// Creating Super Admin User
				
				$user = \App\User::where('context_id', "1")->first();
				if(!isset($user['id'])) {
					$data = array();
					$data['name']     = $this->ask('Super Admin name');
					$data['email']    = $this->ask('Super Admin email');
					$data['password'] = bcrypt($this->secret('Super Admin password'));
					$data['context_id']  = "1";
					$data['type']  = "Employee";
					$user = \App\User::create($data);
					
					// TODO: This is Not Standard. Need to find alternative
					Eloquent::unguard();
					
					\App\Employee::create([
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
				$role = \App\Role::whereName('SUPER_ADMIN')->first();
				$user->attachRole($role);
				$this->info("\nLaraAdmin successfully installed.");
				$this->info("You can now login from yourdomain.com/".config('laraadmin.adminRoute')." !!!\n");
				
			} else {
				$this->error("Installation aborted. Please try again after backup. Thank you...");
			}
		} catch (Exception $e) {
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
