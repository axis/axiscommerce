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
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Model_Form_Message extends Axis_Form
{
    protected $_translatorModule = 'contacts';

    protected $_eventPrefix = 'contacts_form_message';

    public function __construct($options = null)
    {
        $default = array(
            'id' => 'form-contacts',
            'action' => Zend_Controller_Front::getInstance()->getBaseUrl()
                . Axis_Locale::getLanguageUrl() . '/contacts'
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }
        parent::__construct($default);
    }

    public function init()
    {
        $this->addElement('text', 'email', array(
            'required'   => true,
            'label'      => 'Email',
            'class'      => 'input-text required email',
            'validators' => array('EmailAddress')
        ));
        $this->addElement('text', 'name', array(
            'required' => true,
            'label'    => 'Full name',
            'class'    => 'input-text required'
        ));
        $this->addElement('text', 'subject', array(
            'required' => true,
            'label'    => 'Subject',
            'class'    => 'input-text required'
        ));

        $departments = Axis_Contacts_Model_Department::collect();
        if (count($departments)) {
            $this->addElement('select', 'department_id', array(
               'label' => 'Department'
            ));
            $this->getElement('department_id')->options = $departments;
        }
        $this->addElement('textarea', 'message', array(
            'required' => true,
            'label'    => 'Message',
            'class'    => 'input-text required',
            'rows'     => 6,
            'cols'     => 60
        ));
        $this->addDisplayGroup(
            $this->getElements(),
            'contacts',
            array('legend' => 'Contact Us')
        );

        $this->addElement('button', 'submit', array(
            'type' => 'submit',
            'class' => 'button',
            'label' => 'Send Message'
        ));

        $this->addActionBar(array('submit'));
    }
}
