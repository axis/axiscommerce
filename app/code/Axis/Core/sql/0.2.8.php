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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_2_8 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.8';
    protected $_info = '';

    public function up()
    {
        $models = array(
            'Configuration_Field'         => 'Axis_Core_Model_Config_Field_Type',
            'CreditCard_SaveNumberAction' => 'Axis_Core_Model_CreditCard_SaveNumberType',
            'CreditCard'                  => 'Axis_Core_Model_CreditCard_Type',
            'Template'                    => 'Axis_Core_Model_Template',
            'Theme'                       => 'Axis_Core_Model_Theme',
            'Layout'                      => 'Axis_Core_Model_Template_Layout',
            'Site'                        => 'Axis_Core_Model_Site',
            'MailBoxes'                   => 'Axis_Core_Model_Mail_Boxes',
            'MailEvent'                   => 'Axis_Core_Model_Mail_Event',
            'MailTemplate'                => 'Axis_Core_Model_Mail_Template',
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
        }
        
    }
}