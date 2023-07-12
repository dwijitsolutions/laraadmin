<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\Department;
use App\Models\LAModule;

class DepartmentObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  Department  $department
     * @return void
     */
    public function deleting(Department $department)
    {
        return LAModule::clearMultiselects('Departments', $department->id);
    }
}
