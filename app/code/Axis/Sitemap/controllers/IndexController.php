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
 * @subpackage  Axis_Sitemap_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sitemap_IndexController extends Axis_Core_Controller_Front
{
    public function init()
    {
        parent::init();
        $this->view->crumbs()->add(
            Axis::translate('sitemap')->__(
                'Sitemap'
            ),
            '/sitemap'
        );
        $this->view->meta()->setTitle(
            Axis::translate('sitemap')->__(
                'Sitemap'
        ));
    }

    public function getAllCategoriesAction()
    {
        $this->view->pageTitle = Axis::translate('sitemap')->__(
            'Site Map Categories'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $items = Axis::single('catalog/category')
            ->getFlatTree($this->_langId, $this->_siteId, true);
        $this->view->items = current($items);
        $this->view->items['siteId'] = $this->_siteId;
        $this->render();
    }

    public function getAllProductsAction()
    {
        $this->view->crumbs()->add(
            Axis::translate('sitemap')->__('Products'), '/get-all-products'
        );
        $this->view->pageTitle = Axis::translate('sitemap')->__(
            'Site Map All Products'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $items = array();
        $productRowset = Axis::single('sitemap/file')
            ->getAllActiveProducts($this->_langId, array($this->_siteId));
        foreach ($productRowset as $item) {
            $item['lvl'] = 1 ;
            $items[] = $item;
        }

        $this->view->items = $items;
        $this->view->items['siteId'] = $this->_siteId;
        $this->render();
    }

    public function getAllPagesAction()
    {
        $this->view->pageTitle = Axis::translate('sitemap')->__(
            'Site Map All Pages'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $result = array();
        $categories = Axis::single('cms/category')->getActiveCategory();

        $catsIds = array ();
        foreach ($categories as $category) {
             $catsIds[] = $category['id'];
        }

        $pages = array();
        $countPages = 0;
        if (Axis::config()->sitemap->cms->showPages) {
            $pagesRowset = Axis::single('cms/page')
                ->getPageListByActiveCategory($catsIds, Axis_Locale::getLanguageId());
            foreach ($pagesRowset as $page) {
                $pages[$page['cms_category_id']][] = $page;
                $countPages++;
            }
        }
        foreach ($categories as $category) {
            $result[intval($category['parent_id'])][$category['id']] = array(
                'id'    => $category['id'],
                'title' => $category['title'],
                'link'  => $category['link'],
                'pages' => isset($pages[$category['id']])
                    ? $pages[$category['id']] : null
            );
        }
        $this->view->treeCount = $countPages;
        $this->view->tree = $result;
        $this->render('pages');
    }
}