<?php

namespace Dwij\Laraadmin\Commands;

use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
    
    /**
     * Generate Whole structure for /admin
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            
        } catch (Exception $e) {
            $this->error("LAInstall::handle exception: ".$e);
            throw new Exception("LAInstall::handle Unable to install : ".$e->getMessage(), 1);
        }
        
        $this->info("\nLaraAdmin successfully installed. You can now login.\n");
    }
}
