<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Admin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Admin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_FieldsetItems
{
    private $_valueset;
    private $_fieldLabel;

    public function __construct()
    {
        $this->_fieldLabel = 'field_label' . Axis_Locale::getLanguageId();
    }

    private function _getValues($set) {
        $vf = '[';
        if ($set && $this->_valueset[$set]['values']) {
            foreach ($this->_valueset[$set]['values'] as $value){
                $label = empty($value['label' . Axis_Locale::getLanguageId()]) ?
                    '' : addslashes($value['label' . Axis_Locale::getLanguageId()]);

                $vf .= "['$value[id]', '$label'],";
            };
        }
        $vf = substr($vf, -1) == ',' ? substr($vf, 0, -1) : $vf;
        $vf .= ']';
        return $vf;
    }

    private function _getAdditionalProp($type, $field) {
        switch ($type) {
            case 'multiselect':
                return "store: new Ext.data.SimpleStore({
                            fields: ['id', 'value'],
                            data: " . $this->_getValues($field['customer_valueset_id']) . "
                        }),
                        displayField: 'value',
                        valueField: 'id',";
            case 'combo':
                return "typeAhead: true,
                        triggerAction: 'all',
                        store: new Ext.data.SimpleStore({
                            fields: ['id', 'value'],
                            data: " . $this->_getValues($field['customer_valueset_id']) . "
                        }),
                        displayField: 'value',
                        editable: false,
                        valueField: 'id',
                        mode: 'local',
                        lazyRender: true,";
            default:
                return '';
        }
    }

    private function _getFieldType($type, $validator) {
        if ($validator == 'Date') return "datefield', format: 'Y-m-d";
        switch($type) {
            case 'select': case 'radio':
                return 'combo';
            case 'textarea':
                return 'textarea';
            case 'multiselect': case 'checkbox': case 'multiCheckbox':
                return 'multiselect';
            default:
                return 'textfield';
        }
    }

    public function fieldsetItems($fieldset, $valueset) {
        $this->_valueset = $valueset;
        $disabled = !$fieldset['is_active'];
        $json = '';
        if (sizeof($fieldset['fields']) > 0) {
            $json .= 'items: [';
            foreach ($fieldset['fields'] as $field) {
                $fieldType = $this->_getFieldType($field['field_type'], $field['validator']);
                $json .=
                    "{
                      fieldLabel: '". (isset($field[$this->_fieldLabel]) ? addslashes($field[$this->_fieldLabel]) : '') . "',
                      xtype: '" . $fieldType . "',
                      allowBlank: " . ($field['required'] ? 'false' : 'true') . ",
                      " . ($disabled ? 'disabled: true' : 'disabled:' .( $field['is_active'] ? 'false' : 'true')) . ",
                      " . $this->_getAdditionalProp($fieldType, $field) . "
                      name: 'custom_fields[field_" . $field['id'] . "]',
                      hiddenName: 'custom_fields[field_" . $field['id'] . "]',
                      anchor: '-10'
                    },\n";
            };
            return substr($json, 0, -2) . '],';
        } else return '';
    }
}