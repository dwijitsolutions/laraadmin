<?php
/**
 * Code generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
 */

namespace Dwij\Laraadmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Helpers\LAHelper;

/**
 * Class Packaging
 * @package Dwij\Laraadmin\Commands
 *
 * Command to put latest development and changes of project into LaraAdmin package.
 * [For LaraAdmin Developer's Only]
 */
class Packaging extends Command
{
    // The command signature.
    var $modelsInstalled = ["User", "Role", "Permission", "Employee", "Department", "Upload", "Organization", "Backup"];
    
    // The command description.
    protected $signature = 'la:packaging';
    
    // Copy From Folder - Package Install Files
    protected $description = '[Developer Only] - Copy LaraAdmin-Dev files to package: "dwij/laraadmin"';
    
    // Copy to Folder - Project Folder
    protected $from;
    
    // Model Names to be handled during Packaging
    protected $to;
    
    /**
     * Copy Project changes into LaraAdmin package.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Exporting started...');
        
        $from = base_path();
        $to = base_path('vendor/dwij/laraadmin/src/Installs');
        
        $this->info('from: ' . $from . " to: " . $to);
        
        // Controllers
        $this->line('Exporting Controllers...');
        $this->replaceFolder($from . "/app/Http/Controllers/Auth", $to . "/app/Controllers/Auth");
        $this->replaceFolder($from . "/app/Http/Controllers/LA", $to . "/app/Controllers/LA");
        $this->copyFile($from . "/app/Http/Controllers/Controller.php", $to . "/app/Controllers/Controller.php");
        $this->copyFile($from . "/app/Http/Controllers/HomeController.php", $to . "/app/Controllers/HomeController.php");
        
        // Models
        $this->line('Exporting Models...');
        
        foreach($this->modelsInstalled as $model) {
            if($model == "User" || $model == "Role" || $model == "Permission") {
                $this->copyFile($from . "/app/" . $model . ".php", $to . "/app/Models/" . $model . ".php");
            } else {
                $this->copyFile($from . "/app/Models/" . $model . ".php", $to . "/app/Models/" . $model . ".php");
            }
        }
        
        // Routes
        $this->line('Exporting Routes...');
        if(LAHelper::laravel_ver() == 5.3) {
            // $this->copyFile($from."/routes/web.php", $to."/app/routes.php"); // Not needed anymore
            $this->copyFile($from . "/routes/admin_routes.php", $to . "/app/admin_routes.php");
        } else {
            // $this->copyFile($from."/app/Http/routes.php", $to."/app/routes.php"); // Not needed anymore
            $this->copyFile($from . "/app/Http/admin_routes.php", $to . "/app/admin_routes.php");
        }
        
        // tests
        $this->line('Exporting tests...');
        $this->replaceFolder($from . "/tests", $to . "/tests");
        
        // Config
        $this->line('Exporting Config...');
        $this->copyFile($from . "/config/laraadmin.php", $to . "/config/laraadmin.php");
        
        // la-assets
        $this->line('Exporting LaraAdmin Assets...');
        $this->replaceFolder($from . "/public/la-assets", $to . "/la-assets");
        // Use "git config core.fileMode false" for ignoring file permissions
        
        // migrations
        $this->line('Exporting migrations...');
        $this->replaceFolder($from . "/database/migrations", $to . "/migrations");
        
        // seeds
        $this->line('Exporting seeds...');
        $this->copyFile($from . "/database/seeds/DatabaseSeeder.php", $to . "/seeds/DatabaseSeeder.php");
        
        // resources
        $this->line('Exporting resources: assets + views...');
        $this->replaceFolder($from . "/resources/assets", $to . "/resources/assets");
        $this->replaceFolder($from . "/resources/views", $to . "/resources/views");
        
        // Utilities 
        $this->line('Exporting Utilities...');
        // $this->copyFile($from."/gulpfile.js", $to."/gulpfile.js"); // Temporarily Not used.
    }
    
    /**
     * Replace Folder contents by deleting content of to folder first
     *
     * @param $from from folder
     * @param $to to folder
     */
    private function replaceFolder($from, $to)
    {
        $this->info("replaceFolder: ($from, $to)");
        if(file_exists($to)) {
            LAHelper::recurse_delete($to);
        }
        LAHelper::recurse_copy($from, $to);
    }
    
    /**
     * Copy file contents. If file not exists create it.
     *
     * @param $from from file
     * @param $to to file
     */
    private function copyFile($from, $to)
    {
        $this->info("copyFile: ($from, $to)");
        //LAHelper::recurse_copy($from, $to);
        if(!file_exists(dirname($to))) {
            $this->info("mkdir: (" . dirname($to) . ")");
            mkdir(dirname($to));
        }
        copy($from, $to);
    }
}
