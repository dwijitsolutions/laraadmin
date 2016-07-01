<?php
namespace Dwij\Laraadmin;

use Schema;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;

class LAFormMaker
{
	
	/**
	* Print input field enclosed within form-group
	**/
	public static function input($module, $field_name, $default_val = null, $required2 = null, $class = 'form-control')
	{
		$row = null;
		if(isset($module->row)) {
			$row = $module->row;
		}
		
		//print_r($module->fields);
		$label = $module->fields[$field_name]['label'];
		$field_type = $module->fields[$field_name]['field_type'];
		$readonly = $module->fields[$field_name]['readonly'];
		$defaultvalue = $module->fields[$field_name]['defaultvalue'];
		$minlength = $module->fields[$field_name]['minlength'];
		$maxlength = $module->fields[$field_name]['maxlength'];
		$required = $module->fields[$field_name]['required'];
		$popup_vals = $module->fields[$field_name]['popup_vals'];
		
		if($required2 != null) {
			$required = $required2;
		}
		
		$field_type = ModuleFieldTypes::find($field_type);
		
		$out = '<div class="form-group">';
		$required_ast = "";
		$params = [
			'class'=>'form-control',
			'placeholder'=>'Enter '.$label
		];
		if($minlength) {
			$params['data-rule-minlength'] = $minlength;
		}
		if($maxlength) {
			$params['data-rule-maxlength'] = $maxlength;
		}
		if($readonly) {
			$params['readonly'] = "readonly";
		}
		
		if($required) {
			$params['required'] = $required;
			$required_ast = "*";
		}
		
		switch ($field_type->name) {
			case 'Address':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$params['cols'] = 30;
				$params['rows'] = 3;
				$out .= Form::textarea($field_name, $default_val, $params);
				break;
			case 'Checkbox':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				// ############### Remaining
				unset($params['placeholder']);
				unset($params['data-rule-maxlength']);
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::checkbox($field_name, $field_name, $default_val, $params);
				$out .= '<div class="Switch Round On" style="vertical-align:top;margin-left:10px;"><div class="Toggle"></div></div>';
				break;
			case 'Currency':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				unset($params['data-rule-maxlength']);
				$params['data-rule-currency'] = "true";
				$params['min'] = "0";
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Date':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				$dval = $default_val;
				if($default_val != "") {
					$dval = date("d/m/Y", strtotime($default_val));
				}
				
				unset($params['data-rule-maxlength']);
				// $params['data-rule-date'] = "true";
				
				$out .= "<div class='input-group date'>";
				$out .= Form::text($field_name, $dval, $params);
				$out .= "<span class='input-group-addon'><span class='fa fa-calendar'></span></span></div>";
				// $out .= Form::date($field_name, $default_val, $params);
				break;
			case 'Datetime':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				// ############### Remaining
				$dval = $default_val;
				if($default_val != "") {
					$dval = date("d/m/Y h:i A", strtotime($default_val));
				}
				$out .= "<div class='input-group datetime'>";
				$out .= Form::text($field_name, $dval, $params);
				$out .= "<span class='input-group-addon'><span class='fa fa-calendar'></span></span></div>";
				break;
			case 'Decimal':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				unset($params['data-rule-maxlength']);
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Dropdown':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$params['data-placeholder'] = $params['placeholder'];
				unset($params['placeholder']);
				$params['rel'] = "select2";
				
				//echo $defaultvalue;
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				if($popup_vals != "") {
					$popup_vals = LAFormMaker::process_values($popup_vals);
				} else {
					$popup_vals = array();
				}
				$out .= Form::select($field_name, $popup_vals, $default_val, $params);
				break;
			case 'Email':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$params['data-rule-email'] = "true";
				$out .= Form::email($field_name, $default_val, $params);
				break;
			case 'File':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				$out .= "<div class='input-group file'>";
				$out .= Form::text($field_name, $default_val, $params);
				$out .= "<span class='input-group-addon file' file_type='file' selecter='".$field_name."'><span class='fa fa-cloud-upload'></span></span></div>";
				break;
			case 'Float':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				unset($params['data-rule-maxlength']);
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'HTML':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				// ############### Remaining
				$out .= '<div class="htmlbox" id="htmlbox_'.$field_name.'" contenteditable>'.$default_val.'</div>';
				$out .= Form::hidden($field_name, $default_val, $params);
				break;
			case 'Image':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				$out .= "<div class='input-group file'>";
				$out .= Form::text($field_name, $default_val, $params);
				$out .= "<span class='input-group-addon preview'></span>";
				$out .= "<span class='input-group-addon file' file_type='image' selecter='".$field_name."'><span class='fa fa-cloud-upload'></span></span></div>";
				break;
			case 'Integer':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				$params['min'] = "0";
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Mobile':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Multiselect':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$params['data-placeholder'] = "Select multiple ".str_plural($label);
				unset($params['placeholder']);
				$params['multiple'] = "true";
				$params['rel'] = "select2";
				if($default_val == null) {
					if($defaultvalue != "") {
						$default_val = json_decode($defaultvalue);
					} else {
						$default_val = "";
					}
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = json_decode($row->$field_name);
				}
				
				if($popup_vals != "") {
					$popup_vals = LAFormMaker::process_values($popup_vals);
				} else {
					$popup_vals = array();
				}
				$out .= Form::select($field_name."[]", $popup_vals, $default_val, $params);
				break;
			case 'Name':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Password':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::password($field_name, $default_val, $params);
				break;
			case 'Radio':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' : </label><br>';
				
				// ############### Remaining
				unset($params['placeholder']);
				unset($params['data-rule-maxlength']);
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				if($popup_vals != "") {
					$popup_vals = array_values(json_decode($popup_vals));
				} else {
					$popup_vals = array();
				}
				$out .= '<div class="radio">';
				foreach ($popup_vals as $value) {
					$sel = false;
					if($default_val != "" && $default_val == $value) {
						$sel = true;
					}
					$out .= '<label>'.(Form::radio($field_name, $value, $sel)).' '.$value.' </label>';
				}
				$out .= '</div>';
				break;
			case 'String':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Taginput':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if(isset($params['data-rule-maxlength'])) {
					$params['maximumSelectionLength'] = $params['data-rule-maxlength'];
					unset($params['data-rule-maxlength']);
				}
				$params['multiple'] = "true";
				$params['rel'] = "taginput";
				$params['data-placeholder'] = "Add multiple ".str_plural($label);
				unset($params['placeholder']);
				
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = json_decode($row->$field_name);
				}
				
