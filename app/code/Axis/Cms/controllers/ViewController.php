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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_ViewController extends Axis_Cms_Controller_Abstract
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

        $rowContent = $rowPage->getContent();

        $categories = Axis::single('cms/category')
            ->cache()
            ->getParentCategory($pageId, true);

        foreach ($categories as $_category) {
            $label = empty($_category['title']) ? 
                    $_category['link'] : $_category['title'];
            $this->_helper->breadcrumbs(array(
                'label'  => $label,
                'params' => array('cat' => $_category['link']),
                'route'  => 'cms_category'
            ));
        }
        $page = array(
            'id'           => $rowPage->id,
            'content'      => $rowContent->content,
            'is_commented' => $rowPage->comment
        );

        if ($rowPage->comment) {
            // get all comments
            $comments = $rowPage->cache()->getComments();
            foreach ($comments as $comment) {
                $page['comment'][] = $comment;
            }
            // create comment form
            $this->view->formComment =
                Axis::model('cms/form_comment', array('pageId' => $rowPage->id));
        }
        $this->view->page = $page;

        $this->setTitle($rowContent->title);
        $metaTitle = empty($rowContent->meta_title) ?
                $rowContent->title : $rowContent->meta_title;
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_page', $pageId)
            ->setDescription($rowContent->meta_description)
            ->setKeywords($rowContent->meta_keyword);

        $this->_helper->layout->setLayout($rowPage->layout);
        $this->render();
    }

    public function viewCategoryAction()
    {
        $modelCategory = Axis::single('cms/category')->cache();
        $link = $this->_getParam('cat');
        $categoryId = $modelCategory->getCategoryIdByLink($link);
        $currentCategory = $modelCategory->find($categoryId)->current();

        if (!$currentCategory || !$currentCategory->is_active) {
            return $this->_forward('not-found', 'Error', 'Axis_Core');
        }

        $rowContent = $currentCategory->getContent();

        $this->view->category = array();
        $this->view->pageTitle = $rowContent->title;

        $categories = $modelCategory->getParentCategory($categoryId);
        foreach ($categories as $_category) {
            $label = empty($_category['title']) ? 
                    $_category['link'] : $_category['title'];
            $this->_helper->breadcrumbs(array(
                'label'  => $label,
                'params' => array('cat' => $_category['link']),
                'route'  => 'cms_category'
            ));
        }

        $metaTitle = empty($rowContent->meta_title) ?
            $rowContent->title : $rowContent->meta_title;
        $metaDescription = empty($rowContent->meta_description) ?
            $rowContent->description : $rowContent->meta_description;
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_category', $categoryId)
            ->setDescription($metaDescription)
            ->setKeywords($rowContent->meta_keyword);

        $this->view->category = array(
            'description' => $rowContent->description,
            'childs'      => $currentCategory->getChilds(),
            'pages'       => $currentCategory->getPages()
        );
        $this->render();
    }
}