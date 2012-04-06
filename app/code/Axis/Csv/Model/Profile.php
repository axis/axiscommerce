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
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Csv_Model_Profile extends Axis_Db_Table
{
    protected $_name = 'csv_profile';

    protected $_dependentTables = array('Axis_Csv_Model_Profile_Filter');

    public function getList()
    {
        return $this->select()->from($this->_name)
            ->joinLeft('csv_profile_filter',
                'cp.id = cpf.profile_id',
                array(
                    'product_name' => 'name',
                    'language_ids',
                    'sku',
                    'stock',
                    'status',
                    'price_from',
                    'price_to',
                    'qty_from',
                    'qty_to'
                )
            )
            ->order(array('cp.updated_at DESC', 'cp.created_at DESC'))
            ->fetchAll();
    }

    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        //before save
        $row->updated_at = Axis_Date::now()->toSQLString();
        if (empty($row->created_at)) {
            $row->created_at = $row->updated_at;
        }
        $row->save();
        return $row;
    }

    public function deleteByIds(array $ids)
    {
        if (!count($ids)) {
            return false;
        }
        $where = $this->getAdapter()->quoteInto('id IN(?)', $ids);

        if ($result = parent::delete($where)) {
           Axis::message()->addSuccess(
               Axis::translate('admin')->__(
                   'Profile was deleted successfully'
                )
           );
        }
        return $result;
    }

    // Csv import-export function
    /**
     *
     * @return Zend_Db_Table_Rowset
     *
     * @param int $minId[required] exclusive bound of product_id
     * @param int $limit[optional] max count of rowset
     * @param array $filters[optional] example:
     *     array(
     *         'status' => 0 - disabled, 1 - enabled, 2 - all,
     *         'stock' => 0 - out of stock, 1 - in stock, 2 - all,
     *         'name' => 'Cellphone',
     *         'sku' => '10002',
     *         'site' => '1,2' site ids, 'all' - all sites
     *     )
     */
    public function getProductSet($minId, $limit = 1, array $filters = null)
    {
        if (!is_numeric($minId)) {
            return array();
        }

        $select = Axis::single('catalog/product')->select()
            ->setIntegrityCheck(false)
            ->distinct()
            ->from('catalog_product')
            ->where('cp.id > ' . $minId)
            ->order('cp.id ASC')
            ->limit($limit);

        if (null !== $filters) {
            if (isset($filters['status']) && $filters['status'] != 2 && is_numeric($filters['status'])) {
                $select->where('cp.is_active = ' . intval($filters['status']));
            }
            if (isset($filters['stock']) && $filters['stock'] != 2 && is_numeric($filters['stock'])) {
                $select->join('catalog_product_stock', 'cp.id = cps.product_id')
                    ->where('cps.in_stock = ?', intval($filters['stock']));
            }
            if (isset($filters['site']) && $filters['site'] != 'all' && trim($filters['site'], ' ,') != '') {
                $site_count = count(Axis::model('core/option_site'));
                $filter_sites = explode(',', trim($filters['site'], ' ,'));
                $filters_site_count = count($filter_sites);
                if ($site_count != $filters_site_count) {
                    $select->join('catalog_product_category', 'cp.id = cpc.product_id')
                        ->join('catalog_category', 'cpc.category_id = cc.id')
                        ->where('cc.site_id IN (?)', $filter_sites);
                }
            }
            if (isset($filters['name']) && $filters['name'] != '') {
                $select->join('catalog_product_description', 'cp.id = cpd.product_id')
                    ->where('cpd.name LIKE ?', $filters['name'].'%');
            }
            if (isset($filters['sku']) && $filters['sku'] != '') {
                $select->where('cp.sku LIKE ?', $filters['sku'].'%');
            }
            /* price filter */
            if (isset($filters['price_from']) && $filters['price_from'] != '' && is_numeric($filters['price_from'])) {
                $select->where('cp.price >= ?', $filters['price_from']);
            }
            if (isset($filters['price_to']) && $filters['price_to'] != '' && is_numeric($filters['price_to'])) {
                $select->where('cp.price <= ?', $filters['price_to']);
            }
            /* qty filter */
            if (isset($filters['qty_from']) && $filters['qty_from'] != '' && is_numeric($filters['qty_from'])) {
                $select->where('cp.quantity >= ?', $filters['qty_from']);
            }
            if (isset($filters['qty_to']) && $filters['qty_to'] != '' && is_numeric($filters['qty_to'])) {
                $select->where('cp.quantity <= ?', $filters['qty_to']);
            }
        }

        return Axis::single('catalog/product')->fetchAll($select);
    }
}