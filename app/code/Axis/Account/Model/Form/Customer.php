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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Form_Customer extends Zend_Form
{
    protected $_translatorModule = 'account';
    
    public function __construct($options = null)
    {
        parent::__construct($options);
        $db = Axis::db();
        $this->setAttrib('id', 'form-customer');
        $this->addElement('select', 'site_id', array(
            'label'    => 'Site'
        ));
        $this->getElement('site_id')
            ->setMultiOptions(Axis_Collect_Site::collect());
        $this->addElement('text', 'email', array(
            'required' => true,
            'label'    => 'Email'
        ));
        $this->addElement('password', 'password', array(
            'label'    => 'Password'
        ));
        $this->addElement('text', 'firstname', array(
            'required' => true,
            'label' => 'Firstname',
            'class' => 'input-text required'
        ));
        $this->addElement('text', 'lastname', array(
            'required' => true,
            'label' => 'Lastname',
            'class' => 'input-text required'
        ));
        $this->addElement('checkbox', 'is_active', array(
            'label' => 'Active'
        ));
        
        
        $this->addDisplayGroup(
            array('site_id' ,'email', 'password', 'firstname', 'lastname', 'is_active'),
            'login',
            array('legend' => 'General information')
        );
        
        $rows = Axis::single('account/customer_field')
            ->getFields();
        $groupsFields = array();
        foreach ($rows as $row) {
            $field = 'field_' . $row['id'];
            $this->addElement($row['field_type'], $field, array(
                'required' => (boolean) $row['required'],
                'label'    => $row['field_label']
            ));
            
            if ($row['required']) {
                $this->getElement($field)
                     ->addValidator('NotEmpty');
            }
            if (!empty($row['validator'])) {
                $this->getElement($field)->addValidator($row['validator']);
                if ($row['validator'] == 'Date') 
                    $this->getElement($field)->setAttrib('class', 'date-picker');
            }
            if (isset($row['customer_valueset_id'])) {
                $values = Axis::single('account/Customer_ValueSet_Value')
                    ->getCustomValues(
                        $row['customer_valueset_id'],
                        Axis_Locale::getLanguageId()
                    );
                $this->getElement($field)
                     ->setMultiOptions($values);
            }
            $groupsFields[$row['customer_field_group_id']][$row['id']] = $field;
        }
        
        /* add field groups */
        if (count($groupsFields)) {
            $groups = Axis::single('account/customer_fieldGroup')
                ->getCustomGroups(
                    array_keys($groupsFields), Axis_Locale::getLanguageId()
                );
            foreach ($groups as $row) {
                $this->addDisplayGroup(
                    array_values($groupsFields[$row['id']]), 
                    'group_' . $row['id'],
                    array('legend' => $row['group_label'])
                );
                $this->getDisplayGroup('group_' . $row['id'])
                    ->setDisableTranslator(true);
            }
        }
    }

    /**
     *
     * @param Zend_View_Interface $view
     * @param boolean $skipFormTag
     * @return string
     */
    public function render(
        Zend_View_Interface $view = null, $skipFormTag = false)
    {
        $content = parent::render($view);
        if ($skipFormTag) {
            $patterns[0] = '/<form.*?>/';
            $patterns[1] = '/<\/form.*?>/';
            $content = preg_replace($patterns, '', $content);
        }
        return $content;
    }
}