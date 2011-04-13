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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_IndexController extends Axis_Cms_Controller_Abstract
{
    public function indexAction()
    {
        $this->setTitle(Axis::translate('cms')->__('Pages'), null, false);

        $categories = Axis::single('cms/category')->select(array('id', 'parent_id'))
            ->addCategoryContentTable()
            ->columns(array('ccc.link', 'ccc.title'))
            ->addActiveFilter()
            ->addSiteFilter(Axis::getSiteId())
            ->addLanguageIdFilter(Axis_Locale::getLanguageId())
            ->order('cc.parent_id')
            ->where('ccc.link IS NOT NULL')
            ->fetchAssoc();

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
        $this->view->menu = $menu;
        $this->render();
    }
}