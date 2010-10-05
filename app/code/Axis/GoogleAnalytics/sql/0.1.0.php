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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */


class Axis_GoogleAnalytics_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')
            ->add('analytics', 'Google analytics', null, null, array('translation_module' => 'Axis_GoogleAnalytics'))
            ->add('analytics/main/uacct', 'Google analytics/General/GOOGLE_ANALYTICS_UACCT', '', 'handler', '', array('model' => 'Crypt'))
            ->add('analytics/main/used', 'Enabled', 0 , 'bool')
            ->add('analytics/main/usedPageName', 'USE PAGENAME',1 , 'bool')
            ->add('analytics/main/affiliation', 'Optional partner or store affilation', '' )
            ->add('analytics/attributes/brackets', 'Google analytics/Products attributes/PRODUCTS ATTRIBUTES BRACKETS', '[]')
            ->add('analytics/attributes/delimiter', 'PRODUCTS ATTRIBUTES DELIMITER', ';')
            ->add('analytics/conversion/used', 'Google analytics/Conversion option/Enabled', 1, 'bool', 'Enabled currency convertion')
            ->add('analytics/conversion/id', 'Id', '"')
            ->add('analytics/conversion/language', 'Language(en_EN)', 'en_EN')
            ->add('analytics/tracking/used', 'Google analytics/Tracking options/Enabled', 1, 'bool', 'Enabled tracking')
            ->add('analytics/tracking/linksPrefix', 'Prefix');
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        Axis::single('core/config_field')->remove('analytics');
        Axis::single('core/config_value')->remove('analytics');
        //Axis::single('core/template_box')->remove('Axis_GoogleAnalytics_Ga');
    }
}