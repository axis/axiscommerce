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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Wishlist extends Axis_Db_Table
{
    protected $_name = 'account_wishlist';

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
     * @param array $params
     * @return array
     */
    public function getList($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('cw' => $this->_prefix . 'account_wishlist'));
        if (isset($params['getProductNames'])) {
            $select->joinLeft(
                array('pd' => $this->_prefix . 'catalog_product_description'),
                "pd.product_id = cw.product_id AND pd.language_id = "
                . $params['languageId'], array('product_name' => 'name')
            );
        }
        if (isset($params['getCustomerEmail'])) {
            $select->joinLeft(
                array('c' => $this->_prefix . 'account_customer'),
                'cw.customer_id = c.id',
                array('customer_email' => 'email')
            );
        }
        if (isset($params['customerId'])) {
            $select->where('cw.customer_id = ?', $params['customerId']);
        }
        if (!empty($params['limit'])) {
            $select->limit($params['limit'], $params['start']);
        }
        if (!empty($params['sort'])) {
            $select->order($params['sort'] . ' ' . $params['dir']);
        }

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
                        if (($filter['field'] == 'customer_email')) {
                            if (!isset($params['getCustomerEmail'])) {
                                $select->joinLeft(
                                    array('c' => $this->_prefix . 'account_customer'),
                                    'cw.customer_id = c.id',
                                    array('customer_email' => 'email')
                                );
                            }
                            $select->where("c.email LIKE ?", $filter['data']['value'] . "%");
                        } else if (($filter['field'] == 'product_name')&&(isset($params['getProductNames']))) {
                            $select->where("pd.name LIKE ?", $filter['data']['value'] . "%");
                        } else {
                            $select->where("cw.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        }
                        break;
                }
            }
        }
        return $select->query()->fetchAll();
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
            ->where('cp.id IN (?)', $productIds)
            ->fetchProducts($productIds);

        foreach ($wishlist as &$item) {
            $item['product'] = $products[$item['product_id']];
        }
        return $wishlist;
    }

    /**
     *
     * @param array $params
     * @return int
     */
    public function getCount($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('cw' => $this->_prefix . 'account_wishlist'), new Zend_Db_Expr('COUNT(*)'));
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
                        if ($filter['field'] == 'customer_email') {
                            $select->joinLeft(
                                array('c' => $this->_prefix . 'account_customer'),
                                'cw.customer_id = c.id',
                                array()
                            );
                            $select->where("c.email LIKE ?", $filter['data']['value'] . "%");
                        } elseif ($filter['field'] == 'product_name') {
                            $select->joinLeft(
                                array('pd' => $this->_prefix . 'catalog_product_description'),
                                "pd.product_id = cw.product_id AND pd.language_id = " . $params['languageId'],
                                array()
                            );
                            $select->where("pd.name LIKE ?", $filter['data']['value'] . "%");
                        } else {
                            $select->where("cw.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        }
                    break;
                }
            }
        }
        return $this->getAdapter()->fetchOne($select);
    }
}