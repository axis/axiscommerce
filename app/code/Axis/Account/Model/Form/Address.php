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
class Axis_Account_Model_Form_Address extends Axis_Form
{
    /**
     * @var array
     */
    protected  $_zones;

    protected $_translatorModule = 'account';

    public function __construct($options = null)
    {
        $default = array(
            'id' => 'form-new-address',
            'action' => Zend_Controller_Front::getInstance()->getBaseUrl()
                 . Axis_Locale::getLanguageUrl() . '/account/address-book/save'
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }

        parent::__construct($default);

        $this->addElement('hidden', 'id', array(
                'validators' => array(
                    new Axis_Account_Model_Form_Validate_AddressId()
                )
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
        $this->addElement('text', 'company', array(
            'label' => 'Company',
            'class' => 'input-text'
        ));
        $this->addElement('text', 'phone', array(
            'required' => true,
            'label' => 'Telephone',
            'class' => 'input-text required'
        ));
        $this->addElement('text', 'fax', array(
            'label' => 'Fax',
            'class' => 'input-text'
        ));

        $this->addDisplayGroup(
            array('firstname', 'lastname', 'company', 'phone', 'fax'),
            'contact_info',
            array('legend' => 'Contact information')
        );
        $this->getDisplayGroup('contact_info')
            ->addRow(array('firstname', 'lastname'), 'name')
            ->addRow('company', 'company')
            ->addRow(array('phone', 'fax'), 'communication');

        $this->getDisplayGroup('contact_info')->getRow('name')
            ->addColumn('firstname', 'firstname')
            ->addColumn('lastname', 'lastname');

        $this->getDisplayGroup('contact_info')->getRow('communication')
            ->addColumn('phone', 'phone')
            ->addColumn('fax', 'fax');

        $this->addElement('text', 'street_address', array(
            'label' => 'Street',
            'class' => 'input-text'
        ));
        $this->addElement('text', 'city', array(
            'required' => true,
            'label' => 'City',
            'class' => 'input-text required'
        ));
        $this->addElement('select', 'zone_id', array(
           'label' => 'State/Province',
           'class' => 'input-text input-zone'
        ));
        $countries = Axis_Collect_Country::collect();
        if (isset($countries['0'])
            && 'ALL WORLD COUNTRY' === $countries['0']) {

            unset($countries['0']);
        }

        $zones = Axis_Collect_Zone::collect();
        $this->_zones = $zones;
        if (isset($zones[key($countries)]) && count($countries)) {
            $this->getElement('zone_id')->options = $zones[key($countries)];
        }
        $this->addElement('text', 'postcode', array(
            'required' => true,
            'label' => 'Zip/Postcode',
            'class' => 'input-text required'
        ));
        if (count($countries)) {
            $this->addElement('select', 'country_id', array(
                'required' => true,
                'label' => 'Country',
                'class' => 'input-text required input-country',
                'validators' => array(
                    new Zend_Validate_InArray(array_keys($countries))
                )
            ));
            $this->getElement('country_id')->options = $countries;
        }

        $this->addDisplayGroup(
            array('street_address', 'city', 'zone_id', 'postcode', 'country_id'),
            'address',
            array('legend' => 'Address')
        );
        $this->getDisplayGroup('address')
            ->addRow('street_address', 'row1')
            ->addRow(array('city', 'country_id'), 'row2')
            ->addRow(array('postcode', 'zone_id'), 'row3');

        $this->getDisplayGroup('address')->getRow('row2')
            ->addColumn('city', 'city')
            ->addColumn('country_id', 'country_id');

        $this->getDisplayGroup('address')->getRow('row3')
            ->addColumn('postcode', 'postcode')
            ->addColumn('zone_id', 'zone_id');

        $this->addElement('button', 'submit', array(
            'type' => 'submit',
            'class' => 'button',
            'label' => 'Save'
        ));

        $this->addActionBar(array('submit'));
    }

    /**
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if (isset($data['country_id'])) {
            $this->getElement('zone_id')->setAttribs(array(
                'options' => isset($this->_zones[$data['country_id']]) ?
                    $this->_zones[$data['country_id']] : ''
            ));
        }

        return parent::isValid($data);
    }

    /**
     *
     * @return array
     */
    public function getZones()
    {
        return $this->_zones;
    }

    /**
     *
     * @param array $defaults
     * @return Axis_Account_Model_Form_Address Fluent interface
     */
    public function setDefaults(array $defaults)
    {
        parent::setDefaults($defaults);
        if (array_key_exists('zone_id', $defaults)
            && isset($this->_zones[$defaults['country_id']])) {

            $this->getElement('zone_id')->setAttribs(array(
                'options' =>  $this->_zones[$defaults['country_id']]
            ));
            $this->setDefault('zone_id', $defaults['zone_id']);
        }
        return $this;
    }

    public function addGuestField()
    {
        $element = $this->createElement('text', 'email', array(
            'required'  => true,
            'label'     => 'Email',
            'class'     => 'input-text required email',
            'validators' => array('EmailAddress')
        ));

        $this->getDisplayGroup('contact_info')
            ->addElement($element);

        $this->getDisplayGroup('contact_info')
            ->addRow('email', 'email');

        return $this;
    }

    /**
     *
     * @return  Axis_Account_Model_Form_Address Fluent interface
     */
    public function addRegisterField()
    {
        $this->addElement('text', 'email', array(
            'required'  => true,
            'label'     => 'Email',
            'class'     => 'input-text required email',
            'description' => 'Email will be used to login into your account',
            'validators' => array(
                'EmailAddress',
                new Axis_Validate_Exists(
                    Axis::single('account/customer'), 'email', 'site_id = ' . Axis::getSiteId()
                )
            )
        ));
        $this->addElement('password', 'register_password', array(
            'required' => true,
            'label'    => 'Password',
            'class'    => 'input-text required password',
            'validators' => array(
                'NotEmpty',
                new Axis_Validate_PasswordConfirmation(
                    'register_password',
                    'register_password_confirm'
                )
            )
        ));
        $this->addElement('password', 'register_password_confirm', array(
            'required' => true,
            'label'    => 'Confirm Password',
            'class'     => 'input-text required password'
        ));

        $this->addDisplayGroup(
            array('email', 'register_password', 'register_password_confirm'),
            'login',
            array('legend' => 'Login information')
        );

        $this->getDisplayGroup('login')
            ->addRow(array('email'), 'row1', array('colsetClass' => 'col2-set'))
            ->addRow(array('register_password', 'register_password_confirm'), 'row2');

        $this->getDisplayGroup('login')
            ->getRow('row1')
            ->addColumn(array('email'), 'col1');

        $this->getDisplayGroup('login')
            ->getRow('row2')
            ->addColumn(array('register_password'), 'col1')
            ->addColumn(array('register_password_confirm'), 'col2');

        $rows = Axis::single('account/customer_field')
            ->getFields();
        $groupsFields = array();
        foreach ($rows as $row) {
            $field = 'field_' . $row['id'];
            $config = array(
                'id' => 'field_' . $row['name'],
                'required' => (boolean) $row['required'],
                'label'    => $row['field_label'],
                'class'    => 'input-text'
            );
            if ($row['field_type'] == 'textarea') {
                $config['rows'] = 6;
                $config['cols'] = 60;
            }
            $this->addElement($row['field_type'], $field, $config);

            if ($row['required']) {
                $this->getElement($field)
                     ->addValidator('NotEmpty')
                     ->setAttrib(
                         'class',
                         $this->getElement($field)->getAttrib('class')
                            . ' required'
                    );
            }
            if (!empty($row['validator'])) {
                $this->getElement($field)->addValidator($row['validator']);
                if ($row['validator'] == 'Date') {
                    $this->getElement($field)
                        ->setAttrib(
                            'class' ,
                            $this->getElement($field)
                                ->getAttrib('class') . ' input-date'
                        );
                }
            }
            if (!empty($row['axis_validator'])) {
                $this->getElement($field)
                    ->addValidator(new $row['axis_validator']());
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
                $groupName = empty($row['name']) ?
                        $row['id'] : $row['name'];
                $this->addDisplayGroup(
                    array_values($groupsFields[$row['id']]),
                    $groupName,
                    array(
                        'legend' => $row['group_label'],
                        'colsetClass' => 'col2-set'
                    )
                );
                $this->getDisplayGroup($groupName)->addColumn(
                        array_values($groupsFields[$row['id']]), 'col'
                    );
            }
        }

        return $this;
    }

    /**
     * @return  Axis_Account_Model_Form_Address Fluent interface
     */
    public function addUseAsDefaultAddressCheckbox()
    {
        $element = $this->createElement('checkbox', 'default_billing', array(
            'label' => 'Use as my default billing address',
            'class' => 'input-checkbox'
        ));
        $element->addDecorator('Label', array(
                'tag' => '',
                'placement' => 'append',
                'separator' => ''
            ))
            ->addDecorator('HtmlTag', array(
                'tag' => 'div',
                'class' => 'label-inline'
            ));
        $this->getDisplayGroup('address')
            ->addElement($element)
            ->addRow('default_billing', 'default_billing');

        $element = $this->createElement('checkbox', 'default_shipping', array(
            'label' => 'Use as my default shipping address',
            'class' => 'input-checkbox'
        ));
        $element->addDecorator('Label', array(
                'tag' => '',
                'placement' => 'append',
                'separator' => ''
            ))
            ->addDecorator('HtmlTag', array(
                'tag' => 'div',
                'class' => 'label-inline'
            ));
        $this->getDisplayGroup('address')
            ->addElement($element)
            ->addRow('default_shipping', 'default_shipping');

        return $this;
    }
}