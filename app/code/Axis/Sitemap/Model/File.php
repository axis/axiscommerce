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
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sitemap_Model_File extends Axis_Db_Table
{
    protected $_name = 'sitemap_file';

    public function getList($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('cw' => $this->_prefix . 'sitemap_file'));

        if (!empty($params['limit']))
            $select->limit($params['limit'], $params['start']);
        if (!empty($params['sort']))
            $select->order($params['sort'] . ' ' . $params['dir']);

        if (isset($params['filters'])) {
            $filterGrid = $params['filters'];
            foreach ($filterGrid as $filter) {
                switch ($filter['data']['type']) {
                    case 'numeric': case 'date':
                        $condition = $filter['data']['comparison'] == 'eq' ? '=' :
                                         ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        $select->where("cw.$filter[field] $condition ?", $filter['data']['value']);
                        break;
                    case 'list':
                        $select->where($this->getAdapter()->quoteInto("
                            cw.$filter[field] IN (?)", explode(',', $filter['data']['value'])));
                        break;
                    default:
                            $select->where("cw.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        break;
                }
            }
        }
        return $select->query()->fetchAll();
    }

    public function getCount($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('cw' => $this->_prefix . 'sitemap_file'),
            new Zend_Db_Expr('COUNT(*)')
        );
        if (isset($params['filters'])) {
            $filterGrid = $params['filters'];
            foreach ($filterGrid as $filter) {
                switch ($filter['data']['type']) {
                    case 'numeric':
                    case 'date':
                        $condition = $filter['data']['comparison'] == 'eq' ? '=' :
                                    ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        $select->where("cw.$filter[field] $condition ?", $filter['data']['value']);
                        break;
                    default:
                        $select->where("cw.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        break;
                }
            }
        }
        return $this->getAdapter()->fetchOne($select);
    }
}