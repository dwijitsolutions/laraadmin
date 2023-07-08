<?php
/**
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use App\Models\LAModule;
use App\Models\LAModuleField;
use Illuminate\Support\Facades\DB;

use App\Models\Backup;

class BackupObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  Backup  $backup
     * @return void
     */
    public function deleting(Backup $backup)
    {
        return LAModule::clearMultiselects('Backups', $backup->id);
    }
}
