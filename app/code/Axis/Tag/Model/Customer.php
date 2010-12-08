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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Model_Customer extends Axis_Db_Table
{
    protected $_name = 'tag_customer';

    protected $_dependentTables = array('Axis_Tag_Model_Product');

    protected $_selectClass = 'Axis_Tag_Model_Customer_Select';

    const STATUS_APPROVED    = 1;
    const STATUS_PENDING     = 2;
    const STATUS_DISAPPROVED = 3;

    /**
     * Returns all rows with some `tag`
     * @param $tag
     * @return mixed|array
     */
    public function findByTag($tag)
    {
        return $this->select()
            ->where('name = ?', $tag)
            ->where('status = ?', self::STATUS_APPROVED)
            ->where('site_id = ?', Axis::getSiteId())
            ->fetchAll();
    }

    /**
     * Return customer tags with count of products for each
     *
     * @param int $customerId
     * @param bool $getProductNames
     * @param int $languageId
     * @return array
     */
    public function getMyWithWeight(
        $customerId = null, $getProductNames = false, $languageId = null)
    {
        if (null === $customerId) {
            $customerId = Axis::getCustomerId();
        }
        if (!$customerId) {
            return false;
        }
        $select = $this->select(array('*', new Zend_Db_Expr('COUNT(*) AS weight')))
            ->joinLeft('tag_product',
                'tp.customer_tag_id = tc.id', 'product_id')
            ->where('tc.customer_id = ?', $customerId)
            ->where('tc.site_id = ?', Axis::getSiteId())
            ->where('tc.status = ?', self::STATUS_APPROVED)
            ->group('tc.id');

        if ($getProductNames) {
            if (null === $languageId) {
                $languageId = Axis_Locale::getLanguageId();
            }

            $select->joinLeft(
                'catalog_product_description',
                'pd.product_id = tp.product_id AND pd.language_id = ' . $languageId,
                array('product_name' => 'name')
            );
        }

        return $select->query()->fetchAll();
    }

    public function findProductsByTagId($tagId)
    {
        $row = $this->find($tagId)->current();
        if (!$row instanceof Axis_Db_Table_Row) {
            return array();
        }
        return $row->findDependentRowset('Axis_Tag_Model_Product');
    }

    public function getByProductId($productId)
    {
        $select = Axis::model('tag/product')->select(
                array ('*', new Zend_Db_Expr('COUNT(*) weight'))
            )
            ->joinLeft(
                'tag_customer',
                'tp.customer_tag_id = tc.id',
                '*'
            )
            ->group('tc.id')
            ->where('status = ?', self::STATUS_APPROVED);

        $weights = array();
        foreach ($select->query()->fetchAll() as $weight) {
            $weights[$weight['name']] = $weight['weight'];
        }
        $select->where('tp.product_id = ? ', $productId);

        $tags = array();
        foreach ($select->query()->fetchAll() as $tag) {
            $tags[$tag['name']] = $tag;
            $tags[$tag['name']]['weight'] = $weights[$tag['name']];
        }
        return $tags;
    }

    /**
     *
     * @param int $limit
     * @return array()
     */
    public function getAllWithWeight($limit = null)
    {

        $tags = $this->select(
                array('name', '*', new Zend_Db_Expr('COUNT(*) AS weight'))
            )
            ->joinLeft(
                'tag_product',
                'tp.customer_tag_id = tc.id',
                'product_id')
            ->where('site_id = ?', Axis::getSiteId())
            ->where('status = ?', self::STATUS_APPROVED)
            ->group('tc.name')
            //->order('RAND()');
            ->limit($limit)
            ->order('weight DESC')
            ->fetchAssoc();

        ksort($tags);
        return $tags;
    }

   /**
    * Return list tag on backend
    * @param array $params
    * @return mixed|array
    */
    public function getList($params = array())
    {
        $select = $this->select(
                array('id', 'tag' => 'name', 'status')
            )
            ->joinLeft(
                'account_customer',
                'tc.customer_id = ac.id',
                array('customer_email' => 'email', 'customer_id' => 'id')
            )
            ->joinLeft(
                'tag_product',
                'tp.customer_tag_id = tc.id',
                'product_id'
            )
            ->joinLeft(
                'catalog_product_description',
                'cpd.product_id = tp.product_id AND cpd.language_id = :languageId',
                array('product_name' => 'name')
            )
            ->bind(array('languageId' => $params['languageId']))
            ;

        if (!empty($params['limit'])) {
            $select->limit($params['limit'], $params['start']);
        }
        if (!empty($params['sort'])) {
            $select->order($params['sort'] . ' ' . $params['dir']);
        }
        if (isset($params['filters'])) {
            $this->_setFilter($select, $params['filters']);
        }

        return $select->fetchAll();
    }

    /**
    * Return count tag on backend
    * @param array $params
    * @return int
    */
    public function getCount($params = array())
    {
        $select = $this->select(new Zend_Db_Expr('COUNT(*)'))
            ->joinLeft(
                'account_customer',
                'tc.customer_id = ac.id'
            )
            ->joinLeft(
                'tag_product',
                'tp.customer_tag_id = tc.id'
            )
            ->joinLeft(
                'catalog_product_description',
                'cpd.product_id = tp.product_id AND cpd.language_id = :languageId'
            )
            ->bind(array('languageId' => $params['languageId']));

        if (isset($params['filters'])) {
            $this->_setFilter($select, $params['filters']);
        }
        return $this->getAdapter()->fetchOne($select);
    }

    /**
    * @param (Zend_Db_Select|Zend_Db_Table_Select) $select
    * @param array $filters
    * @return mixed (Zend_Db_Select|Zend_Db_Table_Select)
    */
    private function _setFilter(&$select, array $filters)
    {
        foreach ($filters as $filter) {
            switch ($filter['data']['type']) {
                case 'numeric': case 'date':
                    $condition = $filter['data']['comparison'] == 'eq' ? '=' :
                                     ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                    if ($filter['field'] == 'product_id') {
                        $select->where("tp.product_id $condition ?", $filter['data']['value']);
                    } else {
                        $select->where("tc.$filter[field] $condition ?", $filter['data']['value']);
                    }
                    break;
                default:
                    if (($filter['field'] == 'customer_email')) {
                        $select->where("c.email LIKE ?", $filter['data']['value'] . "%");
                    } else if ($filter['field'] == 'product_name') {
                        $select->where("cpd.name LIKE ?", $filter['data']['value'] . "%");
                    } else if ($filter['field'] == 'tag') {
                        $select->where("tc.name LIKE ?", $filter['data']['value'] . "%");
                    } else {
                        $select->where("tc.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                    }
                    break;
            }
        }
        return $select;
    }

    /**
    * Returns array of tags status
    *
    * @static
    * @return const array
    */
    public static function getStatuses()
    {
        return array(
            self::STATUS_APPROVED    => Axis::translate('core')->__('Approved'),
            self::STATUS_PENDING     => Axis::translate('core')->__('Pending'),
            self::STATUS_DISAPPROVED => Axis::translate('core')->__('Disapproved')
        );
    }

    public function getRowByTag($tag)
    {
        return $this->fetchRow(
            $this->select()
                ->where('name = ?', $tag)
                ->where('customer_id = ?', Axis::getCustomerId())
                ->where('site_id = ?', Axis::getSiteId())
            )
            ;
    }

    /**
    * Links recieved tags to product.
    * Split tags by spaces and link each to productId
    *
    * @param string $tags
    * @param int $productId
    * @return bool
    */
    public function save($tags, $productId)
    {
        $tags = explode(',', $tags);

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) {
                continue;
            }
            $tagCustomerRow = $this->getRowByTag($tag);

            if ($tagCustomerRow instanceof Axis_Db_Table_Row) {

                $rowsWithProductId = $tagCustomerRow
                    ->findDependentRowset(Axis::single('tag/product'));
                $exists = false;
                foreach ($rowsWithProductId as $row) {
                    if ($row->product_id == $productId) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    Axis::single('tag/product')->insert(array(
                        'customer_tag_id' => $tagCustomerRow->id,
                        'product_id'      => $productId
                    ));
                    Axis::dispatch('tag_product_add_success', array(
                        'tag'        => $tag,
                        'product_id' => $productId
                    ));
                    Axis::message()->addSuccess(
                        Axis::translate('tag')->__(
                            "Tag '%s' was successfully added to product", $tag
                        )
                    );
                } else {
                    Axis::message()->addError(
                        Axis::translate('tag')->__(
                            "Your tag '%s' is already added to this product", $tag
                        )
                    );
                }
            } else {
                $tagId = $this->insert(array(
                    'customer_id' => Axis::getCustomerId(),
                    'site_id'     => Axis::getSiteId(),
                    'name'        => $tag,
                    'status'      => $this->getDefaultStatus()
                ));
                Axis::single('tag/product')->insert(array(
                    'customer_tag_id' => $tagId,
                    'product_id'      => $productId
                ));
                Axis::dispatch('tag_product_add_success', array(
                    'tag'        => $tag,
                    'product_id' => $productId
                ));
                Axis::message()->addSuccess(
                    Axis::translate('tag')->__(
                        "Tag '%s' was successfully added to product", $tag
                    )
                );
            }
        }
        return true;
    }

    /**
     * Retrieve the default tag status
     * @return int
     */
    public function getDefaultStatus()
    {
        if (null === Axis::getCustomerId()) {
            return Axis::config('tag/main/guest_status');
        }
        return Axis::config('tag/main/customer_status');

    }
}