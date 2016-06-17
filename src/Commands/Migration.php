<?php

namespace Dwij\Laraadmin\Commands;

use Illuminate\Console\Command;

use Dwij\Laraadmin\CodeGenerator;

class Migration extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'la:migration {table} {--generate}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Genrate Migrations for LaraAdmin';

    /**
     * Generate a CRUD files inclusing Controller, Model and Routes
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
        $generateFromTable = $this->option('generate');
        if($generateFromTable) {
            $generateFromTable = true;
        }
        CodeGenerator::generateMigration($table, $generateFromTable, $this);
    }
}
