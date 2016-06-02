<?php
namespace Dwijitso\Sbscrud;

use Collective\Html\FormFacade as Form;
use Dwijitso\Sbscrud\Models\ModuleFieldTypes;
class SbsFormMaker
{
	public static function field($module, $field_name, $default_val = null, $class = 'form-control')
	{
		print_r($module->fields);
		$label = $module->fields[$field_name]['label'];
		$field_type = $module->fields[$field_name]['field_type'];
		$readonly = $module->fields[$field_name]['readonly'];
		$defaultvalue = $module->fields[$field_name]['defaultvalue'];
		$minlength = $module->fields[$field_name]['minlength'];
		$maxlength = $module->fields[$field_name]['maxlength'];
		$required = $module->fields[$field_name]['required'];
		
		$out = "";
		// $field_type = ModuleFieldTypes::find($field_type);
		// print_r($field_type);
		switch ($field_type) {
			case 'Address':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Currency':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Date':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Datetime':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Decimal':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Dropdown':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Email':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Float':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'HTML':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Image':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Integer':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Mobile':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Multiselect':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Name':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Password':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Radio':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'String':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'Textarea':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'TextField':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
			case 'URL':
				$out = '<label for="'.$field_name.'">'.$label.' :</label>';
				$out .= Form::text($field_name, null, ['class'=>'form-control', 'placeholder'=>'Enter '.$label]);
				break;
		}
		return $out;
	}
}