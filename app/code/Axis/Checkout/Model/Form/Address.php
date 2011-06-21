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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Form_Address extends Axis_Form
{
    /**
     * @var array
     */
    protected  $_zones;

    protected $_translatorModule = 'account';

    protected $_fieldConfig = array();

    protected $_fields = array(
        'firstname' => array(
            'name' => 'firstname',
            'type' => 'text',
            'label' => 'Firstname',
            'class' => 'input-text'
        ),
        'lastname' => array(
            'name' => 'lastname',
            'type' => 'text',
            'label' => 'Lastname',
            'class' => 'input-text'
        ),
        'company' => array(
            'name' => 'company',
            'type' => 'text',
            'label' => 'Company',
            'class' => 'input-text'
        ),
        'phone' => array(
            'name' => 'phone',
            'type' => 'text',
            'label' => 'Telephone',
            'class' => 'input-text'
        ),
        'fax' => array(
            'name' => 'fax',
            'type' => 'text',
            'label' => 'Fax',
            'class' => 'input-text'
        ),
        'street_address' => array(
            'name' => 'street_address',
            'type' => 'text',
            'label' => 'Street',
            'class' => 'input-text'
        ),
        'city' => array(
            'name' => 'city',
            'type' => 'text',
            'label' => 'City',
            'class' => 'input-text'
        ),
        'zone_id' => array(
            'name' => 'zone_id',
            'type' => 'select',
            'label' => 'State/Province',
            'class' => 'input-zone'
        ),
        'postcode' => array(
            'name' => 'postcode',
            'type' => 'text',
            'label' => 'Zip/Postcode',
            'class' => 'input-text'
        ),
        'country_id' => array(
            'name' => 'country_id',
            'type' => 'select',
            'label' => 'Country',
            'class' => 'input-country'
        )
    );

    public function __construct($options = null)
    {
        $default = array(
            'id'        => 'form-address',
            'subform'   => false
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }

        parent::__construct($default);

        $configOptions = Axis::config('account/address_form')->toArray();
        $this->_fieldConfig = array_merge(array(
                'firstname_sort_order'  => -20,
                'firstname_status'      => 'required',
                'lastname_sort_order'   => -19,
                'lastname_status'       => 'required'
            ),
            $configOptions
        );
        uasort($this->_fields, array($this, '_sortFields'));

        $form = $this;
        if (!empty($default['subform'])) {
            $form = new Axis_Form(); // makes possible to use brackets in field names
            $form->setIsArray(true);
            $form->removeDecorator('Form');
            $form->removeDecorator('HtmlTag');
            $this->addSubForm($form, $default['subform']);
        }

        $countries = Axis_Collect_Country::collect();
        if (isset($countries['0'])
            && 'ALL WORLD COUNTRY' === $countries['0']) {

            unset($countries['0']);
        }

        $allowedCountries = $configOptions['country_id_allow'];
        if (!in_array(0, $allowedCountries)) { // ALL WORLD COUNTRIES is not selected
            $countries = array_intersect_key(
                $countries,
                array_flip($allowedCountries)
            );
        }
        $countryIds     = array_keys($countries);
        $defaultCountry = current($countryIds);
        if (!empty($options['values']['country_id'])) {
            $_defaultCountry = $options['values']['country_id'];
            if (isset($countries[$_defaultCountry])) {
                $defaultCountry = $_defaultCountry;
            }
        }

        $zones = Axis_Collect_Zone::collect();
        $this->_zones = $zones;

        if ('billing_address' == $default['subform']
            && !$customerId = Axis::getCustomerId()) {

            $form->addElement('text', 'email', array(
                'required' => true,
                'label' => 'Email',
                'class' => 'input-text required email',
                'validators' => array('EmailAddress')
            ));
            $form->addRow('email', 'email');
        }

        foreach ($this->_fields as $name => $values) {
            $status = $this->_fieldConfig[$name . '_status'];
            if ('disabled' == $status) {
                continue;
            }
            $fieldOptions = array(
                'value'     => empty($options['values'][$name]) ?
                    '' : $options['values'][$name],
                'required'  => ('required' === $status),
                'label'     => $values['label'],
                'class'     => $values['class']
                    . ('required' === $status ? ' required' : '')
            );

            if ('country_id' == $name) {
                $fieldOptions['validators'] = array(
                    new Zend_Validate_InArray(array_keys($countries))
                );
                $values['type'] = new Zend_Form_Element_Select($name, $fieldOptions);
                $values['type']->removeDecorator('HtmlTag');
                $values['type']->options = $countries;
            } else if ('zone_id' == $name) {
                $values['type'] = new Zend_Form_Element_Select($name, $fieldOptions);
                $values['type']->removeDecorator('HtmlTag');
                if (isset($zones[$defaultCountry]) && count($countries)) {
                    $values['type']->options = $zones[$defaultCountry];
                }
            }

            $form->addElement($values['type'], $name, $fieldOptions);
            $form->addRow($name, $name);
        }
    }

    /**
     * Adds registration fields to the address form
     *
     * # password
     * # password_confirm
     * # customer info fields
     *
     * @return void
     */
    public function addRegistrationFields()
    {
        $form = $this;
        if ($subform = $this->getSubForms()) {
            $form = current($subform);
        }

        $form->getElement('email')->addValidator(
            new Axis_Validate_Exists(
                Axis::model('account/customer'),
                'email',
                'site_id = ' . Axis::getSiteId()
            )
        );

        $form->addElement('checkbox', 'register', array(
            'label' => 'Create an Account'
        ));
        $form->addRow('register', 'register');

        $form->addElement('password', 'password', array(
            'required'  => true,
            'label'     => 'Password',
            'class'     => 'input-text required password',
            'validators' => array(
                'NotEmpty',
                new Axis_Validate_PasswordConfirmation(
                    'password',
                    'password_confirm'
                )
            )
        ));
        $form->addElement('password', 'password_confirm', array(
            'required'  => true,
            'label'     => 'Confirm Password',
            'class'     => 'input-text required password'
        ));

        $fields = array(
            'password',
            'password_confirm'
        );
        $rows = Axis::model('account/customer_field')->getFields();
        $displayMode = Axis::config('checkout/address_form/custom_fields_display_mode');
        foreach ($rows as $row) {
            if (!$row['required'] && 'required' == $displayMode) {
                continue;
            }

            $fieldName  = 'field_' . $row['id'];
            $fields[]   = $fieldName;
            $config = array(
                'id'        => 'field_' . $row['name'],
                'required'  => (bool) $row['required'],
                'label'     => $row['field_label'],
                'class'     => 'input-text'
            );
            if ($row['field_type'] == 'textarea') {
                $config['rows'] = 6;
                $config['cols'] = 60;
            }
            $form->addElement($row['field_type'], $fieldName, $config);
            $field = $form->getElement($fieldName);

            if ($row['required']) {
                $field->addValidator('NotEmpty')
                     ->setAttrib(
                         'class', $field->getAttrib('class') . ' required'
                    );
            }
            if (!empty($row['validator'])) {
                $field->addValidator($row['validator']);
                if ('Date' == $row['validator']) {
                    $field->setAttrib(
                        'class', $field->getAttrib('class') . ' input-date'
                    );
                }
            }
            if (!empty($row['axis_validator'])) {
                $field->addValidator(new $row['axis_validator']());
            }
            if (isset($row['customer_valueset_id'])) {
                $values = Axis::single('account/customer_valueSet_value')
                    ->getCustomValues(
                        $row['customer_valueset_id'],
                        Axis_Locale::getLanguageId()
                    );
                $field->setMultiOptions($values);
            }
        }

        $form->addDisplayGroup($fields, 'registration_fields');
        $group = $form->getDisplayGroup('registration_fields');
        foreach ($fields as $field) {
            $group->addRow($field, $field);
        }
    }

    /**
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if (isset($data['country_id'])) {
            $form = $this;
            if ($subform = $this->getSubForms()) {
                $form = current($subform);
            }
            $form->getElement('zone_id')->setAttribs(array(
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

    protected function _sortFields($a, $b)
    {
        $aSort = $this->_fieldConfig[$a['name'] . '_sort_order'];
        $bSort = $this->_fieldConfig[$b['name'] . '_sort_order'];
        if ($aSort == $bSort) {
            return 0;
        }
        return ($aSort < $bSort) ? -1 : 1;
    }
}
