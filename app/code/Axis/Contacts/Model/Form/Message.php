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
 * @copyright   Copyright 2008-2010 Axis
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
       
       $this->addElement('text', 'email', array(
            'required'  => true,
            'label'     => 'Email',
            'class'     => 'input-text required email',
            'validators' => array('EmailAddress')
        ));
        $this->addElement('text', 'name', array(
            'required'  => true,
            'label'     => 'Full name',
            'class'     => 'input-text required'
        ));
        $this->addElement('text', 'subject', array(
            'required'  => true,
            'label'     => 'Subject',
            'class'     => 'input-text required'
        ));
        
        $departments = Axis_Collect_Department::collect();
        if (count($departments)) {
            $this->addElement('select', 'department', array(
               'label' => 'Department'
            ));
            $this->getElement('department')->options = $departments;
        }
        $this->addElement('textarea', 'message', array(
            'required'  => true,
            'label'     => 'Message',
            'class'     => 'input-text required',
            'rows' => 6,
            'cols' => 60
        ));
        $this->addDisplayGroup(
            array('email', 'name', 'subject', 'department', 'message'),
            'contacts',
            array('legend' => 'Contact Us')
        );
        
        $this->getDisplayGroup('contacts')
            ->addRow(array('email', 'name'), 'user_info')
            ->addRow(array('subject', 'department'), 'message_info')
            ->addRow('message', 'message');
        
        $this->getDisplayGroup('contacts')->getRow('user_info')
            ->addColumn('email', 'col1')
            ->addColumn('name', 'col2');
        
        $this->getDisplayGroup('contacts')->getRow('message_info')
            ->addColumn('subject', 'col1')
            ->addColumn('department', 'col2');
        
        $this->addElement('button', 'submit', array(
            'type' => 'submit',
            'class' => 'button',
            'label' => 'Send Message'
        ));
        
        $this->addActionBar(array('submit'));
    }
}