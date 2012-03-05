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
 * @copyright   Copyright 2008-2011 Axis
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

    protected $_fieldConfig = array();

    protected $_eventPrefix = 'account_form_address';

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
            'id' => 'form-new-address',
            'action' => Zend_Controller_Front::getInstance()->getBaseUrl()
                 . Axis_Locale::getLanguageUrl() . '/account/address-book/save'
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }
        parent::__construct($default);
    }

    public function init()
    {
        $this->addElement('hidden', 'id', array(
            'validators' => array(
                new Axis_Account_Model_Form_Validate_AddressId()
            )
        ));

        $configOptions = Axis::config('account/address_form')->toArray();
        $this->_fieldConfig = array_merge(array(
                'firstname_sort_order'  => -20,
                'firstname_status'      => 'required',
                'lastname_sort_order'   => -19,
                'lastname_status'       => 'required'
            ),
            $configOptions
        );

        $countries = Axis_Location_Model_Option_Country::getConfigOptionsArray();
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

        $zones = Axis_Location_Model_Option_Zone::getConfigOptionsArray();
        $this->_zones = $zones;
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
                        'id'    => "{$name}-row",
                        'class' => 'element-row'
                    ));
                $values['type']->options = $countries;
            } else if ('zone_id' == $name) {
                $values['type'] = new Zend_Form_Element_Select($name, $fieldOptions);
                $values['type']->removeDecorator('HtmlTag')
                    ->addDecorator('HtmlTag', array(
                        'tag'   => 'li',
                        'id'    => "{$name}-row",
                        'class' => 'element-row'
                    ));
                if (isset($zones[$defaultCountry]) && count($countries)) {
                    $values['type']->options = $zones[$defaultCountry];
                }

                // zone name field
                $zoneNameOptions = $fieldOptions;
                $zoneNameOptions['order']++;
                $zoneNameOptions['class'] .= ' input-text';
                $this->addElement('text', 'state', $zoneNameOptions);
            }

            $this->addElement($values['type'], $name, $fieldOptions);
        }

        $this->addDisplayGroup(
            $this->getElements(),
            'address',
            array('legend' => 'Address')
        );

        $this->addElement('checkbox', 'default_billing', array(
            'label' => 'Use as my default billing address',
            'class' => 'input-checkbox'
        ));
        $element = $this->getElement('default_billing');

        $this->addElement('checkbox', 'default_shipping', array(
            'label' => 'Use as my default shipping address',
            'class' => 'input-checkbox'
        ));
        $element = $this->getElement('default_shipping');

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
        if (isset($data['country_id']) && $zone = $this->getElement('zone_id')) {
            if (!empty($this->_zones[$data['country_id']])) {
                $zone->setAttribs(array(
                    'options' => $this->_zones[$data['country_id']]
                ));
                $this->getElement('state')->setRequired(false);
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
        if (array_key_exists('zone_id', $defaults)
            && isset($this->_zones[$defaults['country_id']])) {

            $this->getElement('zone_id')->setAttribs(array(
                'options' =>  $this->_zones[$defaults['country_id']]
            ));
            $this->setDefault('zone_id', $defaults['zone_id']);
        }
        return $this;
    }
}
