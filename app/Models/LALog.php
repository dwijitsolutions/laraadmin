<?php
/**
 * Model generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\LAModule;

class LALog extends Model
{
    use SoftDeletes;

    protected $table = 'la_logs';

    protected $hidden = [

    ];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'la_logs_index';
    }

    /**
     * Get mapping array by key
     *
     * @return array
     */
    public static function arr($key = "id")
    {
        $results = LALog::all();
        $arr = array();
        foreach ($results as $result) {
            $arr[$result->$key] = $result;
        }
        return $arr;
    }

    /**
     * Get all events happened on Module
     *
     * @return mixed
     */
    public function timeline()
    {
        $moduleConfigs = config('laraadmin.log.LA_logs');
        $moduleConfigsArr = array_keys($moduleConfigs);
        return LALog::where("context_id", $this->id)->whereIn("type", $moduleConfigsArr)->orderBy("created_at", "desc")->get();
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'lalog_user', 'lalog_id', 'user_id');
    }

    public static function make($log_type, $log)
    {
        // log_type starts with "laraadmin.log.". Then we should append that to get config
        if (!str_starts_with($log_type, "laraadmin.log.")) {
            $log_type = "laraadmin.log.".$log_type;
        }

        // Check if log_type really exists in config file.
        // If not then skip adding LALog
        if (null !== config($log_type.'.name')) {
            // Get Title if not present in parameters
            if (!isset($log['title'])) {
                $log['title'] = config($log_type.'.name');
            }

            // Get Type if not present in parameters
            if (!isset($log['type'])) {
                $log['type'] = config($log_type.'.code');
            }

            // If module_id is passes as Module Name, then need to convert that to ID
            if (is_string($log['module_id'])) {
                $module = LAModule::get($log['module_id']);
                // Check if this Module Exists
                if (isset($module->id)) {
                    $log['module_id'] = $module->id;
                } else {
                    $log['module_id'] = null;
                }
            }

            // Check Type of Log "content"
            $content = "{}";
            if (is_string($log['content'])) {
                $content = $log['content'];
            } elseif (is_object($log['content'])) {
                // This can be Model Object / Request Object
                $content = json_encode($log['content']);
            } elseif (is_array($log['content']) && count($log['content']) == 2) {
                // This can be array containing old and new data changes
                // Now compare two arrays
                $old = $log['content']['old'];
                $new = $log['content']['new'];
                $old = json_decode(json_encode($old));
                $new = json_decode(json_encode($new));

                $diff = (object) array();
                foreach ($new as $key => $value) {
                    if (isset($old->{$key})) {
                        if ($old->{$key} != $new->{$key}) {
                            $diff->{$key} = [
                                'old' => $old->{$key},
                                'new' => $new->{$key},
                            ];
                        }
                    } else {
                        if (isset($new->{$key})) {
                            $diff->{$key} = [
                                'old' => null,
                                'new' => $new->{$key},
                            ];
                        }
                    }
                }
                $content = json_encode($diff);
                if ($content == "{}") {
                    return;
                }
            }
            $log['content'] = $content;

            // Handle Notify To
            $notify_to = $log['notify_to'];
            unset($log['notify_to']);

            // Create LALogs
            $log = LALog::create($log);
        }
    }
}
