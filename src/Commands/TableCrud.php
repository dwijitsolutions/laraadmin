<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\AppNamespaceDetectorTrait;

use Dwij\Laraadmin\CrudGenerator;

class TableCrud extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'la:table-crud {table} {--api} {--migration} {--bootstrap} {--semantic}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a basic CRUD from an existing table';

    /**
     * Generate a CRUD stack
     *
     * @return mixed
     */
    public function handle()
    {
        $filesystem = new Filesystem;
        $table = $this->argument('table');
        $tableDefintion = $this->tableDefintion($table);

        Artisan::call('laracogs:crud', [
            'table' => $table,
            '--api' => $this->option('api'),
            '--migration' => $this->option('migration'),
            '--bootstrap' => $this->option('bootstrap'),
            '--semantic' => $this->option('semantic'),
            '--schema' => $tableDefintion,
        ]);

        $migrationName = 'create_'.$table.'_table';
        $migrationFiles = $filesystem->allFiles(base_path('database/migrations'));

        foreach ($migrationFiles as $file) {
            if (stristr($file->getBasename(), $migrationName) ) {
                $migrationData = file_get_contents($file->getPathname());
                if (stristr($migrationData, 'updated_at')) {
                    $migrationData = str_replace("\$table->timestamps();", '', $migrationData);
                }
                file_put_contents($file->getPathname(), $migrationData);
            }
        }

        $this->line("\nYou've generated a CRUD for the table: ".$table);
        $this->line("\n\nYou may wish to add this as your testing database");
        $this->line("'testing' => [ 'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '' ],");
        $this->info("\n\nCRUD for $table is done.");
    }

    /**
     * Table definitions
     *
     * @param  string $table
     * @return string
     */
    private function tableDefintion($table)
    {
        /*
        $columnStringArray = [];
        $formMaker = new FormMaker();
        $columns = $formMaker->getTableColumns($table, true);

        foreach ($columns as $key => $column) {
            if ($key === 'id') {
                $column['type'] = 'increments';
            }

            $columnStringArray[] = $key.':'.$column['type'];
        }

        $columnString = implode(',', $columnStringArray);

        return $columnString;
        */
    }
}
