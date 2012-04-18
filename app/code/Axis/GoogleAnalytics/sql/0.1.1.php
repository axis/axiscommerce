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

class Axis_GoogleAnalytics_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'old config options was removed';

    public function up()
    {
        Axis::single('core/config_builder')
            ->remove('analytics/main/usedPageName')
            ->remove('analytics/main/affiliation')
            ->remove('analytics/attributes')
            ->remove('analytics/conversion/')
            ->remove('analytics/tracking/');
    }

    public function down()
    {
        Axis::single('core/config_builder')
            ->section('analytics')
                ->section('main')
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
}