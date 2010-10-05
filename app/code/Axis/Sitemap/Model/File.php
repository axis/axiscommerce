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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Model
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

    /**
     * Retrieve the list of all available products
     *
     * @param int $languageId
     * @param array $siteIds
     * @return array
     */
    public function getAllActiveProducts($languageId, $siteIds = false)
    {
        $today = Axis_Date::now()->toPhpString('Y-m-d');

        $select = Axis::single('catalog/product_category')->select()
            ->distinct()
            ->from('catalog_product_category', array())
            ->joinLeft('catalog_product',
                'cp.id = cpc.product_id',
                array('id'))
            ->joinLeft('catalog_product_description',
                "cpd.product_id = cp.id AND cpd.language_id = {$languageId}",
                array('name'))
            ->joinLeft('catalog_hurl',
                "ch.key_id = cp.id AND ch.key_type='p'",
                array('key_word'))
            ->where('cp.is_active = 1')
            ->where('cp.date_available IS NULL OR cp.date_available <= ?', $today);

        if ($siteIds) {
            $select->joinLeft(
                'catalog_category',
                'cpc.category_id = cc.id'
                )
                ->where('cc.site_id IN (?)', $siteIds);
        }

        return $select->fetchAll();
    }
}