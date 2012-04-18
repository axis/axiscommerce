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
 * @package     Axis_GoogleAnalytics
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_GoogleAnalytics_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_builder')
            ->section('analytics', 'Google analytics')
                ->setTranslation('Axis_GoogleAnalytics')
                ->section('main', 'General')
                    ->option('uacct', 'GOOGLE_ANALYTICS_UACCT')
                        ->setModel('core/option_crypt')
                    ->option('used', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('usedPageName', 'USE PAGENAME', true)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('affiliation', 'Optional partner or store affilation')
                ->section('/main')

                ->section('attributes', 'Products attributes')
                    ->option('brackets', 'PRODUCTS ATTRIBUTES BRACKETS', '[]')
                    ->option('delimiter', 'PRODUCTS ATTRIBUTES DELIMITER', ';')
                ->section('/attributes')

                ->section('conversion', 'Conversion option')
                    ->option('used', 'Enabled', true)
                        ->setType('radio')
                        ->setDescription('Enabled currency convertion')
                        ->setModel('core/option_boolean')
                    ->option('id', 'Id', '"')
                    ->option('language', 'Language(en_EN)', 'en_EN')
                ->section('/conversion')

                ->section('tracking', 'Tracking options')
                    ->option('used', 'Enabled', true)
                        ->setType('radio')
                        ->setDescription('Enabled tracking')
                        ->setModel('core/option_boolean')
                    ->option('linksPrefix', 'Prefix')

            ->section('/');
    }

    public function down()
    {
        Axis::single('core/config_builder')->remove('analytics');
        //Axis::single('core/template_box')->remove('Axis_GoogleAnalytics_Ga');
    }
}