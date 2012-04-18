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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_CommentController extends Axis_Cms_Controller_Abstract
{
    protected function _addCommentForm($pageId)
    {
        $form = Axis::model('cms/form_comment', array('pageId' => $pageId));
        if ($this->_request->isPost()) {
            $data = $this->_getAllParams();
            if ($form->isValid($data)) {
                Axis::single('cms/page_comment')->insert(array(
                    'author'      => $data['author'],
                    'email'       => $data['email'],
                    'status'      => 0,
                    'content'     => $data['content'],
                    'created_on'  => Axis_Date::now()->toSQLString(),
                    'cms_page_id' => $pageId
                ));
                Axis::dispatch('cms_comment_add_success', $data);
                Axis::message()->addSuccess(
                    Axis::translate('cms')->__(
                        'Comment successfully added'
                ));
                $this->_redirect(
                    $this->_getBackUrl()
                );
            } else {
                $form->populate($data);
            }
        }
        $this->view->formComment = $form;
    }

    public function addAction()
    {
        $pageId = $this->_getParam('page');
        $this->view->page = array();
        
        $currentPage = Axis::single('cms/page')
            ->cache()
            ->find($pageId)
            ->current();

        if (!$currentPage) {
            $this->setTitle(Axis::translate('cms')->__('Page not found'));
            $this->render();
            return;
        }

        $categories = Axis::single('cms/category')->getParentCategory($pageId, true);
        foreach ($categories as $_category) {
            $label = empty($_category['title']) ? 
                    $_category['link'] : $_category['title'];
            $this->_helper->breadcrumbs(array(
                'label'  => $label,
                'params' => array('cat' => $_category['link']),
                'route'  => 'cms_category'
            ));
        }
        $rowContent = $currentPage->cache()->getContent();
        $this->setTitle($rowContent->title);

        $page = array(
            'content'      => $rowContent->content,
            'is_commented' => (bool) $currentPage->comment
        );

        if ($currentPage->comment) {
            // get all comments
            $comments = $currentPage->cache()->getComments();
            foreach ($comments as $comment) {
                $page['comment'][] = $comment;
            }
            $this->_addCommentForm($pageId);
        }
        
        $this->view->page = $page;
        $metaTitle = empty($rowContent->meta_title) ?
                $rowContent->title : $rowContent->meta_title;
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_page', $pageId)
            ->setDescription($rowContent->meta_description)
            ->setKeywords($rowContent->meta_keyword);

        $this->_helper->layout->setLayout($currentPage->layout);
        $this->render();
    }
}