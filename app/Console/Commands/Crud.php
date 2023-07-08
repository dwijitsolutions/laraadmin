<?php

/**
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Console\Commands;

use Config;
use Illuminate\Support\Facades\Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use App\Models\LAModule;
use App\Helpers\CodeGenerator;

/**
 * Class Crud
 * @package App\Console\Commands
 *
 * Command that generates CRUD's for a Module. Takes Module name as input.
 */
class Crud extends Command
{
    // ================ CRUD Config ================
    public $module = null;
    public $controllerName = "";
    public $modelName = "";
    public $moduleName = "";
    public $dbTableName = "";
    public $singularVar = "";
    public $singularCapitalVar = "";

    // The command signature.
    protected $signature = 'la:crud {module}';

    // The command description.
    protected $description = 'Generate CRUD\'s, Controller, Model, Routes and Menu for given Module.';

    /**
     * Generate a CRUD files including Controller, Model, Views, Routes and Menu
     *
     * @throws Exception
     */
    public function handle()
    {
        $module = $this->argument('module');

        try {
            $config = CodeGenerator::generateConfig($module, "fa-cube");

            CodeGenerator::createController($config, $this);
            CodeGenerator::createModel($config, $this);
            CodeGenerator::createObserver($config, $this);
            CodeGenerator::createViews($config, $this);
            CodeGenerator::appendRoutes($config, $this);
            CodeGenerator::appendObservers($config, $this);
            CodeGenerator::addMenu($config, $this);
            CodeGenerator::createLanguageFile($config, $this);
        } catch (Exception $e) {
            $this->error("Crud::handle exception: " . $e);
            throw new Exception("Unable to generate migration for " . ($module) . " : " . $e->getMessage(), 1);
        }
        $this->info("\nCRUD successfully generated for " . ($module) . "\n");
    }
}
