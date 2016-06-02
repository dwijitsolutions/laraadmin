<?php
namespace Dwijitso\Sbscrud;

use Collective\Html\FormFacade as Form;
use Dwijitso\Sbscrud\Models\ModuleFieldTypes;
class SbsFormMaker
{
	public static function field($module, $field_name, $default_val = null, $required2 = null, $class = 'form-control')
	{
		//print_r($module->fields);
		$label = $module->fields[$field_name]['label'];
		$field_type = $module->fields[$field_name]['field_type'];
		$readonly = $module->fields[$field_name]['readonly'];
		$defaultvalue = $module->fields[$field_name]['defaultvalue'];
		$minlength = $module->fields[$field_name]['minlength'];
		$maxlength = $module->fields[$field_name]['maxlength'];
		$required = $module->fields[$field_name]['required'];
		
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
				
				$params['cols'] = 30;
				$params['rows'] = 3;
				$out .= Form::textarea($field_name, $default_val, $params);
				break;
			case 'Currency':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$params['data-rule-currency'] = "true";
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Date':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$params['data-rule-date'] = "true";
				$out .= Form::date($field_name, $default_val, $params);
				break;
			case 'Datetime':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				// ############### Remaining
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Decimal':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Dropdown':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				unset($params['placeholder']);
				$params['rel'] = "select2";
				if($default_val == null) {
					$default_val = array();
				}
				$out .= Form::select($field_name, $default_val, null, $params);
				break;
			case 'Email':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				$params['data-rule-email'] = "true";
				$out .= Form::email($field_name, $default_val, $params);
				break;
			case 'Float':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'HTML':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				// ############### Remaining
				$out .= '<div class="htmlbox" id="htmlbox_'.$field_name.'" contenteditable>'.$default_val.'</div>';
				$out .= Form::hidden($field_name, $default_val, $params);
				break;
			case 'Image':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Integer':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				$out .= Form::number($field_name, $default_val, $params);
				break;
			case 'Mobile':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Multiselect':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				unset($params['data-rule-maxlength']);
				unset($params['placeholder']);
				$params['multiple'] = "true";
				$params['rel'] = "select2";
				if($default_val == null) {
					$default_val = array();
				}
				$out .= Form::select($field_name, $default_val, null, $params);
				break;
			case 'Name':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Password':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::password($field_name, $default_val, $params);
				break;
			case 'Radio':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				// ############### Remaining
				unset($params['placeholder']);
				unset($params['data-rule-maxlength']);
				
				if($default_val == null) {
					$default_val = array();
				}
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'String':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'Taginput':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				if(isset($params['data-rule-maxlength'])) {
					$params['maximumSelectionLength'] = $params['data-rule-maxlength'];
					unset($params['data-rule-maxlength']);
				}
				unset($params['placeholder']);
				$params['multiple'] = "true";
				$params['rel'] = "taginput";
				if($default_val == null) {
					$default_val = array();
				}
				$out .= Form::select($field_name, $default_val, null, $params);
				break;
			case 'Textarea':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$params['cols'] = 30;
				$params['rows'] = 3;
				$out .= Form::textarea($field_name, $default_val, $params);
				break;
			case 'TextField':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				$out .= Form::text($field_name, $default_val, $params);
				break;
			case 'URL':
				$out .= '<label for="'.$field_name.'">'.$label.$required_ast.' :</label>';
				
				$params['data-rule-url'] = "true";
				$out .= Form::text($field_name, $default_val, $params);
				break;
		}
		$out .= '</div>';
		return $out;
	}
}