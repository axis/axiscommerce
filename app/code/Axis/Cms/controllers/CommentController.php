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
class Axis_Cms_CommentController extends Axis_Core_Controller_Front
{
    public function addAction()
    {
        $pageId = $this->_getParam('page');
        $this->view->page = array();
        $this->view->crumbs()->add(
            Axis::translate('cms')->__('Pages'), '/pages'
        );
        
        $currentPage = Axis::single('cms/page')
            ->cache()
            ->find($pageId)
            ->current();
        
        if (!$currentPage) {
            $this->view->pageTitle = Axis::translate('cms')->__(
                'Page not found'
            );
            $this->view->meta()->setTitle($this->view->pageTitle);
            $this->render();
            return;
        }
        
        $content = $currentPage->cache()->getContent();
        
        $this->view->page = array();
        $this->view->pageTitle = Axis::translate('cms')->__($content['title']);
        $categories = Axis::single('cms/category')
            ->getParentCategory($pageId, true);
        foreach ($categories as $category) {
           $this->view->crumbs()->add(
               empty($category['title']) ?
                   $category['link'] : $category['title'],
               $this->view->href('/cat/' . $category['link'])
           ); 
        }
        
        $this->view->page['content'] = $content['content'];
        $this->view->page['is_commented'] = $currentPage->comment;
        
        if ($currentPage->comment) {
            // get all comments
            $comments = $currentPage->cache()->getComments();
            $i = 0;
            foreach ($comments as $comment) {
                $this->view->page['comment'][$i]['author'] = $comment['author'];
                $this->view->page['comment'][$i]['content'] = $comment['content'];
                $this->view->page['comment'][$i]['created_on'] = $comment['created_on'];
                $this->view->page['comment'][$i]['modified_on'] = $comment['modified_on'];
                $this->view->page['comment'][$i]['status'] = $comment['status'];
                $i++;
            }
            
            $form = Axis::model('cms/form_comment', array('pageId' => $pageId));
            if ($this->_request->isPost()) {
                $data = $this->_getAllParams();
                if ($form->isValid($data)) {
                    Axis::single('cms/page_comment')->insert(array(
                        'author' => $data['author'],
                        'email' => $data['email'],
                        'status' => 0,
                        'content' => $data['content'],
                        'created_on' => Axis_Date::now()->toSQLString(),
                        'cms_page_id' => $pageId
                    ));
                    Axis::dispatch('cms_comment_add_success', $data);
                    Axis::message()->addSuccess(
                        Axis::translate('cms')->__(
                            'Comment successfully added'
                    ));
                    $this->_redirect(
                        $this->getRequest()->getServer('HTTP_REFERER')
                    );
                } else {
                    $form->populate($data);
                }
            }
            $this->view->formComment = $form;
        }

        $metaTitle = $content['meta_title'] == '' ?
                $content['title'] : $content['meta_title'];
        $this->view->meta()
            ->setTitle($metaTitle, 'cms_page', $pageId)
            ->setDescription($content['meta_description'])
            ->setKeywords($content['meta_keyword'])
        ;
        
        $layout = substr($currentPage->layout, strpos($currentPage->layout, '_'));
        $this->_helper->layout->setLayout('layout' . $layout);
        
        $this->render();
    }
}