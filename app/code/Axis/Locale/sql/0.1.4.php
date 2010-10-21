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


class Axis_Locale_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = 'Separate default frontend and backend language';

    public function up()
    {
        $languageId = Axis::config('locale/main/language');

        Axis::single('core/config_field')->remove('locale/main/language');

        Axis::single('core/config_field')
            ->add('locale/main/language_admin', 'Locale/General/Default backend language', $languageId, 'select', 'Default backend language', array('model' => 'Language'))
            ->add('locale/main/language_front', 'Locale/General/Default frontend language', $languageId, 'select', 'Default frontend language', array('model' => 'Language'));
    }

    public function down()
    {
        $languageId = Axis::config('locale/main/language_front');

        Axis::single('core/config_field')
            ->remove('locale/main/language_admin')
            ->remove('locale/main/language_front');

        Axis::single('core/config_field')
            ->add('locale/main/language', 'Locale/General/Default language', $languageId, 'select', 'Default language', array('model' => 'Language'));
    }
}