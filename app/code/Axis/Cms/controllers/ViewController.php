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
class Axis_Cms_ViewController extends Axis_Core_Controller_Front
{
    public function viewPageAction()
    {
        $link = $this->_getParam('page');
        $pageId = Axis::single('cms/page')->getPageIdByLink($link);

        $rowPage = Axis::single('cms/page')
            ->find($pageId)
            ->current();

        if (!$rowPage || !$rowPage->is_active) {
            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $content = $rowPage->getContent();

        $this->view->page = array();
        $this->view->pageTitle = $content['title'];
        $this->view->crumbs()->add(
            Axis::translate('cms')->__('Pages'),
            '/pages'
        );

        $categories = Axis::single('cms/category')
            ->cache()
            ->getParentCategory($pageId, true);

        foreach ($categories as $category) {
           $this->view->crumbs()->add(
               empty($category['title']) ? $category['link'] : $category['title'],
               $this->view->href('/cat/' . $category['link'])
           );
        }
        $this->view->page['id'] = $rowPage->id;
        $this->view->page['content'] = $content['content'];
        $this->view->page['is_commented'] = $rowPage->comment;

        if ($rowPage->comment) {
            // get all comments
            $comments = $rowPage->cache()->getComments();
            $i = 0;
            foreach ($comments as $comment) {
                $this->view->page['comment'][$i]['author']      = $comment['author'];
                $this->view->page['comment'][$i]['content']     = $comment['content'];
                $this->view->page['comment'][$i]['created_on']  = $comment['created_on'];
                $this->view->page['comment'][$i]['modified_on'] = $comment['modified_on'];
                $this->view->page['comment'][$i]['status']      = $comment['status'];
                $i++;
            }

            // create comment form
            $this->view->formComment =
                Axis::model('cms/form_comment', array('pageId' => $rowPage->id));
        }

        $metaTitle = $content['meta_title'] == '' ?
                $content['title'] : $content['meta_title'];
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_page', $pageId)
            ->setDescription($content['meta_description'])
            ->setKeywords($content['meta_keyword']);

        $layout = substr($rowPage->layout, strpos($rowPage->layout, '_'));
        $this->_helper->layout->setLayout('layout' . $layout);
        $this->render();
    }

    public function viewCategoryAction()
    {
        $modelCategory = Axis::single('cms/category')->cache();
        $categoryId = $modelCategory->getCategoryIdByLink($this->_getParam('cat'));
        $currentCategory = $modelCategory->find($categoryId)->current();

        if (!$currentCategory || !$currentCategory->is_active) {
            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $content = $currentCategory->getContent();

        $this->view->category = array();
        $this->view->pageTitle = $content['title'];
        $this->view->crumbs()->add(
            Axis::translate('cms')->__('Pages'),
            '/pages'
        );

        $categories = $modelCategory->getParentCategory($categoryId);

        array_pop($categories);
        foreach ($categories as $category) {
           $this->view->crumbs()->add(
               empty($category['title']) ? $category['link'] : $category['title'],
               $this->view->href('/cat/' . $category['link'])
           );
        }

        $metaTitle = empty($content['meta_title']) ?
            $content['title'] : $content['meta_title'];
        $metaDescription = empty($content['meta_description']) ?
            $content['description'] : $content['meta_description'];
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_category', $categoryId)
            ->setDescription($metaDescription)
            ->setKeywords($content['meta_keyword']);

        $this->view->category['description'] = $content['description'];
        $this->view->category['childs']      = $currentCategory->getChilds();
        $this->view->category['pages']       = $currentCategory->getPages();

        $this->render();
    }
}