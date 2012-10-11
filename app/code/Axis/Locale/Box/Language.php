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
 * @subpackage  Axis_Locale_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Box_Language extends Axis_Core_Box_Abstract
{
    protected $_title = 'Language';
    protected $_class = 'box-language';

    protected function _construct()
    {
        $this->setData('cache_tags', 'locales');
    }

    protected function _beforeRender()
    {
        $languages = Axis::single('locale/language')
            ->select(array('locale', 'language'))
            ->fetchPairs();
        
        if (count($languages) <= 1) {
            return false;
        }
        $this->languages = $languages;
    }
}
