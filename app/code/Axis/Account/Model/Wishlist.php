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
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Wishlist extends Axis_Db_Table
{
    protected $_name = 'account_wishlist';

    protected $_selectClass = 'Axis_Account_Model_Wishlist_Select';

    /**
     *
     * @param array $comments
     * @return bool
     */
    public function updateComments($comments)
    {
        $rowset = $this->find(array_keys($comments));
        foreach ($rowset as $row) {
            if ($row->customer_id != Axis::getCustomerId()) {
                continue;
            }
            $row->wish_comment = $comments[$row->id];
            $row->save();
        }
        return true;
    }

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        if (empty($data['created_on'])) {
            $data['created_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::insert($data);
    }

    /**
     *
     * @param int $customerId
     * @return array
     */
    public function findByCustomerId($customerId)
    {
        $wishlist = $this->fetchAll(array(
            $this->getAdapter()->quoteInto('customer_id = ?', $customerId)
        ))->toArray();

        if (!count($wishlist)) {
            return array();
        }

        $productIds = array();
        foreach ($wishlist as $item) {
            $productIds[] = $item['product_id'];
        }

        $products = Axis::single('catalog/product')->select('*')
            ->addCommonFields()
            ->addFinalPrice()
            ->where('cp.id IN (?)', $productIds)
            ->fetchProducts($productIds);

        if (!count($products)) {
            return array();
        }

        foreach ($wishlist as &$item) {
            if (isset($products[$item['product_id']])) {
                $item['product'] = $products[$item['product_id']];
            }
        }
        return $wishlist;
    }
}