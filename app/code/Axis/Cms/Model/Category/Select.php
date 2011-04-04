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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Category_Select extends Axis_Db_Table_Select
{

    /**
     * @return Axis_Cms_Model_Category_Select
     */
    public function addCategoryContentTable()
    {
        if (array_key_exists('ccc', $this->_parts[self::FROM])) {
            return $this;
        }

        return $this->join('cms_category_content',
              'ccc.cms_category_id = cc.id'
        );
    }

    /**
     *
     * @param bool $status
     * @return Axis_Cms_Model_Category_Select
     */
    public function addActiveFilter($status = true)
    {
        return $this->where('cc.is_active = ?', (bool) $status);
    }

    /**
     *
     * @param mixed array|int $siteId
     * @return Axis_Cms_Model_Category_Select
     */
    public function addSiteFilter($siteId)
    {
        if (null === $siteId) {
            return $this;
        }
        if (!is_array($siteId)) {
            $siteId = array($siteId);
        }
        return $this->where('cc.site_id IN(?)', $siteId);
    }

    /**
     *
     * @param int $languageId
     * @return Axis_Cms_Model_Category_Select
     */
    public function addLanguageIdFilter($languageId)
    {
        return $this->addCategoryContentTable()
            ->where('ccc.language_id = ?', $languageId);
    }
}