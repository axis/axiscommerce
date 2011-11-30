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
 * @copyright   Copyright 2008-2011 Axis
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

    protected $_eventPrefix = 'checkout_form_address';

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
    }

    public function init()
    {
        $configOptions = Axis::config('account/address_form')->toArray();
        $this->_fieldConfig = array_merge(array(
                'firstname_sort_order'  => -20,
                'firstname_status'      => 'required',
                'lastname_sort_order'   => -19,
                'lastname_status'       => 'required'
            ),
            $configOptions
        );

        $form = $this;
        if ($subform = $this->getAttrib('subform')) {
            $form = new Axis_Form(); // makes possible to use brackets in field names
            $form->setIsArray(true);
            $form->removeDecorator('Form');
            $this->addSubForm($form, $subform);
        }

        $countries = Axis_Location_Model_Country::collect();
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

        $zones = Axis_Location_Model_Zone::collect();
        $this->_zones = $zones;

        if ('billing_address' == $subform
            && !$customerId = Axis::getCustomerId()) {

            $form->addElement('text', 'email', array(
                'required' => true,
                'label' => 'Email',
                'class' => 'input-text required email',
                'validators' => array('EmailAddress')
            ));
        }

        foreach ($this->_fields as $name => $values) {
            $status = $this->_fieldConfig[$name . '_status'];
            if ('disabled' == $status) {
                continue;
            }
            $fieldOptions = array(
                'required'  => ('required' === $status),
                'label'     => $values['label'],
                'class'     => $values['class']
                    . ('required' === $status ? ' required' : '')
            );
            if (isset($this->_fieldConfig[$name . '_sort_order'])) {
                $fieldOptions['order'] = $this->_fieldConfig[$name . '_sort_order'];
            }

            if ('country_id' == $name) {
                $fieldOptions['validators'] = array(
                    new Zend_Validate_InArray(array_keys($countries))
                );
                $values['type'] = new Zend_Form_Element_Select($name, $fieldOptions);
                $values['type']->removeDecorator('HtmlTag')
                    ->addDecorator('HtmlTag', array(
                        'tag'   => 'li',
                        'id'    => "{$subform}-{$name}-row",
                        'class' => 'element-row'
                    ));
                $values['type']->options = $countries;
            } else if ('zone_id' == $name) {
                $values['type'] = new Zend_Form_Element_Select($name, $fieldOptions);
                $values['type']->removeDecorator('HtmlTag')
                    ->addDecorator('HtmlTag', array(
                        'tag'   => 'li',
                        'id'    => "{$subform}-{$name}-row",
                        'class' => 'element-row'
                    ));
                if (isset($zones[$defaultCountry]) && count($countries)) {
                    $values['type']->options = $zones[$defaultCountry];
                }

                // zone name field
                $zoneNameOptions = $fieldOptions;
                $zoneNameOptions['order']++;
                $zoneNameOptions['class'] .= ' input-text';
                $form->addElement('text', 'state', $zoneNameOptions);
            }

            $form->addElement($values['type'], $name, $fieldOptions);
        }

        $form->addDisplayGroup($form->getElements(), $subform);
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
                'class'     => in_array($row['field_type'], array('textarea', 'text')) ? 'input-text' : ''
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
                if (method_exists($field, 'setMultiOptions')) {
                    $field->setMultiOptions($values);
                }
            }
        }

        $form->addDisplayGroup($fields, 'registration_fields');
    }

    /**
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data)
    {
        $form = $this;
        if ($subform = $this->getSubForms()) {
            $form = current($subform);
        }

        if (isset($data['country_id']) && $zone = $form->getElement('zone_id')) {
            if (!empty($this->_zones[$data['country_id']])) {
                $zone->setAttribs(array(
                    'options' => $this->_zones[$data['country_id']]
                ));
                $form->getElement('state')->setRequired(false);
            } else {
                $zone->setRegisterInArrayValidator(false);
                $zone->setRequired(false);
            }
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

        $form = $this;
        if ($subform = $this->getSubForms()) {
            $form = current($subform);
        }
        if (array_key_exists('zone_id', $defaults)
            && isset($this->_zones[$defaults['country_id']])) {

            $form->getElement('zone_id')->setAttribs(array(
                'options' =>  $this->_zones[$defaults['country_id']]
            ));
            $form->setDefault('zone_id', $defaults['zone_id']);
        }
        return $this;
    }
}
