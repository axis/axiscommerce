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
class Axis_Cms_IndexController extends Axis_Core_Controller_Front
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Pages');
        $this->view->meta()->setTitle($this->view->pageTitle);

        $categories = Axis::single('cms/category')->getActiveCategory();

        $categoriesIds = array ();
        foreach ($categories as $category) {
             $categoriesIds[] = $category['id'];
        }
        $rowset = Axis::single('cms/page')->cache()->getPageListByActiveCategory(
            $categoriesIds, Axis_Locale::getLanguageId()
        )      ;
        $pages = array();
        foreach ($rowset as $page) {
            $pages[$page['cms_category_id']][] = $page;
        }
        $result = array();
        foreach ($categories as $category) {
            $result[intval($category['parent_id'])][$category['id']] = array(
                'id'    => $category['id'],
                'title' => $category['title'],
                'link'  => $category['link'],
                'pages' => isset($pages[$category['id']]) ?
                    $pages[$category['id']] : null
            );
        }
        $this->view->tree = $result;
        $this->render();
    }
}