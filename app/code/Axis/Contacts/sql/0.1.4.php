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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Contacts_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = '';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("
            CREATE TABLE  `{$installer->getTable('contacts_department_name')}` (
                `department_id` smallint(5) unsigned NOT NULL,
                `language_id` smallint(5) unsigned NOT NULL,
                `name` varchar(128) NOT NULL,
                PRIMARY KEY (`department_id`,`language_id`),
                KEY `FK_CONTACTS_DEPARTMENT_NAME_LANGUAGE` (`language_id`),
                KEY `FK_CONTACTS_DEPARTMENT_NAME_DEPARTMENT` (`department_id`),
                CONSTRAINT `FK_CONTACTS_DEPARTMENT_NAME_DEPARTMENT`
                    FOREIGN KEY (`department_id`)
                    REFERENCES `{$installer->getTable('contacts_department')}` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `FK_CONTACTS_DEPARTMENT_NAME_LANGUAGE`
                    FOREIGN KEY (`language_id`)
                    REFERENCES `{$installer->getTable('locale_language')}` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");

        $rowset    = Axis::model('contacts/department')->select()->fetchRowset();
        $languages = Axis::model('locale/option_language');
        $model     = Axis::model('contacts/department_name');

        foreach ($rowset as $row) {
            foreach ($languages as $languageId => $languageName) {
                $model->createRow(array(
                    'department_id' => $row->id,
                    'language_id'   => $languageId,
                    'name'          => $row->name
                ))->save();
            }
        }

        $installer->run("
            ALTER TABLE `{$installer->getTable('contacts_department')}`
                DROP COLUMN `name`;
        ");
    }
}