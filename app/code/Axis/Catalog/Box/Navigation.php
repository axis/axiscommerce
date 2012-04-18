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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Box_Navigation extends Axis_Core_Box_Abstract
{
    protected $_title = 'Categories';
    protected $_class = 'box-category';
    protected $_activeCategories = null;

    protected function _construct()
    {
        $this->setData('cache_tags', array('catalog', 'catalog_category'));
    }

    protected function _beforeRender()
    {
        $categories = Axis::single('catalog/category')->select('*')
            ->addName(Axis_Locale::getLanguageId())
            ->addKeyWord()
            ->order('cc.lft')
            ->addSiteFilter(Axis::getSiteId())
            ->addDisabledFilter()
            ->fetchAll();

        if (!is_array($this->_activeCategories)) {
            $this->_activeCategories = array();
            if (Zend_Registry::isRegistered('catalog/current_category')) {
                $this->_activeCategories = array_keys(
                    Zend_Registry::get('catalog/current_category')
                        ->cache()
                        ->getParentItems()
                );
            }
        }

        $container = $_container = new Zend_Navigation();
        $lvl = 0;
        $view = $this->getView();
        foreach ($categories as $_category) {

            $uri = $view->hurl(array(
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
                'visible' => 'enabled' === $_category['status'] ? true : false,
                'active'  => in_array($_category['id'], $this->_activeCategories)
            ));

            $lvl = $lvl - $_category['lvl'] + 1;
            for ($i = 0; $i < $lvl; $i++) {
                $_container = $_container->getParent();
            }

            $lvl = $_category['lvl'];
            $_container->addPage($page);
            $_container = $page;
        }
        $this->setData('menu', $container);
    }

    protected function _getCacheKeyParams()
    {
        $categoryId = null;
        if (Zend_Registry::isRegistered('catalog/current_category')) {
            $category   = Zend_Registry::get('catalog/current_category');
            $categoryId = $category->id;
        }
        return array(
            $categoryId
        );
    }
}
