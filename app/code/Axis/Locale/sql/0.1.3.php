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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Locale_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

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

        Axis::single('core/config_builder')
            ->section('locale', 'Locale')
                ->setTranslation('Axis_Locale')
                ->section('main', 'General')
                    ->option('language', 'Default language', 1)
                        ->setType('select')
                        ->setDescription('Default site language')
                        ->setModel('locale/option_language')
                    ->option('locale', 'Default locale', 'en_US')
                        ->setType('select')
                        ->setDescription('Default site locale')
                        ->setModel('locale/option_zendLocale')
                    ->option('timezone', 'Timezone', 'Europe/London')
                        ->setType('select')
                        ->setDescription('Timezone')
                        ->setModel('locale/option_zendTimezone')
                    ->option('baseCurrency', 'Base currency', 'USD')
                        ->setType('select')
                        ->setDescription('Currency will be used for all online payment transactions')
                        ->setModel('locale/option_currency')
                    ->option('currency', 'Default display currency', 'USD')
                        ->setType('select')
                        ->setDescription('Default currency')
                        ->setModel('locale/option_currency_default')

            ->section('/');
    }
}