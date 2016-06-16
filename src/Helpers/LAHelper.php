<?php

namespace Dwij\Laraadmin\Helpers;
use DB;

class LAHelper
{
	// $tables = LAHelper::getDBTables([]);
    public static function getDBTables($remove_tables = []) {
        $tables = DB::select('SHOW TABLES');
		
		$tables_out = array();
		foreach ($tables as $table) {
			$table = (Array)$table;
			$tables_out[] = array_values($table)[0];
		}
		$remove_tables2 = array(
			'migrations',
			'modules',
			'module_fields',
			'module_field_types',
			'password_resets'
		);
		$remove_tables = array_merge($remove_tables, $remove_tables2);
		$remove_tables = array_unique($remove_tables);
		$tables_out = array_diff($tables_out, $remove_tables);
		return $tables_out;
    }
}