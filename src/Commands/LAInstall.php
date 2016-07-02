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
    protected $description = 'Install LaraAdmin Package. Generate Whole structure for /admin.';
    
    protected $from;
    protected $to;
    
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
            
            if ($this->confirm("This process may change/append to following of your existing project files:"
                    ."\n\n\t app/Http/routes.php"
                    ."\n\t app/User.php"
                    ."\n\t database/migrations/2014_10_12_000000_create_users_table.php"
                    ."\n\t gulpfile.js"
                    ."\n\n Please take backup or use git. Do you wish to continue ?", true)) {
                // Controllers
                $this->line("\n".'Generating Controllers...');
                $this->copyFolder($from."/app/Controllers/Auth", $to."/app/Http/Controllers/Auth");
                $this->replaceFolder($from."/app/Controllers/LA", $to."/app/Http/Controllers/LA");
                $this->copyFile($from."/app/Controllers/Controller.php", $to."/app/Http/Controllers/Controller.php");
                $this->copyFile($from."/app/Controllers/HomeController.php", $to."/app/Http/Controllers/HomeController.php");
                
                // Models
                $this->line('Generating Models...');
                $models = ["User", "Role", "Employee", "Department", "Book"];
                foreach ($models as $model) {
                    $this->copyFile($from."/app/Models/".$model.".php", $to."/app/".$model.".php");
                }
                
                // Routes
                $this->line('Appending routes...');
                //if(!$this->fileContains($to."/app/Http/routes.php", "laraadmin.adminRoute")) {
                $this->appendFile($from."/app/routes.php", $to."/app/Http/routes.php");
                
                // Config
                $this->line('Generating Config...');
                $this->copyFile($from."/config/laraadmin.php", $to."/config/laraadmin.php");
                
                // la-assets
                $this->line('Generating LaraAdmin Public Assets...');
                $this->replaceFolder($from."/la-assets", $to."/public/la-assets");
                // Use "git config core.fileMode false" for ignoring file permissions
                
                // migrations
                $this->line('Generating migrations...');
                $this->copyFolder($from."/migrations", $to."/database/migrations");
                
                // resources
                $this->line('Generating resources: assets + views...');
                $this->copyFolder($from."/resources/assets", $to."/resources/assets");
                $this->copyFolder($from."/resources/views", $to."/resources/views");
                
                // Utilities 
                $this->line('Generating Utilities...');
                // if(!$this->fileContains($to."/gulpfile.js", "admin-lte/AdminLTE.less")) {
                $this->appendFile($from."/gulpfile.js", $to."/gulpfile.js");
                
                // Running migrations...
                $this->line('Running migrations...');
                $this->call('migrate');
                
                // Creating Super Admin User
                $this->line('Creating Super Admin User...');
                
                $user = \App\User::where('context_id', "1")->first();
                if(!isset($user['id'])) {
                    $data = array();
                    $data['name']     = $this->ask('Super Admin name');
                    $data['email']    = $this->ask('Super Admin email');
                    $data['password'] = bcrypt($this->secret('Super Admin password'));
                    $data['context_id']  = "1";
                    $data['type']  = "Employee";
                    \App\User::create($data);
                    
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
                        'role' => "1",
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
                $this->info("\nLaraAdmin successfully installed. You can now login from yourdomain.com/admin !!!\n");
            } else {
                $this->error("Installation aborted. Please try again after backup. Thank you...");
            }
        } catch (Exception $e) {
            $this->error("LAInstall::handle exception: ".$e);
            throw new Exception("LAInstall::handle Unable to install : ".$e->getMessage(), 1);
        }
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
