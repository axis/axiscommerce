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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Form_Comment extends Axis_Form
{
    protected $_translatorModule = 'account';

    public function __construct($options = null)
    {
        $page = $options['pageId'];
        unset($options['pageId']);
        $default = array(
            'id' => 'form-page-comment',
            'action' => Zend_Controller_Front::getInstance()->getBaseUrl()
                 . Axis_Locale::getLanguageUrl()
                 . '/cms/comment/add/page/'
                 . $page . '#form-page-comment'
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }

        parent::__construct($default);
        $customer = Axis::getCustomer();
        if ($customer) {
            $name = $customer->firstname . ' ' . $customer->lastname;
            $email = $customer->email;
            $name = $name ? $name : '';
            $email = $email ? $email : '';
        } else {
            $name = 'Guest';
            $email = '';
        }

        $this->addElement('hidden', 'page', array(
            'value' => $page
        ));
        $this->addElement('text', 'author', array(
            'required' => true,
            'label' => 'Name',
            'value' => $name,
            'class' => 'input-text required'
        ));
        $this->addElement('text', 'email', array(
            'required' => true,
            'label' => 'Email address',
            'value' => $email,
            'class' => 'input-text required',
            'validators' => array(
                new Zend_Validate_EmailAddress()
            )
        ));
        $this->addElement('textarea', 'content', array(
            'required' => true,
            'label' => 'Comment',
            'class' => 'input-text required',
            'rows' => '7',
            'cols' => '50'
        ));

        $this->addDisplayGroup($this->getElements(), 'comment');

        $this->addElement('button', 'submit', array(
            'type' => 'submit',
            'class' => 'button',
            'label' => 'Save'
        ));

        $this->addActionBar(array('submit'));
    }
}