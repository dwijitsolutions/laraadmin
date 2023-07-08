<?php
/**
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is Proprietary Software created by Dwij IT Solutions. Use of LaraAdmin requires Paid Licence issued by Dwij IT Solutions.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use User;

use App\Models\LAModule;
use App\Models\LAMenu;
use App\Models\Role;
use DateTime;
use Illuminate\Support\Facades\App;

/**
 * Class LAHelper
 * @package App\Helpers
 *
 * This is LaraAdmin Helper class contains methods required for Admin Panel functionality.
 */
class LAHelper
{
    /**
     * Gives various names of Module in Object like label, table, model, controller, singular
     *
     * $names = LAHelper::generateModuleNames($module_name);
     *
     * @param $module_name module name
     * @param $icon module icon in FontAwesome
     * @return object
     */
    public static function generateModuleNames($module_name, $icon)
    {
        $array = array();
        $module_name = trim($module_name);
        $module_name = str_replace(" ", "_", $module_name);

        $config = CodeGenerator::generateConfig($module_name, $icon, false);

        $array['module'] = $config->moduleName;
        $array['label'] = $config->moduleName2;
        $array['table'] = $config->dbTableName;
        $array['model'] = $config->modelName;
        $array['fa_icon'] = $icon;
        $array['controller'] = $config->controllerName;
        $array['singular_l'] = $config->singularVar;
        $array['singular_c'] = $config->singularCapitalVar;

        // $array['module'] = ucfirst(Str::plural($module_name));
        // $array['label'] = ucfirst(Str::plural($module_name));
        // $array['table'] = strtolower(Str::plural($module_name));
        // $array['model'] = ucfirst(Str::singular($module_name));
        // $array['fa_icon'] = $icon;
        // $array['controller'] = $array['module'] . "Controller";
        // $array['singular_l'] = strtolower(Str::singular($module_name));
        // $array['singular_c'] = ucfirst(Str::singular($module_name));

        return (object)$array;
    }

    /**
     * Get list of Database tables excluding LaraAdmin Context tables like
     * backups, la_configs, la_menus, migrations, la_modules, la_module_fields, la_module_field_types
     * password_resets, permissions, permission_role, role_la_module, role_la_module_fields, role_user
     *
     * Method currently supports MySQL and SQLite databases
     *
     * You can exclude additional tables by $$remove_tables
     *
     * $tables = LAHelper::getDBTables([]);
     *
     * @param array $remove_tables exclude additional tables
     * @return array
     */
    public static function getDBTables($remove_tables = [])
    {
        if (env('DB_CONNECTION') == "sqlite") {
            $tables_sqlite = DB::select('select * from sqlite_master where type="table"');
            $tables = array();
            foreach ($tables_sqlite as $table) {
                if ($table->tbl_name != 'sqlite_sequence') {
                    $tables[] = $table->tbl_name;
                }
            }
        } elseif (env('DB_CONNECTION') == "pgsql") {
            $tables_pgsql = DB::select("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = 'public' ORDER BY table_name;");
            $tables = array();
            foreach ($tables_pgsql as $table) {
                $tables[] = $table->table_name;
            }
        } elseif (env('DB_CONNECTION') == "mysql") {
            $tables = DB::select('SHOW TABLES');
        } else {
            $tables = DB::select('SHOW TABLES');
        }

        $tables_out = array();
        foreach ($tables as $table) {
            $table = (array)$table;
            $tables_out[] = array_values($table)[0];
        }
        if (in_array(-1, $remove_tables)) {
            $remove_tables2 = array();
        } else {
            $remove_tables2 = array(
                'backups',
                'la_configs',
                'la_menus',
                'migrations',
                'la_modules',
                'la_module_fields',
                'la_module_field_types',
                'password_resets',
                'permissions',
                'permission_role',
                'role_la_module',
                'role_la_module_fields',
                'role_user'
            );
        }
        $remove_tables = array_merge($remove_tables, $remove_tables2);
        $remove_tables = array_unique($remove_tables);
        $tables_out = array_diff($tables_out, $remove_tables);

        $tables_out2 = array();
        foreach ($tables_out as $table) {
            $tables_out2[$table] = $table;
        }

        return $tables_out2;
    }

