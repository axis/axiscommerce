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

class Axis_Locale_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = 'Separate default frontend and backend language';

    public function up()
    {
        try {
            $languageId = Axis::config('locale/main/language');
        } catch (Axis_Exception $e) { // installation
            $languageId = 1;
        }

        $this->getConfigBuilder()
            ->remove('locale/main/language')
            ->section('locale')
                ->section('main')
                    ->option('language_admin', 'Default backend language', $languageId)
                        ->setType('select')
                        ->setDescription('Default backend language')
                        ->setModel('locale/option_language')
                    ->option('language_front', 'Default frontend language', $languageId)
                        ->setType('select')
                        ->setDescription('Default frontend language')
                        ->setModel('locale/option_language')

            ->section('/');       
    }

    public function down()
    {
        $languageId = Axis::config('locale/main/language_front');

        $this->getConfigBuilder()
            ->remove('locale/main/language_admin')
            ->remove('locale/main/language_front')
            ->section('locale')
                ->section('main')
                    ->option('language', 'Default language', $languageId)
                        ->setType('select')
                        ->setDescription('Default language')
                        ->setModel('locale/option_language')

            ->section('/');
    }
}