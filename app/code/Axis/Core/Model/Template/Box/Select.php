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
class Axis_Core_Model_Template_Box_Select extends Axis_Db_Table_Select
{
    /**
     * Adds all names of categories where the page lies in, devided by commas
     *
     * @return Axis_Core_Model_Template_Box_Select
     */
    public function addPageIds()
    {
        $this->group('ctb.id')
            ->joinLeft(
                'core_template_box_page',
                'ctb.id = ctbp.box_id',
                array(
                    'page_ids' =>
                        new Zend_Db_Expr('GROUP_CONCAT(ctbp.page_id separator \',\')')
                )
            );

        return $this;
    }

    /**
     * Rewriting of parent method
     * Add having statement if filter by page_name is required
     *
     * @param array $filters
     * <pre>
     *  array(
     *      0 => array(
     *          field       => table_column
     *          value       => column_value
     *          operator    => =|>|<|IN|LIKE    [optional]
     *          table       => table_correlation[optional]
     *      )
     *  )
     * </pre>
     * @return Axis_Core_Model_Template_Box_Select
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            if ('page_name' != $filter['field']) {
                continue;
            }
            $this->having("page_name LIKE ?",  '%' . $filter['value'] . '%');
            unset($filters[$key]);
            break;
        }

        return parent::addFilters($filters);
    }
}