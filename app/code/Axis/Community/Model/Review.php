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
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Review extends Axis_Db_Table
{
    protected $_name = 'community_review';
    protected $_rowClass = 'Axis_Community_Model_Review_Row';
    protected $_selectClass = 'Axis_Community_Model_Review_Select';
    protected $_primary = 'id';
    protected $_dependentTables = array('Axis_Community_Review_Mark');

    /**
     * Retrieve the list of cutomer reviews
     * including product information and prices
     *
     * @param mixed $where
     * @param string $order
     * @param string $dir
     * @param int $limit
     * @param int $page
     * @param bool $all If true - review with any status will be fetched
     * @return array
     */
    public function getList(
                            $where = array(),
                            $order = 'cr.date_created',
                            $dir = 'DESC',
                            $limit = null,
                            $page = null,
                            $all = false)
    {
        $select = $this->select('*')
            ->calcFoundRows()
            ->distinct()
            ->joinLeft('catalog_product',
                'cr.product_id = cp.id',
                array('product_price' => 'price'))
            ->joinLeft('catalog_product_image',
                'cp.image_thumbnail = cpi.id',
                array('product_image_thumbnail' => 'path')
            )
            ->joinLeft('catalog_product_image_title',
                'cpit.image_id = cpi.id AND cpit.language_id = ' . Axis_Locale::getLanguageId(),
                array('product_image_title' => 'title')
            )
            ->joinLeft('catalog_product_description',
                'cp.id = cpd.product_id AND cpd.language_id = ' . Axis_Locale::getLanguageId(),
                array('product_name' => 'name',
                    'product_image_seo_name' => 'image_seo_name')
            )
            ->joinLeft('catalog_hurl',
                "cp.id = ch.key_id AND ch.key_type = 'p'",
                array('product_key_word' => 'key_word'))
            ->order($order . ' ' . $dir);

        if ($order == 'rating') {
            $select->addRating();
        }

        if (!$all) {
            $select->where("cp.is_active = 1 AND cr.status = 'approved'");
        }

        if (null !== $where) {
            if (!is_array($where)) {
                $where = array($where);
            }
            foreach ($where as $statement) {
                $select->where($statement);
            }
        }

        if (null !== $page && null !== $limit) {
            $select->limitPage($page, $limit);
        } elseif (null !== $limit) {
            $select->limit($limit);
        }

        $reviews = $select->fetchAssoc();
        $count = $select->count();

        $products = array();
        foreach ($reviews as $review) {
            $products[$review['product_id']] = array(
                'price' => $review['product_price']
            );
        }
        Axis::single('discount/discount')->fillDiscount($products);

        foreach ($reviews as &$review) {
            if (!isset($products[$review['product_id']]) ||
                !isset($products[$review['product_id']]['price_discount'])) {

                continue;
            }
            $review['product_price_discount'] =
                $products[$review['product_id']]['price_discount'];
        }

        $ratings = $this->loadRating(array_keys($reviews));
        foreach ($reviews as $key => &$review) {
            $review['ratings'] = $ratings[$key];
        }

        return array(
            'reviews' => array_values($reviews),
            'count' => $count
        );
    }

    /**
     * Retrieve the list of cutomer reviews
     * including product information and prices
     *
     * @param mixed $where
     * @param string $order
     * @param string $dir
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getBackList(
                                $where = array(),
                                $order = 'cr.date_created',
                                $dir = 'DESC',
                                $limit = null,
                                $page = null)
    {
        $select = $this->select('*')
            ->calcFoundRows()
            ->distinct()
            ->joinLeft(
                'catalog_product_description',
                'cr.product_id = cpd.product_id AND cpd.language_id = ' . Axis_Locale::getLanguageId(),
                array('product_name' => 'name'))
            ->order($order . ' ' . $dir);

        if (null !== $where) {
            if (!is_array($where)) {
                $where = array($where);
            }
            foreach ($where as $statement) {
                $select->where($statement);
            }
        }

        if (null !== $page && null !== $limit) {
            $select->limitPage($page, $limit);
        } elseif (null !== $limit) {
            $select->limit($limit);
        }

        $reviews = $select->fetchAssoc();
        $count = $select->count();

        return array(
            'reviews' => array_values($reviews),
            'count'   => $count
        );
    }



    /**
     * Retrieve the list of cutomer reviews, excluding product information
     *
     * @param mixed $where
     * @param string $order
     * @param string $dir
     * @param int $limit
     * @param int $page
     * @param bool $all If true - review with any status will be fetched
     * @return array
     */
    public function getTinyList($where = array(),
                                $order = 'cr.date_created',
                                $dir = 'DESC',
                                $limit = null,
                                $page = null,
                                $all = false)
    {
        $select = $this->select('*')
            ->calcFoundRows()
            ->distinct()
            ->order($order . ' ' . $dir);

        if ($order == 'rating') {
            $select->addRating();
        }

        if (!$all) {
            $select->where("cr.status = 'approved'");
        }

        if (null !== $where) {
            if (!is_array($where)) {
                $where = array($where);
            }
            foreach ($where as $statement) {
                $select->where($statement);
            }
        }

        if (null !== $page && null !== $limit) {
            $select->limitPage($page, $limit);
        } elseif (null !== $limit) {
            $select->limit($limit);
        }

        $reviews = $select->fetchAssoc();
        $count   = $select->count();
        $ratings = $this->loadRating(array_keys($reviews));

        foreach ($reviews as $key => &$review) {
            $review['ratings'] = $ratings[$key];
        }

        return array(
            'reviews' => array_values($reviews),
            'count'   => $count
        );
    }

    /**
     * Load available rating marks to appropriate reviews
     *
     * @param mixed $reviewIds
     * @return array
     */
    public function loadRating($reviewIds)
    {
        if (!is_array($reviewIds)) {
            $reviewIds = array($reviewIds);
        }

        if (!count($reviewIds)) {
            return array();
        }

        $select = Axis::single('community/review_mark')
            ->select(array('review_id', 'mark'))
            ->joinRight( 'community_review_rating',
                'crm.rating_id = crr.id',
                'name')
            ->joinLeft('community_review_rating_title',
                'crm.rating_id = crrt.rating_id AND crrt.language_id = ' . Axis_Locale::getLanguageId(),
                'title')
            ->where('crm.review_id IN (?)', $reviewIds);

        $result = array();
        $ratings = array();

        foreach ($select->fetchAll() as $rating) {
            $ratings[$rating['review_id']][$rating['name']] = $rating;
        }

        $availableRatings = Axis::single('community/review_rating')->getList();

        foreach ($reviewIds as $id) {
            foreach ($availableRatings as $rating) {
                $result[$id][$rating['name']] = isset($ratings[$id][$rating['name']]) ?
                    $ratings[$id][$rating['name']] : array();
            }
        }

        return $result;
    }

    /**
     * Load average ratings by product
     *
     * @param mixed $productIds
     * @param bool $mergeRatings
     * @return array (product_id => ratings)
     */
    public function getAverageProductRating($productIds, $mergeRatings = false)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = Axis::single('community/review_mark')
            ->select(array('average_mark' => new Zend_Db_Expr('AVG(crm.mark)')))
            ->joinLeft('community_review',
                'cr.id = crm.review_id',
                array('product_id' => 'cr.product_id'))
            ->where('cr.product_id IN (?)', $productIds)
            ->group('cr.product_id');

        if (!$mergeRatings) {
            $select->joinLeft('community_review_rating',
                    'crm.rating_id = crr.id',
                    'name')
                ->joinLeft('community_review_rating_title',
                    'crm.rating_id = crrt.rating_id AND crrt.language_id = ' . Axis_Locale::getLanguageId(),
                    'title')
                ->group('crr.name')
                ->order('crrt.title DESC');
        }

        $result = array();
        foreach ($select->fetchAll() as $averageMark) {
            $name = isset($averageMark['name']) ?
                $averageMark['name'] : 'rating';
            $result[$averageMark['product_id']][$name] = array(
                'title'      => isset($averageMark['title']) ?
                    $averageMark['title'] : Axis::translate('community')->__('Rating'),
                'mark'       => round($averageMark['average_mark'], 1),
                'product_id' => $averageMark['product_id']
            );
        }

        return $result;
    }

    /**
     * Load average rating by customer
     *
     * @param mixed $customerIds
     * @param bool $mergeRatings
     * @return array (customer_id => ratings)
     */
    public function getAverageCustomerRating($customerIds, $mergeRatings = false)
    {
        if (!is_array($customerIds)) {
            $customerIds = array($customerIds);
        }

        $select = Axis::single('community/review_mark')->select(
                array('average_mark' => new Zend_Db_Expr('AVG(crm.mark)'))
            )
            ->joinLeft('community_review',
                'cr.id = crm.review_id',
                array('customer_id' => 'cr.customer_id'))
            ->where('cr.customer_id IN (?)', $customerIds)
            ->group('cr.customer_id');

        if (!$mergeRatings) {
            $select->joinLeft('community_review_rating',
                    'crm.rating_id = crr.id',
                    'name')
                ->joinLeft('community_review_rating_title',
                    'crm.rating_id = crrt.rating_id AND crrt.language_id = ' . Axis_Locale::getLanguageId(),
                    'title')
                ->group('crr.name')
                ->order('crrt.title DESC');
        }

        $result = array();
        foreach ($select->fetchAll() as $averageMark) {
            $name = isset($averageMark['name']) ? $averageMark['name'] : 'rating';
            $result[$averageMark['customer_id']][$name] = array(
                'title'       => isset($averageMark['title']) ?
                    $averageMark['title'] : Axis::translate('community')->__('Rating'),
                'mark'        => round($averageMark['average_mark'], 1),
                'customer_id' => $averageMark['customer_id']
            );
        }

        return $result;
    }

    /**
     * Add or update customers review
     *
     * @param array $data
     *  (
     *    product_id => int,    required
     *    title => string,      required
     *    pros => string,       required
     *    cons => string,       required
     *    summary => string,    optional
     *    id => int             optional,
     *    status => (pending|approved|disapproved) optional
     *    ratings => array(     optional
     *      [1] => double,      rating_id => rating_mark pair
     *      ...
     *    )
     *  )
     * @return bool
     */
    public function save($data)
    {
        if (!isset($data['customer_id'])) {
            $data['customer_id'] = Axis::getCustomerId() ?
                Axis::getCustomerId() : new Zend_Db_Expr('NULL');
        } elseif (!is_numeric($data['customer_id'])
                || !Axis::single('account/customer')->find($data['customer_id'])->current()) {

            $data['customer_id'] = new Zend_Db_Expr('NULL');
        }
        if (!isset($data['status'])) {
            $data['status'] = $this->getDefaultStatus();
            if ($data['status'] != 'approved') {
                Axis::message()->addSuccess(
                    Axis::translate('community')->__(
                        'Review has been accepted for moderation'
                    )
                );
            }
        }

        if (!isset($data['id'])
            || empty($data['id'])
            || !$row = $this->find($data['id'])->current()) {

            $row = $this->createRow();
            $row->date_created = Axis_Date::now()->toSQLString();
        }
        unset($data['id']);
        $row->setFromArray($data);
        $row->save();

        Axis::message()->addSuccess(
            Axis::translate('community')->__(
                'Review was successfully saved'
        ));

        $data['ratings'] = array_filter($data['ratings']);

        if (!is_numeric($data['customer_id']) && count($data['ratings'])) {
            Axis::message()->addNotice(
                Axis::translate('community')->__(
                    'Guests do not have the permission to vote. Review was saved without ratings'
            ));
        } elseif (count($data['ratings']) &&
            Axis::single('community/review_mark')->isCustomerVoted(
                $data['customer_id'], $data['product_id'], $row->id
            )) {

            Axis::message()->addNotice(
                Axis::translate('community')->__(
                    'You have already voted for this product. Review was saved without ratings'
            ));
        } elseif (count($data['ratings'])) {
            $row->saveMark($data['ratings']);
        }

        return true;
    }

    /**
     * Retrieve the default review status
     * @return string
     */
    public function getDefaultStatus()
    {
        if (Axis::getCustomerId()) {
            return Axis::config()->community->review->customer_status;
        }
        return Axis::config()->community->review->guest_status;
    }

    /**
     * Returns can or not current user write a review
     * @return bool
     */
    public function canAdd()
    {
        if (!Axis::config()->community->review->guest_permission
            && !Axis::getCustomerId()) {

            return false;
        }
        return true;
    }

    /**
     * Remove reviews by ids
     *
     * @param mixed $ids
     * @return void
     */
    public function remove($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $where = $this->getAdapter()->quoteInto('id IN (?)', $ids);
        $this->delete($where);
        Axis::message()->addSuccess(
            Axis::translate('community')->__(
                "%d review(s) was deleted successfully", count($ids)
        ));
    }

    /**
     * Retrieve reviews count (approved reviews) for specified product(s)
     *
     * @param mixed $productIds
     * @return array (productId => count,...)
     */
    public function getCountByProductId($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $counts = $this->select(array('product_id', new Zend_Db_Expr("COUNT('id')")))
            ->where('product_id IN (?)', $productIds)
            ->where('status = ?', 'approved')
            ->group('product_id')
            ->fetchPairs();

        $result = array();
        foreach ($productIds as $productId) {
            $result[$productId] = isset($counts[$productId]) ? $counts[$productId] : 0;
        }
        return $result;
    }
}