    /**
     * Get Array of All Modules
     *
     * $modules = LAHelper::getModuleNames([]);
     *
     * @param array $remove_modules to exclude certain modules.
     * @return array Array of Modules
     */
    public static function getModuleNames($remove_modules = [])
    {
        $modules = LAModule::all();

        $modules_out = array();
        foreach ($modules as $module) {
            $modules_out[] = $module->name;
        }
        $modules_out = array_diff($modules_out, $remove_modules);

        $modules_out2 = array();
        foreach ($modules_out as $module) {
            $modules_out2[$module] = $module;
        }

        return $modules_out2;
    }

    /**
     * Method to parse the dropdown, Multiselect, Taginput and radio values which are linked with
     * either other tables via "@" e.g. "@employees" or string array of values
     *
     * This function parse the either case and gives output in html labels.
     * Used only in show.blade.php of modules
     *
     * LAHelper::parseValues($field['popup_vals']);
     *
     * @param $value value source for column e.g. @employees / ["Marvel","Universal"]
     * @return string html labeled values
     */
    public static function parseValues($value)
    {
        // return $value;
        $valueOut = "";
        if (strpos($value, '[') !== false) {
            $arr = json_decode($value);
            foreach ($arr as $key) {
                $valueOut .= "<div class='label label-primary'>" . $key . "</div> ";
            }
        } elseif (strpos($value, ',') !== false) {
            $arr = array_map('trim', explode(",", $value));
            foreach ($arr as $key) {
                $valueOut .= "<div class='label label-primary'>" . $key . "</div> ";
            }
        } elseif (strpos($value, '@') !== false) {
            $valueOut .= "<b data-toggle='tooltip' data-placement='top' title='From " . str_replace("@", "", $value) . " table' class='text-primary'>" . $value . "</b>";
        } elseif ($value == "") {
            $valueOut .= "";
        } else {
            $valueOut = "<div class='label label-primary'>" . $value . "</div> ";
        }
        return $valueOut;
    }

    /**
     * Log method to log either in command line or in Log file depending on $type.
     *
     * LAHelper::log("info", "", $commandObject);
     *
     * @param $type where to put log - error / info / debug
     * @param $text text to put in log
     * @param $commandObject command object if log is to be put on commandline
     */
    public static function log($type, $text, $commandObject)
    {
        if ($commandObject) {
            $commandObject->$type($text);
        } else {
            if ($type == "line") {
                $type = "info";
            }
            Log::$type($text);
        }
    }