				if($default_val == null) {
					$defaultvalue2 = json_decode($defaultvalue);
					if(is_array($defaultvalue2)) {
						$default_val = $defaultvalue;
					} else if(is_string($defaultvalue)) {
						if (strpos($defaultvalue, ',') !== false) {
							$default_val = array_map('trim', explode(",", $defaultvalue));
						} else {
							$default_val = [$defaultvalue];
						}
					} else {
						$default_val = array();
					}
				}
				$default_val = LAFormMaker::process_values($default_val);
				$out .= Form::select($field_name."[]", $default_val, $default_val, $params);
				break;
			case 'Textarea':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				$params['cols'] = 30;
				$params['rows'] = 3;
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::textarea($field_name, $default_val, $params);
				break;
			case 'TextField':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'URL':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if($default_val == null) {
					$default_val = $defaultvalue;
				}
				// Override the edit value
				if(isset($row) && isset($row->$field_name)) {
					$default_val = $row->$field_name;
				}
				
				$params['data-rule-url'] = "true";
				$out .= Form::text($field_name, $default_val, $params);
				break;
		}
		$out .= '</div>';
		return $out;
	}
	
	/**
	* Processes the populated values for Multiselect / Taginput / Dropdown
	**/
	private static function process_values($json) {
		$out = array();
		// Check if populated values are from Module or Database Table
		if(is_string($json) && starts_with($json, "@")) {
			
			// Get Module / Table Name
			$json = str_ireplace("@", "", $json);
			$table_name = strtolower(str_plural($json));
			
			// Search Module
			$module = Module::getByTable($table_name);
			if(isset($module)) {
				$out = Module::getDDArray($module->name);
			} else {
				// Search Table if no module found
				if (Schema::hasTable($table_name)) {
					$model = "App\\".ucfirst(str_singular($table_name));
					$result = $model::all();
					// find view column name
					$view_col = "";
					// Check if atleast one record exists
					if(isset($result[0])) {
						$view_col_test_1 = "name";
						$view_col_test_2 = "title";
						if(isset($result[0]->$view_col_test_1)) {
							// Check whether view column name == "name"
							$view_col = $view_col_test_1;
						} else if(isset($result[0]->$view_col_test_2)) {
							// Check whether view column name == "title"
							$view_col = $view_col_test_2;
						} else {
							// retrieve the second column name which comes after "id"
							$arr2 = $result[0]->toArray();
							$arr2 = array_keys($arr2);
							$view_col = $arr2[1];
							// if second column not exists
							if(!isset($result[0]->$view_col)) {
								$view_col = "";
							}
						}
						// If view column name found successfully through all above efforts
						if($view_col != "") {
							// retrieve rows of table
							foreach ($result as $row) {
								$out[$row->id] = $row->$view_col;
							}
						} else {
							// Failed to find view column name
						}
					} else {
						// Skipped efforts to detect view column name
					}
				} else if(Schema::hasTable($json)) {
					// $array = \DB::table($table_name)->get();
				}
			}
		} else if(is_string($json)) {
			$array = json_decode($json);
			if(is_array($array)) {
				foreach ($array as $value) {
					$out[$value] = $value;
				}
			} else {
				// TODO: Check posibility of comma based pop values.
			}
		} else if(is_array($json)) {
			foreach ($json as $value) {
				$out[$value] = $value;
			}
		}
		return $out;
	}
	
	/**
	* Display field using blade directive @la_display
	**/
	public static function display($module, $field_name, $class = 'form-control')
	{
		$label = $module->fields[$field_name]['label'];
		$field_type = $module->fields[$field_name]['field_type'];
		$field_type = ModuleFieldTypes::find($field_type);
		
		$row = null;
		if(isset($module->row)) {
			$row = $module->row;
		}
		
		$out = '<div class="form-group">';
		$out .= '<label for="'.$field_name.'" class="col-md-2">'.$label.' :</label>';
		
		$value = $row->$field_name;
		
		switch ($field_type->name) {
			case 'Address':
				if($value != "") {
					$value = $value.'<a target="_blank" class="pull-right btn btn-xs btn-primary btn-circle" href="http://maps.google.com/?q='.$value.'" data-toggle="tooltip" data-placement="left" title="Check location on Map"><i class="fa fa-map-marker"></i></a>';
				}
				break;
			case 'Checkbox':
				if($value == 0) {
					$value = "<div class='label label-danger'>False</div>";
				} else {
					$value = "<div class='label label-success'>True</div>";
				}
				break;
			case 'Currency':
				
				break;
			case 'Date':
				$dt = strtotime($value);
				$value = date("d M Y", $dt);
				break;
			case 'Datetime':
				$dt = strtotime($value);
				$value = date("d M Y, h:i A", $dt);
				break;
			case 'Decimal':
				
				break;
			case 'Dropdown':
				
				break;
			case 'Email':
				$value = '<a href="mailto:'.$value.'">'.$value.'</a>';
				break;
			case 'File':
				$value = '<a class="preview" target="_blank" href="'.asset($value).'"><i class="fa fa-file-o"</i></a>';
				break;
			case 'Float':
				
				break;
			case 'HTML':
				break;
			case 'Image':
				$value = '<a class="preview" target="_blank" href="'.asset($value).'"><img src="'.asset($value).'"></a>';
				break;
			case 'Integer':
				
				break;
			case 'Mobile':
				$value = '<a target="_blank" href="tel:'.$value.'">'.$value.'</a>';
				break;
			case 'Multiselect':
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
				} else {
					$valueOut = "<div class='label label-primary'>".$value."</div> ";
				}
				$value = $valueOut;
				break;
			case 'Name':
				
				break;
			case 'Password':
				$value = '<a href="#" data-toggle="tooltip" data-placement="top" title="Cannot be declassified !!!">********</a>';
				break;
			case 'Radio':
				
				break;
			case 'String':
				
				break;
			case 'Taginput':
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
				} else {
					$valueOut = "<div class='label label-primary'>".$value."</div> ";
				}
				$value = $valueOut;
				break;
			case 'Textarea':
				
				break;
			case 'TextField':
				
				break;
			case 'URL':
				$value = '<a target="_blank" href="'.$value.'">'.$value.'</a>';
				break;
		}
		
		$out .= '<div class="col-md-10 fvalue">'.$value.'</div>';
		$out .= '</div>';
		return $out;
	}
	
	/**
	* Print form using blade directive @la_form
	**/
	public static function form($module, $fields = [])
	{
		if(count($fields) == 0) {
			$fields = array_keys($module->fields);
		}
		$out = "";
		foreach ($fields as $field) {
			$out .= LAFormMaker::input($module, $field);
		}
		return $out;
	}
}