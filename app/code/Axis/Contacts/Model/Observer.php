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
class Axis_Contacts_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'customer' => array(
                'pages' => array(
                    'contacts/index' => array(
                        'label'         => 'Incoming Box',
                        'order'         => 50,
                        'translator'    => 'Axis_Contacts',
                        'module'        => 'Axis_Contacts',
                        'controller'    => 'index',
                        'action'        => 'index',
                        'route'         => 'admin/axis/contacts',
                        'resource'      => 'admin/axis/contacts/index/index'
                    )
                )
            )
        ));
    }
}
