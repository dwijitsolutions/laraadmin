<?php
/***
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Observers;

use App\Models\Customer;
use App\Models\LAModule;

class CustomerObserver
{
    /**
     * Listen to the Record deleting event.
     *
     * @param  Customer  $customer
     * @return void
     */
    public function deleting(Customer $customer)
    {
        return LAModule::clearMultiselects('Customers', $customer->id);
    }
}
