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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Locale_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('locale_currency')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('locale_currency')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `code` char(3) NOT NULL,
          `title` varchar(128) NOT NULL,
          `position` tinyint(1) unsigned default NULL,
          `display` tinyint(2) unsigned default NULL,
          `format` varchar(10) default NULL,
          `currency_precision` tinyint(3) unsigned NOT NULL default '2',
          `rate` decimal(17,10) unsigned NOT NULL default '1.0000',
          PRIMARY KEY  (`id`),
          UNIQUE KEY `UNQ_LOCALE_CURRENCY` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('locale_language')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('locale_language')}` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `code` varchar(3) NOT NULL,
          `language` varchar(128) NOT NULL,
          `locale` varchar(5) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `LOCALE_LANGUAGE_CODE` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

        ");

        Axis::single('core/cache')
            ->add('locales', 1, 864000); //10 days

        Axis::single('admin/acl_resource')
            ->add('admin/locale', 'Locale')
            ->add('admin/locale_currency', 'Currency')
            ->add("admin/locale_currency/batch-save")
            ->add("admin/locale_currency/delete")
            ->add("admin/locale_currency/index")
            ->add("admin/locale_currency/list")
            ->add("admin/locale_currency/save")

            ->add('admin/locale_language', 'Language')
            ->add("admin/locale_language/change")
            ->add("admin/locale_language/delete")
            ->add("admin/locale_language/index")
            ->add("admin/locale_language/list")
            ->add("admin/locale_language/save");

        Axis::single('core/config_field')
            ->add('locale', 'Locale', null, null, array('translation_module' => 'Axis_Locale'))
            ->add('locale/main/language', 'Locale/General/Default language',  '1', 'select', 'Default site language', array('model' => 'Language'))
            ->add('locale/main/locale', 'Default locale', 'en_US', 'select', 'Default site locale', array('sort_order' => 8, 'model' => 'ZendLocale'))
            ->add('locale/main/timezone', 'Timezone', 'Europe/London', 'select', 'Timezone' , array('sort_order' => 9, 'model' => 'ZendTimezone'))
            ->add('locale/main/baseCurrency', 'Base currency', 'USD', 'select', 'Currency will be used for all online payment transactions', array('model' => 'Currency'))
            ->add('locale/main/currency', 'Default display currency', 'USD', 'handler', 'Default currency', array('model' => 'BaseCurrency'))
            ;
    }

    public function down()
    {

    }
}