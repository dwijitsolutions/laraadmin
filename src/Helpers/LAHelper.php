<?php

namespace Dwij\Laraadmin\Helpers;

use DB;
use Log;

use Dwij\Laraadmin\Models\Module;

class LAHelper
{
	// $names = LAHelper::generateModuleNames($module_name);
    public static function generateModuleNames($module_name, $icon) {
		$array = array();
		$module_name = trim($module_name);
		$module_name = str_replace(" ", "", $module_name);
		
		$array['module'] = ucfirst(str_plural($module_name));
		$array['label'] = ucfirst(str_plural($module_name));
		$array['table'] = strtolower(str_plural($module_name));
		$array['model'] = ucfirst(str_singular($module_name));
		$array['fa_icon'] = $icon;
		$array['controller'] = $array['module']."Controller";
		$array['singular_l'] = strtolower(str_singular($module_name));
		$array['singular_c'] = ucfirst(str_singular($module_name));
		
		return (object) $array;
	}
	
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
		
		$tables_out2 = array();
		foreach ($tables_out as $table) {
			$tables_out2[$table] = $table;
		}
		
		return $tables_out2;
    }
	
	// $modules = LAHelper::getModuleNames([]);
    public static function getModuleNames($remove_modules = []) {
        $modules = Module::all();
		
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
	
	// LAHelper::parseValues($field['popup_vals']);
    public static function parseValues($value) {
		// return $value;
		$valueOut = "";
		if (strpos($value, '[') !== false) {
			$arr = json_decode($value);
			foreach ($arr as $key) {
				$valueOut .= "<div class='label label-primary'>".$key."</div> ";
			}
		} else if (strpos($value, ',') !== false) {
			$arr = array_map('trim', explode(",", $value));
			foreach ($arr as $key) {
				$valueOut .= "<div class='label label-primary'>".$key."</div> ";
			}
		} else if (strpos($value, '@') !== false) {
			$valueOut .= "<b data-toggle='tooltip' data-placement='top' title='From ".str_replace("@", "", $value)." table' class='text-primary'>".$value."</b>";
		} else if ($value == "") {
			$valueOut .= "";
		} else {
			$valueOut = "<div class='label label-primary'>".$value."</div> ";
		}
		return $valueOut;
	}
	
	// LAHelper::log("info", "", $commandObject);
	public static function log($type, $text, $commandObject) {
		if($commandObject) {
			$commandObject->$type($text);
		} else {
			if($type == "line") {
				$type = "info";
			}
			Log::$type($text);
		}
	}
	
	// LAHelper::recurse_copy("", "");
	public static function recurse_copy($src,$dst) { 
		$dir = opendir($src); 
		@mkdir($dst, 0777, true);
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					LAHelper::recurse_copy($src . '/' . $file,$dst . '/' . $file); 
				} 
				else { 
					// ignore files
					if(!in_array($file, [".DS_Store"])) {
						copy($src . '/' . $file, $dst . '/' . $file);
					}
				}
			}
		}
		closedir($dir); 
	}
	
	// LAHelper::recurse_delete("");
	public static function recurse_delete($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object))
						LAHelper::recurse_delete($dir."/".$object);
					else
						unlink($dir."/".$object); 
				}
			}
			rmdir($dir); 
		}
	}
	
	// Generate Random Password
	// $password = LAHelper::gen_password();
	public static function gen_password($chars_min=6, $chars_max=8, $use_upper_case=false, $include_numbers=false, $include_special_chars=false) {
		$length = rand($chars_min, $chars_max);
		$selection = 'aeuoyibcdfghjklmnpqrstvwxz';
		if($include_numbers) {
			$selection .= "1234567890";
		}
		if($include_special_chars) {
			$selection .= "!@\"#$%&[]{}?|";
		}
		$password = "";
		for($i=0; $i<$length; $i++) {
			$current_letter = $use_upper_case ? (rand(0,1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];            
			$password .=  $current_letter;
		}
		return $password;
	}

	// LAHelper::img($upload_id);
    public static function img($upload_id) {
        $upload = \App\Upload::find($upload_id);
        if(isset($upload->id)) {
            return url("files/".$upload->hash.DIRECTORY_SEPARATOR.$upload->name);
        } else {
			return "";
		}
    }
	
	// LAHelper::createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height);
	public static function createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height, $background=false) {
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
	    } else if ($original_type === 2) {
	        $imgt = "ImageJPEG";
	        $imgcreatefrom = "ImageCreateFromJPEG";
	    } else if ($original_type === 3) {
	        $imgt = "ImagePNG";
	        $imgcreatefrom = "ImageCreateFromPNG";
	    } else {
	        return false;
	    }
	    $old_image = $imgcreatefrom($filepath);
	    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background
	    // figuring out the color for the background
	    if(is_array($background) && count($background) === 3) {
	      list($red, $green, $blue) = $background;
	      $color = imagecolorallocate($new_image, $red, $green, $blue);
	      imagefill($new_image, 0, 0, $color);
	    // apply transparent background only if is a png image
	    } else if($background === 'transparent' && $original_type === 3) {
	      imagesavealpha($new_image, TRUE);
	      $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
	      imagefill($new_image, 0, 0, $color);
	    }
	    imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
	    $imgt($new_image, $thumbpath);
	    return file_exists($thumbpath);
	}

	// LAHelper::print_menu_editor($menu)
	public static function print_menu_editor($menu) {
		$editing = \Collective\Html\FormFacade::open(['route' => [config('laraadmin.adminRoute').'.la_menus.destroy', $menu->id], 'method' => 'delete', 'style'=>'display:inline']);
		$editing .= '<button class="btn btn-xs btn-danger pull-right"><i class="fa fa-times"></i></button>';
		$editing .= \Collective\Html\FormFacade::close();
		if($menu->type != "module") {
			$info = (object) array();
			$info->id = $menu->id;
			$info->name = $menu->name;
			$info->url = $menu->url;
			$info->type = $menu->type;
			$info->icon = $menu->icon;

			$editing .= '<a class="editMenuBtn btn btn-xs btn-success pull-right" info=\''.json_encode($info).'\'><i class="fa fa-edit"></i></a>';
		}
		$str = '<li class="dd-item dd3-item" data-id="'.$menu->id.'">
			<div class="dd-handle dd3-handle"></div>
			<div class="dd3-content"><i class="fa '.$menu->icon.'"></i> '.$menu->name.' '.$editing.'</div>';
		
		$childrens = \Dwij\Laraadmin\Models\Menu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();
		
		if(count($childrens) > 0) {
			$str .= '<ol class="dd-list">';
			foreach($childrens as $children) {
				$str .= LAHelper::print_menu_editor($children);
			}
			$str .= '</ol>';
		}
		$str .= '</li>';
		return $str;
	}

	// LAHelper::print_menu($menu)
	public static function print_menu($menu) {
		$childrens = \Dwij\Laraadmin\Models\Menu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

		$treeview = "";
		$subviewSign = "";
		if(count($childrens)) {
			$treeview = " class=\"treeview\"";
			$subviewSign = '<i class="fa fa-angle-left pull-right"></i>';
		}
		$str = '<li'.$treeview.'><a href="'.url(config("laraadmin.adminRoute") . '/' . $menu->url ) .'"><i class="fa '.$menu->icon.'"></i> <span>'.$menu->name.'</span> '.$subviewSign.'</a>';
		
		if(count($childrens)) {
			$str .= '<ul class="treeview-menu">';
			foreach($childrens as $children) {
				$str .= LAHelper::print_menu($children);
			}
			$str .= '</ul>';
		}
		$str .= '</li>';
		return $str;
	}
}
