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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Option_Template_TitlePattern extends Axis_Config_Option_Array_Multi
{
    // @todo PROFIT:
    // refac const type multiselect like rwx
    // PAGE_TITLE - 1, PARENT_PAGE_TITLE- 2 , SITE_NAME - 4 
    // 0 - NONE, 1 - PAGE_TITLE, ..., 5 - PAGE_TITLE + SITE_NAME, ...
    const PAGE_TITLE        = 'Page Title'; 
    const PARENT_PAGE_TITLE = 'Parent Page Titles';
    const SITE_NAME         = 'Site Name';
    
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::PAGE_TITLE        => 'Page Title',
            self::PARENT_PAGE_TITLE => 'Parent Page Titles',
            self::SITE_NAME         => 'Site Name'
        );
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return self::PAGE_TITLE . ',' . self::SITE_NAME;
    }
}