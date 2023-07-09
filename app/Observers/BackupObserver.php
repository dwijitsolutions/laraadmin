<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\Backup;
use App\Models\LAModule;

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
