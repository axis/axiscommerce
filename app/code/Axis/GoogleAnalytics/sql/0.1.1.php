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
        Axis::single('core/config_field')
            ->remove('analytics/main/usedPageName')
            ->remove('analytics/main/affiliation')
            ->remove('analytics/attributes')
            ->remove('analytics/conversion/')
            ->remove('analytics/tracking/');
    }

    public function down()
    {
        Axis::single('core/config_field')
            ->add('analytics/main/usedPageName', 'USE PAGENAME',1 , 'bool')
            ->add('analytics/main/affiliation', 'Optional partner or store affilation', '' )
            ->add('analytics/attributes/brackets', 'Google analytics/Products attributes/PRODUCTS ATTRIBUTES BRACKETS', '[]')
            ->add('analytics/attributes/delimiter', 'PRODUCTS ATTRIBUTES DELIMITER', ';')
            ->add('analytics/conversion/used', 'Google analytics/Conversion option/Enabled', 1, 'bool', 'Enabled currency convertion')
            ->add('analytics/conversion/id', 'Id', '"')
            ->add('analytics/conversion/language', 'Language(en_EN)', 'en_EN')
            ->add('analytics/tracking/used', 'Google analytics/Tracking options/Enabled', 1, 'bool', 'Enabled tracking')
            ->add('analytics/tracking/linksPrefix', 'Prefix')
        ;
    }
}