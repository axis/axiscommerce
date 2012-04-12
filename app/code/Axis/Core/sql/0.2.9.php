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

class Axis_Core_Upgrade_0_2_9 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.9';
    protected $_info = '';

    public function up()
    {
        $model = Axis::single('core/config_field');
        $models = array(
            'Configuration_Field' => 'core/option_config_field_type',
            'Template'            => 'core/option_template',
            'Theme'               => 'core/option_theme',
            'Layout'              => 'core/option_template_layout',
            'Site'                => 'core/option_site',
            'MailBoxes'           => 'core/option_mail_boxes',
            'MailEvent'           => 'core/option_mail_event',
            'MailTemplate'        => 'core/option_mail_template',
            'AddressFieldStatus'  => 'core/option_config_field_status'
        );

        $paths = array(
            'design/htmlHead/defaultRobots' => 'core/option_template_robots',
            'design/htmlHead/titlePattern'  => 'core/option_template_titlePattern',
            'mail/main/transport'           => 'core/option_mail_transport',
            'mail/smtp/secure'              => 'core/option_mail_secure',
            'core/store/zone'               => 'core/option_store_zone',
            'core/company/zone'             => 'core/option_company_zone'
        );
        $rowset = $model->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
            
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
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
        
        $installer = $this->getInstaller();
        
        $columns = $model->info('cols');
        if (in_array('model_assigned_with', $columns)) {
            $installer->run("
                ALTER TABLE `{$installer->getTable('core_config_field')}` 
                    DROP COLUMN `model_assigned_with`;
            ");
        }
        
        if (in_array('config_options', $columns)) {
            $installer->run("
                ALTER TABLE `{$installer->getTable('core_config_field')}` 
                    DROP COLUMN `config_options`;
            ");
        }
        if (in_array('config_type', $columns)) {
            $installer->run("
                ALTER TABLE `{$installer->getTable('core_config_field')}` 
                    CHANGE COLUMN `config_type` `type` VARCHAR(128);
            ");
        }
            
        $rowset = $model->select()
            ->where('type = ?', 'radio')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->model = 'core/option_boolean';
            $row->save();
        }
        
        $rowset = $model->select()
            ->where('model = ?', 'Crypt')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->type = 'text';
            $row->model = 'core/option_crypt';
            $row->save();
        }
        $rowset = $model->select()
            ->where('type = ?', 'text')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->type = 'textarea';
            $row->save();
        }
        $rowset = $model->select()
            ->where('type = ?', 'string')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->type = 'text';
            $row->save();
        }
    }
}