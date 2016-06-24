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
            
            if ($this->confirm("This process may replace some of your existing project files.\n Please take backup or use git. Do you wish to continue ?", true)) {
                // Controllers
                $this->line('Generating Controllers...');
                $this->replaceFolder($from."/app/Controllers/Auth", $to."/app/Http/Controllers/Auth");
                $this->replaceFolder($from."/app/Controllers/LA", $to."/app/Http/Controllers/LA");
                $this->copyFile($from."/app/Controllers/Controller.php", $to."/app/Http/Controllers/Controller.php");
                $this->copyFile($from."/app/Controllers/HomeController.php", $to."/app/Http/Controllers/HomeController.php");
                
                // Models
                $this->line('Generating Models...');
                $models = ["User", "Role", "Employee", "Department", "Book"];
                foreach ($models as $model) {
                    $this->copyFile($from."/app/Models/".$model.".php", $to."/app/".$model.".php");
                }
                
                $this->info("\nLaraAdmin successfully installed. You can now login.\n");
            } else {
                $this->error("Installation aborted. Please try again after backup. Thank you...");
            }
        } catch (Exception $e) {
            $this->error("LAInstall::handle exception: ".$e);
            throw new Exception("LAInstall::handle Unable to install : ".$e->getMessage(), 1);
        }
    }
    
    private function replaceFolder($from, $to) {
        $this->info("replaceFolder: ($from, $to)");
        if(file_exists($to)) {
            LAHelper::recurse_delete($to);
        }
        LAHelper::recurse_copy($from, $to);
    }
    
    private function copyFile($from, $to) {
        $this->info("copyFile: ($from, $to)");
        //LAHelper::recurse_copy($from, $to);
        if(!file_exists(dirname($to))) {
            $this->info("mkdir: (".dirname($to).")");
            mkdir(dirname($to));
        }
        copy($from, $to);
    }
}