    /**
     * Method copies folder recursively into another
     *
     * LAHelper::recurse_copy("", "");
     *
     * @param $src source folder
     * @param $dst destination folder
     */
    public static function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst, 0777, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    LAHelper::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    // ignore files
                    if (!in_array($file, [".DS_Store"])) {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
        }
        closedir($dir);
    }

    /**
     * Method deletes folder and its content
     *
     * LAHelper::recurse_delete("");
     *
     * @param $dir directory name
     */
    public static function recurse_delete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        LAHelper::recurse_delete($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Generate Random Password
     *
     * $password = LAHelper::gen_password();
     *
     * @param int $chars_min minimum characters
     * @param int $chars_max maximum characters
     * @param bool $use_upper_case allowed uppercase characters
     * @param bool $include_numbers includes numbers or not
     * @param bool $include_special_chars include special charactors or not
     * @return string random password according to configuration
     */
    public static function gen_password($chars_min = 6, $chars_max = 8, $use_upper_case = false, $include_numbers = false, $include_special_chars = false)
    {
        $length = rand($chars_min, $chars_max);
        $selection = 'aeuoyibcdfghjklmnpqrstvwxz';
        if ($include_numbers) {
            $selection .= "1234567890";
        }
        if ($include_special_chars) {
            $selection .= "!@\"#$%&[]{}?|";
        }
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $current_letter = $use_upper_case ? (rand(0, 1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];
            $password .= $current_letter;
        }
        return $password;
    }

    /**
     * Get url of image by using $upload_id
     *
     * LAHelper::img($upload_id);
     *
     * @param $upload_id upload id of image / file
     * @return string file / image url
     */
    public static function img($upload_id)
    {
        $upload = \App\Models\Upload::find($upload_id);
        if (isset($upload->id)) {
            return url("files/" . $upload->hash . DIRECTORY_SEPARATOR . $upload->name);
        } else {
            return "";
        }
    }

    /**
     * Get Thumbnail image path of Uploaded image
     *
     * LAHelper::createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height);
     *
     * @param $filepath file path
     * @param $thumbpath thumbnail path
     * @param $thumbnail_width thumbnail width
     * @param $thumbnail_height thumbnail height
     * @param bool $background background color - default transparent
     * @return bool/string Returns Thumbnail path
     */
    public static function createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height, $background = false)
    {
        list($original_width, $original_height, $original_type) = getimagesize($filepath);
        if ($original_width > $original_height) {
            $new_width = $thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($thumbnail_width - $new_width) / 2);
        $dest_y = intval(($thumbnail_height - $new_height) / 2);
        if ($original_type === 1) {
            $imgt = "ImageGIF";
            $imgcreatefrom = "ImageCreateFromGIF";
        } elseif ($original_type === 2) {
            $imgt = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        } elseif ($original_type === 3) {
            $imgt = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        } else {
            return false;
        }
        $old_image = $imgcreatefrom($filepath);
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background
        // figuring out the color for the background
        if (is_array($background) && count($background) === 3) {
            list($red, $green, $blue) = $background;
            $color = imagecolorallocate($new_image, $red, $green, $blue);
            imagefill($new_image, 0, 0, $color);
        // apply transparent background only if is a png image
        } elseif ($background === 'transparent' && $original_type === 3) {
            imagesavealpha($new_image, true);
            $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
            imagefill($new_image, 0, 0, $color);
        }
        imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, $thumbpath);
        return file_exists($thumbpath);
    }

    /**
     * Print the menu editor view.
     * This needs to be done recursively
     *
     * LAHelper::print_menu_editor($menu)
     *
     * @param $menu menu array from database
     * @return string menu editor html string
     */
    public static function print_menu_editor($menu)
    {
        $editing = \Collective\Html\FormFacade::open(['route' => [config('laraadmin.adminRoute') . '.la_menus.destroy', $menu->id], 'method' => 'delete', 'style' => 'display:inline']);
        $editing .= '<button class="btn btn-xs btn-danger pull-right"><i class="fa fa-times"></i></button>';
        $editing .= \Collective\Html\FormFacade::close();
        $editing .= '<a href="'.url(config('laraadmin.adminRoute') . '/la_menus/'.$menu->id).'" class="btn btn-xs btn-success pull-right"><i class="fa fa-key"></i></a>';
        if ($menu->type != "module") {
            $info = (object)array();
            $info->id = $menu->id;
            $info->name = $menu->name;
            $info->url = $menu->url;
            $info->type = $menu->type;
            $info->icon = $menu->icon;

            $editing .= '<a class="editMenuBtn btn btn-xs btn-warning pull-right" info=\'' . json_encode($info) . '\'><i class="fa fa-edit"></i></a>';
        }
        $str = '<li class="dd-item dd3-item" data-id="' . $menu->id . '">
			<div class="dd-handle dd3-handle"></div>
			<div class="dd3-content"><i class="fa ' . $menu->icon . '"></i> ' . $menu->name . ' ' . $editing . '</div>';

        $childrens = LAMenu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

        if (count($childrens) > 0) {
            $str .= '<ol class="dd-list">';
            foreach ($childrens as $children) {
                $str .= LAHelper::print_menu_editor($children);
            }
            $str .= '</ol>';
        }
        $str .= '</li>';
        return $str;
    }

   /**
     * Print the sidebar menu view.
     * This needs to be done recursively
     *
     * LAHelper::print_menu($menu)
     *
     * @param $menu menu array from database
     * @return string menu in html string
     */
    public static function print_menu($menu, $active = false)
    {
        $childrens = LAMenu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

        $str = "";
        $treeview = "";
        $subviewSign = "";
        if (count($childrens)) {
            $treeview = " class=\"treeview\"";
            $subviewSign = '<i class="fa fa-angle-left pull-right"></i>';
        }
        $active_str = '';
        if ($active) {
            $active_str = 'class="active"';
        }
        $url = "#";
        if ($menu->url != "#") {
            $url = url(config("laraadmin.adminRoute") . '/' . $menu->url);
        }
        $ajaxload = config('laraadmin.ajaxload');
        if ($menu->url == "#" || $menu->name == "Messages" || $menu->name == "Dashboard") {
            $ajaxload = "";
        }
        if ($menu->user_access(Auth::User()->id)) {
            if (count($childrens)) {
                $access = false;
                foreach ($childrens as $children) {
                    if ($children->type == "custom") {
                        if ($menu->user_access(Auth::User()->id)) {
                            $access = true;
                        }
                    } else {
                        $module = LAModule::get($children->url);
                        if (LAModule::hasAccess($module->id)) {
                            $access = true;
                        }
                    }
                }
                if ($access) {
                    $str = '<li' . $treeview . ' ' . $active_str . '><a '.$ajaxload.' href="' . $url . '"><i class="fa ' . $menu->icon . '"></i> <span>' . LAHelper::real_module_name($menu->name) . '</span> ' . $subviewSign . '</a>';
                } else {
                    $str = '';
                }
            } else {
                $str = '<li' . $treeview . ' ' . $active_str . '><a '.$ajaxload.' href="' . $url . '"><i class="fa ' . $menu->icon . '"></i> <span>' . LAHelper::real_module_name($menu->name) . '</span> ' . $subviewSign . '</a>';
            }

            if (count($childrens)) {
                $str .= '<ul class="treeview-menu">';
                foreach ($childrens as $children) {
                    if ($children->type == "custom") {
                        if ($menu->user_access(Auth::User()->id)) {
                            $str .= LAHelper::print_menu($children);
                        }
                    } else {
                        $module = LAModule::get($children->url);
                        if (LAModule::hasAccess($module->id)) {
                            $str .= LAHelper::print_menu($children);
                        }
                    }
                }
                $str .= '</ul>';
            }
            $str .= '</li>';
        }
        return $str;
    }

    /**
     * Print the top navbar menu view.
     * This needs to be done recursively
     *
     * LAHelper::print_menu_topnav($menu)
     *
     * @param $menu menu array from database
     * @param bool $active is this menu active or not
     * @return string menu in html string
     */
    public static function print_menu_topnav($menu, $active = false)
    {
        $childrens = LAMenu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

        $treeview = "";
        $treeview2 = "";
        $subviewSign = "";
        if (count($childrens)) {
            $treeview = " class=\"dropdown\"";
            $treeview2 = " class=\"dropdown-toggle\" data-toggle=\"dropdown\"";
            $subviewSign = ' <span class="caret"></span>';
        }
        $active_str = '';
        if ($active) {
            $active_str = 'class="active"';
        }

        $str = '<li ' . $treeview . '' . $active_str . '><a ' . $treeview2 . ' href="' . url(config("laraadmin.adminRoute") . '/' . $menu->url) . '">' . LAHelper::real_module_name($menu->name) . $subviewSign . '</a>';

        if (count($childrens)) {
            $str .= '<ul class="dropdown-menu" role="menu">';
            foreach ($childrens as $children) {
                $str .= LAHelper::print_menu_topnav($children);
            }
            $str .= '</ul>';
        }
        $str .= '</li>';
        return $str;
    }

    /**
     * Get Menu for User
     */
    public static function get_user_menu($user)
    {
        $menuItems = LAMenu::where("parent", 0)->orderBy('hierarchy', 'asc')->get();
        $menuList = array();
        foreach ($menuItems as $menu) {
            if ($menu->type == "module") {
                $temp_module_obj = LAModule::get($menu->name);
                if (LAModule::hasAccess($temp_module_obj->id, "view", $user->id)) {
                    if (isset($module->id) && $module->name == $menu->name) {
                        $menuList[] = LAHelper::get_menu($menu, $user);
                    } else {
                        $menuList[] = LAHelper::get_menu($menu, $user);
                    }
                }
            } else {
                if ($menu->user_access($user->id)) {
                    $menuList[] = LAHelper::get_menu($menu, $user);
                }
            }
        }
        return $menuList;
    }
    /**
     * Get menu data
     */
    public static function get_menu($menu, $user)
    {
        $childrens = LAMenu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

        if ($menu->url != "#") {
            $menu->url = url(config("laraadmin.adminRoute") . '/' . $menu->url);
        }
        $menu->is_ajaxload = true;
        if ($menu->url == "#" || $menu->name == "Messages") {
            $menu->is_ajaxload = false;
        }

        if (count($childrens)) {
            $menu_childrens = array();
            foreach ($childrens as $children) {
                if ($children->type == "custom") {
                    if ($children->user_access($user->id)) {
                        $menu_childrens[] = LAHelper::get_menu($children, $user);
                    }
                } else {
                    $module = LAModule::get($children->url);
                    if (isset($module->id)) {
                        if (LAModule::hasAccess($module->id, "view", $user->id)) {
                            $menu_childrens[] = LAHelper::get_menu($children, $user);
                        }
                    } else {
                        $menu_childrens[] = LAHelper::get_menu($children, $user);
                    }
                }
            }
            $menu->childrens = $menu_childrens;
        }
        unset($menu['created_at']);
        unset($menu['updated_at']);
        return $menu;
    }

    /**
     * Print the role hierarchy view.
     * This needs to be done recursively
     *
     * LAHelper::print_roles($role)
     *
     * @param $role role array from database
     * @return string role in html string
     */
    public static function print_roles($role)
    {
        $childrens = Role::where("parent", $role->id)->get();
        $role_count = $role->users->count();
        $str = '<li class="dd-item" data-id="'.$role->id.'">
                <div class="dd-handle"></div>
                <div class="dd-content">
                    <div class="dd-handle" role_id="'.$role->id.'" style="">'.$role->name .'<span title="Role ID" class="pull-right">'. $role->id.'<span class="badge badge-success" title="Employee Count">'.$role_count.'</span></span></div></div>';
        $str = '<li class="dd-item dd3-item" data-id="' . $role->id . '">
			<div class="dd-handle dd3-handle"></div>
			<div class="dd3-content">'.$role->name .'<span title="Role ID" class="pull-right"><span class="badge badge-success" title="Employee Count">'.$role_count.'</span></span></div>';
        if (count($childrens) > 0) {
            $str .= '<ol class="dd-list">';
            foreach ($childrens as $children) {
                $str .= LAHelper::print_roles($children);
                //$str .= json_encode($children);
            }
            $str .= '</ol>';
        }
        $str .= '</li>';
        return $str;
    }

    /**
     * Get laravel version. very important in installation and handling Laravel 5.3 changes.
     *
     * LAHelper::laravel_ver()
     *
     * @return float|string laravel version
     */
    public static function laravel_ver()
    {
        $var = App::VERSION();

        if (str_starts_with($var, "5.2")) {
            return 5.2;
        } elseif (str_starts_with($var, "5.3")) {
            return 5.3;
        } elseif (str_starts_with($var, "5.4")) {
            return 5.4;
        } elseif (str_starts_with($var, "5.5")) {
            return 5.5;
        } elseif (str_starts_with($var, "5.6")) {
            return 5.6;
        } elseif (substr_count($var, ".") == 3) {
            $var = substr($var, 0, strrpos($var, "."));
            return $var . "-str";
        } else {
            return floatval($var);
        }
    }

    /**
     * Get real Module name by replacing underscores within name
     *
     * @param $name Module Name with whitespace filled by underscores
     * @return mixed return Module Name
     */
    public static function real_module_name($name)
    {
        $name = str_replace('_', ' ', $name);
        return $name;
    }

    /**
     * Get complete line within file by comparing passed substring $str
     *
     * LAHelper::getLineWithString()
     *
     * @param $fileName file name to be scanned
     * @param $str substring to be checked for line match
     * @return int/string return -1 if failed to find otherwise complete line in string format
     */
    public static function getLineWithString($fileName, $str)
    {
        $lines = file($fileName);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }
        return -1;
    }

    /**
     * Get complete line within given file contents by comparing passed substring $str
     *
     * LAHelper::getLineWithString2()
     *
     * @param $content content to be scanned
     * @param $str substring to be checked for line match
     * @return int/string return -1 if failed to find otherwise complete line in string format
     */
    public static function getLineWithString2($content, $str)
    {
        $lines = explode(PHP_EOL, $content);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }
        return -1;
    }

    /**
     * Method sets parameter in ".env" file as well as into php environment.
     *
     * LAHelper::setenv("CACHE_DRIVER", "array");
     *
     * @param $param parameter name
     * @param $value parameter value
     */
    public static function setenv($param, $value)
    {
        $envfile = LAHelper::openFile('.env');
        $line = LAHelper::getLineWithString('.env', $param . '=');
        $envfile = str_replace($line, $param . "=" . $value . "\n", $envfile);
        file_put_contents('.env', $envfile);

        $_ENV[$param] = $value;
        putenv($param . "=" . $value);
    }

    /**
     * Get file contents
     *
     * @param $from file path
     * @return string file contents in String
     */
    public static function openFile($from)
    {
        $md = file_get_contents($from);
        return $md;
    }

    /**
     * Delete file
     *
     * LAHelper::deleteFile();
     *
     * @param $file_path file's path to be deleted
     */
    public static function deleteFile($file_path)
    {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    /**
     * Get Migration file name by passing matching table name
     *
     * LAHelper::get_migration_file("students_table");
     *
     * @param $file_name matching table name like 'create_employees_table'
     * @return string returns migration file name if found else blank string
     */
    public static function get_migration_file($file_name)
    {
        $mfiles = scandir(base_path('database/migrations/'));
        foreach ($mfiles as $mfile) {
            if (str_contains($mfile, $file_name)) {
                $mgr_file = base_path('database/migrations/' . $mfile);
                if (file_exists($mgr_file)) {
                    return 'database/migrations/' . $mfile;
                }
            }
        }
        return "";
    }

    /**
     * Check if passed array is associative
     *
     * @param array $array array to be checked associative or not
     * @return bool true if associative
     */
    public static function is_assoc_array(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * Replace last occurance of string
     *
     * LAHelper::str_lreplace("", "", $object);
     *
     */
    public static function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * Get File Permissions in Octal
     *
     * @param $file File Path
     * @return mixed return file permissions in Octal "0777"
     */
    public static function fileperms($file)
    {
        return substr(sprintf('%o', fileperms($file)), -4);
    }

    /**
     * Compare File Permissions in Octal
     *
     * @param $file File Path
     * @return mixed return true if File Permissions are good.
     */
    public static function fileperms_cmp($perm_given, $perm_required)
    {
        if (intval($perm_given[0]) < $perm_required[0]) {
            return false;
        }
        if (intval($perm_given[1]) < $perm_required[1]) {
            return false;
        }
        if (intval($perm_given[2]) < $perm_required[2]) {
            return false;
        }
        if (intval($perm_given[3]) < $perm_required[3]) {
            return false;
        }
        return true;
    }

    /**
     * Check if mail can be sent. i.e. Mail Configuration is OK
     *
     * if(LAHelper::is_mail()) {}
     *
     * @return true if mails can be sent
     */
    public static function is_mail()
    {
        return (env('MAIL_USERNAME') !== null && env('MAIL_USERNAME') != "null" && env('MAIL_USERNAME') != "" && env('MAIL_USERNAME') != "smtp.mailtrap.ios");
    }

    /**
     * Download File
     *
     * @param $src File Path, $dest File destination path
     * @return mixed return result.
     */
    public static function downloadFile($src, $dest)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $src);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $dest);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13');
        $page = curl_exec($ch);
        $curl_error_str = curl_error($ch);
        curl_close($ch);
        $r = array("page" => $page, "curl_error_str" => $curl_error_str);
        return $r;
    }

    /**
     * Verify Correct Date Format
     */
    public static function isDateFormat($date)
    {
        return (DateTime::createFromFormat('Y-m-d H:i:s', $date) !== false);
    }

    /**
     * Print Date in Readable Format
     */
    public static function dateFormat($date)
    {
        return date("M d, Y / H:i:s", strtotime($date));
    }

    // Converts to DB Timestamp
    // $time_std == 22-08-2016
    // return == 2016-08-22
    public static function cvt2ts($time_reg)
    {
        if ($time_reg != "00-00-0000" && $time_reg != '') {
            $arr = explode("-", $time_reg);
            if (strlen($arr[2]) == 4) {
                return $arr[2]."-".$arr[1]."-".$arr[0];
            } else {
                return $time_reg;
            }
        } else {
            return null;
        }
    }
}
