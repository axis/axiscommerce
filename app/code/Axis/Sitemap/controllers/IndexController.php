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
 * @copyright   Copyright 2008-2011 Axis
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
        $this->_helper->breadcrumbs(array(
            'label' => Axis::translate('sitemap')->__('Sitemap'),
            'route' => 'sitemap'
        ));
    }

    public function getAllCategoriesAction()
    {
        $this->setTitle(Axis::translate('sitemap')->__(
            'Site Map Categories'
        ));
        
        $categories = Axis::single('catalog/category')->select('*')
            ->addName(Axis_Locale::getLanguageId())
            ->addKeyWord()
            ->order('cc.lft')
            ->addSiteFilter(Axis::getSiteId())
            ->addDisabledFilter()
            ->fetchAll();

        $menu = $_container = new Zend_Navigation();
        $lvl = 0;
        foreach ($categories as $_category) {

            $uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_category['id'],
                    'seo'   => $_category['key_word']
                ),
                'controller' => 'catalog',
                'action'     => 'view'
            ), false, true);

            $class = 'nav-' . str_replace('.', '-', $_category['key_word']);

            $page = new Zend_Navigation_Page_Uri(array(
                'label'   => $_category['name'],
                'title'   => $_category['name'],
                'uri'     => $uri,
                'order'   => $_category['lft'],
                'class'   => $class,
                'visible' => ('enabled' === $_category['status']) ? true : false
            ));

            $lvl = $lvl - $_category['lvl'] + 1;
            for ($i = 0; $i < $lvl; $i++) {
                $_container = $_container->getParent();
            }

            $lvl = $_category['lvl'];
            $_container->addPage($page);
            $_container = $page;
        }

        $this->view->menu = $menu;
        $this->render('categories');
    }

    public function getAllProductsAction()
    {
        $this->setTitle(
            Axis::translate('sitemap')->__(
                'Site Map All Products'
        ));
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
            ->addSiteFilter(Axis::getSiteId())
            ->order('cpd.name')
            ->fetchAll();

        $menu = new Zend_Navigation();
        foreach ($products as $_product) {
            $uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_product['id'],
                    'seo'   => $_product['key_word']
                ),
                'controller' => 'catalog',
                'action'     => 'view'
            ), false, true);

            $class = 'nav-' . str_replace('.', '-', $_product['key_word']);

            $page = new Zend_Navigation_Page_Uri(array(
                'label'   => $_product['name'],
                'title'   => $_product['name'],
                'uri'     => $uri,
                'class'   => $class
            ));

            $menu->addPage($page);
        }
        $this->view->menu = $menu;
        $this->render('products');
    }

    public function getAllPagesAction()
    {
        $this->setTitle(Axis::translate('sitemap')->__('Site Map All Pages'));
        
        $result = array();
        $categories = Axis::single('cms/category')->select(array('id', 'parent_id'))
            ->addCategoryContentTable()
            ->columns(array('ccc.link', 'ccc.title'))
            ->addActiveFilter()
            ->addSiteFilter(Axis::getSiteId())
            ->addLanguageIdFilter(Axis_Locale::getLanguageId())
            ->order('cc.parent_id')
            ->where('ccc.link IS NOT NULL')
            ->fetchAssoc();

        $menu = new Zend_Navigation();
        
        foreach ($categories as $_category) {

            $title = empty($_category['title']) ?
                $_category['link'] : $_category['title'];

            $page = new Zend_Navigation_Page_Mvc(array(
                'category_id' => $_category['id'],
                'label'       => $title,
                'title'       => $title,
                'route'       => 'cms_category',
                'params'      => array('cat' => $_category['link']),
                'class'       => 'icon-folder'
            ));
            $_container = $menu->findBy('category_id', $_category['parent_id']);
            if (null === $_container) {
                $_container = $menu;
            }
            $_container->addPage($page);
        }

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
                ->order('cpca.cms_category_id')
                ->fetchAssoc();

            foreach($pages as $_page) {
                $title = empty($_page['title']) ? $_page['link'] : $_page['title'];

                $page = new Zend_Navigation_Page_Mvc(array(
                    'label'  => $title,
                    'title'  => $title,
                    'route'  => 'cms_page',
                    'params' => array('page' => $_page['link']),
                    'class'  => 'icon-page'
                ));
                $_container = $menu->findBy('category_id', $_page['cms_category_id']);
                $_container->addPage($page);
            }
        }
        $this->view->menu = $menu;
        $this->render('pages');
    }
}