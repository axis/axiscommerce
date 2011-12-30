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
            'Configuration_Field' => 'Axis_Core_Model_Config_Field_Type',
            'Template'            => 'Axis_Core_Model_Template',
            'Theme'               => 'Axis_Core_Model_Theme',
            'Layout'              => 'Axis_Core_Model_Template_Layout',
            'Site'                => 'Axis_Core_Model_Site',
            'MailBoxes'           => 'Axis_Core_Model_Mail_Boxes',
            'MailEvent'           => 'Axis_Core_Model_Mail_Event',
            'MailTemplate'        => 'Axis_Core_Model_Mail_Template',
            'AddressFieldStatus'  => 'Axis_Core_Model_Config_Field_Status'
        );

        $paths = array(
            'design/htmlHead/defaultRobots' => 'Axis_Core_Model_Template_Robots',
            'design/htmlHead/titlePattern'  => 'Axis_Core_Model_Template_TitlePattern',
            'mail/main/transport'           => 'Axis_Core_Model_Mail_Transport',
            'mail/smtp/secure'              => 'Axis_Core_Model_Mail_Secure'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
            
            if (isset($paths[$row->path])) {
                $row->config_options = null; 
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
        
        $rowset = Axis::single('core/config_field')->select()
            ->where('config_type = ?', 'bool')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->model = 'Axis_Core_Model_Config_Value_Boolean';
            $row->save();
        }
                      
        Axis::model('admin/acl_rule')
            ->rename('admin/axis/core/config-field',      'admin/axis/core/config_field')
            ->rename('admin/axis/core/config-field/list', 'admin/axis/core/config_field/list')

            ->rename('admin/axis/core/config-value',             'admin/axis/core/config_value')
            ->rename('admin/axis/core/config-value/index',       'admin/axis/core/config_value/index')
            ->rename('admin/axis/core/config-value/list',        'admin/axis/core/config_value/list')
            ->rename('admin/axis/core/config-value/load',        'admin/axis/core/config_value/load')
            ->rename('admin/axis/core/config-value/save',        'admin/axis/core/config_value/save')
            ->rename('admin/axis/core/config-value/copy-global', 'admin/axis/core/config_value/copy-global')
            ->rename('admin/axis/core/config-value/use-global',  'admin/axis/core/config_value/use-global')
        ;
        
        $rowset = Axis::model('admin/acl_rule')->select()
            ->where('resource_id LIKE ? ', "admin/axis/core/config-field%")
            ->fetchRowset()
            ;
        
        foreach ($rowset as $row) {
            $row->delete();
        }
    }
}