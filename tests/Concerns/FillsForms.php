<?php

namespace Tests\Concerns;

use App\Models\LAModuleFieldType;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Reads a form from a rendered page, fills every field with a valid value for its module field type, and submits
 * it the way LaraAdmin's JavaScript does (getFormDataJSON in public/la-assets/js/app.js).
 */
trait FillsForms
{
    /**
     * The successful controls of a form, like jQuery's serializeArray(): [[name, value], ...].
     *
     * Also returns the options of each select / radio group in $choices, so values can be picked from them.
     *
     * @param  string  $html
     * @param  string  $formId  form id, or an XPath expression starting with "/"
     * @param  array  $choices
     * @return array<int, array{0: string, 1: string}>
     */
    protected function formControls($html, $formId, &$choices = [])
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $form = $xpath->query(str_starts_with($formId, '/') ? $formId : '//form[@id="'.$formId.'"]')->item(0);
        $this->assertNotNull($form, "Form #$formId not found");

        $pairs = [];
        $choices = [];
        /** @var DOMElement $el */
        foreach ($xpath->query('.//input[@name] | .//select[@name] | .//textarea[@name]', $form) as $el) {
            $name = $el->getAttribute('name');
            if ($el->hasAttribute('disabled')) {
                continue;
            }
            if ($el->tagName === 'select') {
                $choices[$name] = $choices[$name] ?? [];
                foreach ($xpath->query('.//option', $el) as $option) {
                    $choices[$name][] = $option->getAttribute('value');
                    if ($option->hasAttribute('selected')) {
                        $pairs[] = [$name, $option->getAttribute('value')];
                    }
                }

                continue;
            }
            if ($el->tagName === 'textarea') {
                $pairs[] = [$name, $el->textContent];

                continue;
            }
            $type = strtolower($el->getAttribute('type') ?: 'text');
            if (in_array($type, ['submit', 'button', 'reset', 'file', 'image'], true)) {
                continue;
            }
            if (in_array($type, ['checkbox', 'radio'], true)) {
                $choices[$name][] = $el->getAttribute('value') ?: 'on';
                if ($el->hasAttribute('checked')) {
                    $pairs[] = [$name, $el->getAttribute('value') ?: 'on'];
                }

                continue;
            }
            $pairs[] = [$name, $el->getAttribute('value')];
        }

        return $pairs;
    }

    /**
     * Fill the form's module fields with valid values. Controls that are not module fields keep their value unless
     * given in $overrides ([name => value or list of values]).
     *
     * @param  object  $module  LAModule::get() result
     * @param  array  $pairs  formControls() result
     * @param  array  $choices  formControls() choices
     * @param  string  $seed  makes text and unique values distinct between forms
     * @param  array  $overrides
     * @return array<int, array{0: string, 1: string}>
     */
    protected function fillControls($module, $pairs, $choices, $seed, $overrides = [])
    {
        $types = LAModuleFieldType::getFTypes2();
        $names = array_unique(array_merge(array_column($pairs, 0), array_keys($choices)));

        $filled = [];
        foreach ($names as $name) {
            $colname = str_ends_with($name, '[]') ? substr($name, 0, -2) : $name;
            if (array_key_exists($name, $overrides)) {
                $values = (array) $overrides[$name];
            } elseif (isset($module->fields[$colname])) {
                $field = $module->fields[$colname];
                $values = (array) $this->fieldValue($types[$field['field_type']], $field, $choices[$name] ?? [], $seed);
            } else {
                $values = array_column(array_filter($pairs, fn ($pair) => $pair[0] === $name), 1);
            }
            foreach ($values as $value) {
                $filled[] = [$name, (string) $value];
            }
        }

        return $filled;
    }

    /**
     * A valid form value for a module field.
     *
     * @param  string  $type  field type name
     * @param  array  $field  module field row
     * @param  array  $choices  options rendered for the field
     * @param  string  $seed
     * @return string|array
     */
    protected function fieldValue($type, $field, $choices, $seed)
    {
        $choices = array_values(array_filter($choices, fn ($value) => $value !== '' && $value !== '0'));
        $text = fn ($text) => $this->fitLength($text, $field);

        return match ($type) {
            'Address' => $text("12 $seed Street, Pune"),
            'Checkbox' => 'on',
            'Currency' => '1234.50',
            'Date' => '24/10/2026',
            'Datetime' => '24/10/2026 10:30 AM',
            'Decimal', 'Float' => '12.5',
            'Dropdown', 'Radio' => $choices[0] ?? '',
            'Email' => strtolower(preg_replace('/\W+/', '', $seed)).'.'.$field['colname'].'@example.com',
            'File', 'Image' => '1',
            'Files' => '["1"]',
            'HTML' => "<p>$seed <b>content</b></p>",
            'Integer' => '42',
            'Mobile' => $text('9876543210'),
            'Multiselect' => array_slice($choices, 0, 2),
            'Name', 'String', 'TextField' => $text("$seed {$field['label']}"),
            'Password' => 'secret1234',
            'Taginput' => ['alpha', 'beta'],
            'Textarea' => $text("$seed note"),
            'URL' => 'https://example.com/'.strtolower(preg_replace('/\W+/', '-', $seed)),
            'Location' => '18.5204,73.8567',
            'Color' => '#336699',
            'Time' => '10:30 AM',
            'JSON' => '{"key":"value"}',
            'List' => '["one","two"]',
            'Duration' => '90',
            'Checklist' => '[{"title":"First","checked":false}]',
        };
    }

    /**
     * Pad or cut text to the field's length limits.
     *
     * @return string
     */
    protected function fitLength($text, $field)
    {
        if ($field['maxlength'] > 0 && strlen($text) > $field['maxlength']) {
            $text = substr($text, 0, $field['maxlength']);
        }

        return str_pad($text, max((int) $field['minlength'], strlen($text)), 'x');
    }

    /**
     * The JSON body LaraAdmin's getFormDataJSON() builds: "name[]" controls become arrays under "name".
     *
     * @param  array  $pairs
     * @return array
     */
    protected function formJson($pairs)
    {
        $data = [];
        foreach ($pairs as [$name, $value]) {
            if (str_ends_with($name, '[]')) {
                $data[substr($name, 0, -2)][] = $value;
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Submit form data as an Ajax request with a JSON body, as LaraAdmin's add / edit modals do.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $pairs
     * @return \Illuminate\Testing\TestResponse
     */
    protected function submitAjax($method, $url, $pairs)
    {
        $data = $this->formJson($pairs);
        unset($data['_method']);

        return $this->call($method, $url, [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode($data));
    }

    /**
     * Submit form data as a regular form post.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $pairs
     * @return \Illuminate\Testing\TestResponse
     */
    protected function submitForm($method, $url, $pairs)
    {
        $data = [];
        foreach ($pairs as [$name, $value]) {
            if (str_ends_with($name, '[]')) {
                $data[substr($name, 0, -2)][] = $value;
            } else {
                $data[$name] = $value;
            }
        }

        return $this->call($method, $url, $data);
    }
}
