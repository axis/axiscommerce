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
 * @subpackage  Axis_Community_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_ReviewController extends Axis_Core_Controller_Front
{
    public function init()
    {
        parent::init();

        $this->addBreadcrumb(array(
            'label' => Axis::translate('catalog')->__('Catalog'),
            'route' => 'product_catalog'
        ));
        $this->addBreadcrumb(array(
            'label' => Axis::translate('community')->__('Reviews'),
            'route' => 'community_review'
        ));
    }

    /**
     * Retrieve the list of all reviews ordered by date
     */
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('community')->__(
            'All reviews'
        );
        $this->view->comparable = true;
        $params = $this->_getListingParams();
        $this->view->data = Axis::single('community/review')
            ->getList(null, $params['order'], $params['dir'], $params['limit'], $params['page']);

        $paging = array(
            'perPage' => $this->_getPerPage(),
            'sortBy'  => $this->_getSortBy(),
            'limit'   => null === $params['limit'] ? 'all' : $params['limit'],
            'order'   => $params['order'],
            'dir'     => $params['dir'] == 'asc' ? 'desc' : 'asc',
            'page'    => $params['page'],
            'count'   => $this->view->data['count']
        );
        $this->view->paging = $paging;

        if (count($this->view->data['reviews'])) {
            $productIds = array();
            $keywords = array();
            foreach ($this->view->data['reviews'] as $review) {
                $productIds[] = $review['product_id'];
                $keywords[$review['product']['name']] = $review['product']['name'];
            }

            $title = Axis::translate('community')->__('Customer reviews');
            $this->view->meta()
                ->setTitle($title)
                ->setKeywords(implode(',', $keywords))
                ->setDescription($title);

            $this->view->average_ratings = Axis::single('community/review')
                ->getAverageProductRating(
                    $productIds,
                    Axis::config()->community->review->merge_average
                );
        } else {
            $this->view->meta()->setTitle(
                Axis::translate('community')->__(
                    'Review not found'
            ));
        }
        $this->render('list');
    }

    /**
     * Get the review by id
     */
    public function detailAction()
    {
        $this->view->review = array();

        if (!$this->_hasParam('id') || !is_numeric($this->_getParam('id'))) {
            $this->setTitle(Axis::translate('community')->__(
                'Review not found'
            ));
            $this->render();
            return;
        }

        $where = 'cr.id = ' . (int)$this->_getParam('id');
        $data = Axis::single('community/review')->getList($where);

        if (count($data['reviews'])) {
            reset($data['reviews']);
            $review = current($data['reviews']);
            $author = empty($review['author']) ?
                Axis::translate('community')->__('Guest') : $review['author'];

            $title = Axis::translate('community')->__(
                "%s: Review by %s", $review['product']['name'], $author
            );
            $this->setTitle($title, false);
            $this->view->meta()
                ->setTitle($title)
                ->setKeywords($review['product']['name'] . ',' . $author);
            $this->view->review = $review;
            $this->view->average_ratings = Axis::single('community/review')
                ->getAverageProductRating(
                    $review['product_id'],
                    Axis::config()->community->review->merge_average
                );
        } else {
            $this->setTitle(Axis::translate('community')->__(
                'Review not found'
            ));
        }

        $this->render();
    }

    /**
     * Retrieve the list of all reviews of product
     */
    public function productAction()
    {
        $this->view->data = array(
            'reviews' => array(),
            'count' => 0
        );

        if (!$this->_hasParam('hurl')) {
            $this->setTitle(
                Axis::translate('community')->__(
                    'Review not found'
            ));
            $this->render('list-product');
            return;
        }

        $where = "ch.key_word = '{$this->_getParam('hurl')}'";
        $params = $this->_getListingParams();
        $this->view->data = Axis::single('community/review')->getList(
            $where, $params['order'], $params['dir'], $params['limit'], $params['page']
        );

        $paging = array(
            'perPage' => $this->_getPerPage(),
            'sortBy'  => $this->_getSortBy(),
            'limit'   => null === $params['limit'] ? 'all' : $params['limit'],
            'order'   => $params['order'],
            'dir'     => $params['dir'] == 'asc' ? 'desc' : 'asc',
            'page'    => $params['page'],
            'count'   => $this->view->data['count']
        );
        $this->view->paging = $paging;

        if (count($this->view->data['reviews'])) {
            reset($this->view->data['reviews']);
            $review = current($this->view->data['reviews']);
            $productName = $review['product']['name'];

            $this->view->productId = $review['product_id'];
            $this->view->average_ratings = Axis::single('community/review')
                ->getAverageProductRating(
                    $review['product_id'],
                    Axis::config()->community->review->merge_average
                );
        } else {
            if (!$product = Axis::single('catalog/product')->getByUrl($this->_getParam('hurl'))) {
                return $this->_forward('not-found', 'Error', 'Axis_Core');
            } else {
                $description = $product->getDescription();
                $productName = $description['name'];
                $this->view->productId = $product->id;
            }
        }

        if ($this->view->productId) {
            $form = Axis::model('community/form_review', array(
                'productId' => $this->view->productId
            ));
            $this->view->formReview = $form;
        }

        $title = Axis::translate('community')->__(
            'Reviews for the %s', $productName
        );
        $this->setTitle($title, false, $productName);
        $this->view->meta()
            ->setTitle($title)
            ->setKeywords($productName)
            ->setDescription($title)
        ;

        $this->render('list-product');
    }

    /**
     * Retrieve the list of all reviews written by customer
     */
    public function customerAction()
    {
        $customerId = $this->_getParam('id');
        if (!$customerId
            || !is_numeric($customerId)
            || !$customer = Axis::model('account/customer')->find($customerId)->current()) {

            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $this->view->data = array(
            'reviews' => array(),
            'count' => 0
        );

        $where = 'cr.customer_id = ' . (int)$this->_getParam('id');
        $params = $this->_getListingParams();
        $this->view->data = Axis::single('community/review')->getList(
            $where, $params['order'], $params['dir'], $params['limit'], $params['page']
        );

        $paging = array(
            'perPage' => $this->_getPerPage(),
            'sortBy'  => $this->_getSortBy(),
            'limit'   => null === $params['limit'] ? 'all' : $params['limit'],
            'order'   => $params['order'],
            'dir'     => $params['dir'] == 'asc' ? 'desc' : 'asc',
            'page'    => $params['page'],
            'count'   => $this->view->data['count']
        );
        $this->view->paging = $paging;
        $this->view->comparable = true;

        if (!$nickname = $customer->getExtraField('nickname')) {
            $nickname = $customerId;
        }

        $this->view->customer   = $nickname;
        $this->view->customerId = $customerId;

        $title = Axis::translate('community')->__(
            'Reviews written by customer %s', $nickname
        );
        $this->setTitle($title);

        if (count($this->view->data['reviews'])) {
            $productIds = array();
            $keywords = array();
            foreach ($this->view->data['reviews'] as $review) {
                $productIds[] = $review['product_id'];
                $keywords[$review['product']['name']] = $review['product']['name'];
            }

            $this->view->meta()
                ->setKeywords(implode(',', $keywords))
                ->setDescription($title);

            $mReview = Axis::model('community/review');
            $this->view->average_ratings = $mReview->getAverageProductRating(
                $productIds,
                Axis::config('community/review/merge_average')
            );

            $this->view->average_customer_ratings = $mReview->getAverageCustomerRating(
                $customerId,
                Axis::config('community/review/merge_average')
            );
        }

        $this->render('list-customer');
    }

    public function loginAction()
    {
        $this->_setSnapshot($this->getRequest()->getServer('HTTP_REFERER'));
        $this->_forward('index', 'auth', 'Axis_Account');
    }

    public function addAction()
    {
        $productId = $this->_getParam('product');
        $form = Axis::model('community/form_review', array(
            'productId' => $productId
        ));

        if (!$product = Axis::single('catalog/product')->find($productId)->current()) {
            $productName = Axis::translate('catalog')->__(
                'Product not found'
            );
        } else {
            $description = $product->getDescription();
            $productName = $description['name'];
            $this->view->productId = $product->id;
            $this->view->hurl = $product->getHumanUrl();
        }

        $title = Axis::translate('community')->__(
            'Add review for the %s', $productName
        );
        $this->setTitle($title, false, $productName);
        $this->view->meta()
            ->setTitle($title)
            ->setKeywords($productName)
            ->setDescription();

        if ($this->_request->isPost()) {
            $data = array(
                'product_id' => $this->_getParam('product'),
                'summary'    => $this->_getParam('summary'),
                'author'     => $this->_getParam('author'),
                'title'      => $this->_getParam('title'),
                'pros'       => $this->_getParam('pros'),
                'cons'       => $this->_getParam('cons'),
                'ratings'    => $this->_getRatings()
            );
            if ($form->isValid($this->_request->getPost())) {
                Axis::single('community/review')->save($data);
                Axis::dispatch('community_review_add_success', $data);
                $this->_redirect(
                    $this->getRequest()->getServer('HTTP_REFERER')
                );
            } else {
                $form->populate($data);
            }
        }

        $this->view->formReview = $form;
        $this->render();
    }

    /**
     * Collects all ratings in request.
     *
     * Reason: Zend Framework doesn't allow to create elements
     * with name that contains brackets;
     * @see http://framework.zend.com/issues/browse/ZF-5556 (isArray - is not a solution)
     *
     * @return array (rating_id => rating_values)
     */
    private function _getRatings()
    {
        $result = array();
        foreach ($this->_getAllParams() as $key => $value) {
            if (strpos($key, 'rating_') === 0) {
                $result[str_replace('rating_', '', $key)] = $value;
            }
        }
        return $result;
    }

    /**
     * Retrieve current review listing params
     *  order, dir, limit, page
     *
     * @return array
     */
    private function _getListingParams()
    {
        if ($this->_hasParam('dir')
            && in_array($this->_getParam('dir'), array('asc', 'desc'))) {

            $dir = $this->_getParam('dir');
        } elseif (Axis::session('review')->dir) {
            $dir = Axis::session('review')->dir;
        } else {
            $dir = 'desc';
        }
        Axis::session('review')->dir = $dir;

        if ($this->_hasParam('order')
            && in_array($this->_getParam('order'), array('date', 'rating'))) {

            switch ($this->_getParam('order')) {
                case 'date':
                    $order = 'cr.date_created';
                break;
                case 'rating':
                    $order = 'rating';
                break;
                default:
                    $order = 'cr.date_created';
                break;
            }
        } elseif (Axis::session('review')->order) {
            $order = Axis::session('review')->order;
        } else {
            $order = 'cr.date_created';
        }
        Axis::session('review')->order = $order;

        if ($this->_hasParam('limit')) {
            $limit = $this->_getParam('limit');
            if (!is_numeric($limit) || $limit == 0) {
                $limit = null;
            }
        } elseif (Axis::session('review')->limit) {
            $limit = Axis::session('review')->limit;
        } elseif (is_numeric(Axis::config('community/review/perPageDefault'))) {
            $limit = Axis::config('community/review/perPageDefault');
        } else {
            $limit = 10;
        }
        Axis::session('review')->limit = $limit;

        if ($this->_hasParam('page') && is_numeric($this->_getParam('page'))) {
            $page = $this->_getParam('page');
        } else {
            $page = 1;
        }

        return array(
            'order' => $order,
            'dir'   => $dir,
            'limit' => $limit,
            'page'  => $page
        );
    }

    /**
     * Get available per page listing numbers
     */
    private function _getPerPage()
    {
        $paging = array();
        foreach (explode(',', Axis::config('community/review/perPage')) as $perPage) {
            $paging[$this->view->url(array('limit' => $perPage, 'page' => null))] = $perPage;
        }
        return $paging;
    }

    /**
     * Get available sortby options
     */
    private function _getSortBy()
    {
        $sort = array();
        $sort[$this->view->url(array('order' => 'date'))] =
            Axis::translate('community')->__('Date');
        $sort[$this->view->url(array('order' => 'rating'))] =
            Axis::translate('community')->__('Users rating');
        return $sort;
    }

}