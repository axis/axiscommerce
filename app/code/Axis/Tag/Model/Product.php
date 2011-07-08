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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Model_Product extends Axis_Db_Table
{
    protected $_name = 'tag_product';
    protected $_referenceMap = array(
        'Tag' => array(
            'columns'       => 'customer_tag_id',
            'refTableClass' => 'Axis_Tag_Model_Customer',
            'refColumns'    => 'id',
            'onDelete'      => self::CASCADE
        )
    );

    /**
     *
     * @param int $tagId
     * @return int
     */
    public function weightTag($tagId)
    {
        return $this->select()
            ->where('customer_tag_id = ?', $tagId)
            ->count();
    }
}