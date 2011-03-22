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
        
        $this->view->categories = Axis::single('catalog/category')->select('*')
            ->addName(Axis_Locale::getLanguageId())
            ->addKeyWord()
            ->order('cc.lft')
            ->addSiteFilter(Axis::getSiteId())
            ->addDisabledFilter()
            ->fetchAll();
        $this->render('categories');
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
        $siteId = Axis::getSiteId();
        $products = Axis::single('catalog/product_category')->select()
            ->distinct()
            ->from('catalog_product_category', array())
            ->joinLeft('catalog_product',
                'cp.id = cpc.product_id',
                array('id'))
            ->addName(Axis_Locale::getLanguageId())
            ->addKeyWord()
            ->addActiveFilter()
            ->addDateAvailableFilter()
            ->addSiteFilter($siteId)
            ->fetchAll();
        foreach ($products as &$product) {
            $product['lvl'] = 1;
        }

        $this->view->products = $products;
        $this->render('products');
    }

    public function getAllPagesAction()
    {
        $this->view->pageTitle = Axis::translate('sitemap')->__(
            'Site Map All Pages'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $result = array();
        $categories = Axis::single('cms/category')->select(array('id', 'parent_id'))
            ->addCategoryContentTable()
            ->columns(array('ccc.link', 'ccc.title'))
            ->addActiveFilter()
            ->addSiteFilter(Axis::getSiteId())
            ->addLanguageIdFilter(Axis_Locale::getLanguageId())
            ->where('ccc.link IS NOT NULL')
            ->fetchAssoc();

        if (Axis::config('sitemap/cms/showPages') && !empty ($categories)) {
            $pages = Axis::single('cms/page')->select(array('id', 'name'))
                ->join(array('cpca' => 'cms_page_category'),
                    'cp.id = cpca.cms_page_id',
                    'cms_category_id')
                ->join('cms_page_content',
                    'cp.id = cpc.cms_page_id',
                    array('link', 'title'))
                ->where('cp.is_active = 1')
                ->where('cpc.language_id = ?', Axis_Locale::getLanguageId())
                ->where('cpca.cms_category_id IN (?)', array_keys($categories))
                ->fetchAssoc();
            
            foreach ($pages as $page) {
                $categories[$page['cms_category_id']]['pages'][$page['id']] = $page;
            }
        }

        $tree = array();
        foreach ($categories as $category) {
            $tree[intval($category['parent_id'])][$category['id']] = $category;
        }
        
        $this->view->treeCount = count($pages);
        $this->view->tree = $tree;
        $this->render('pages');
    }
}