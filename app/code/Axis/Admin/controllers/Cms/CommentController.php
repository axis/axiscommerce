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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Cms_CommentController extends Axis_Admin_Controller_Back 
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__(
            'Page Comments'
        );
        
        $this->view->status = Axis_Admin_Model_Cms_Page_Comment::getStatuses();
        
        $this->render();
    }
    
    public function getCommentsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $filterGrid = $this->_getParam('filter');
        $filterTree = $this->_getParam('filterTree');

        $tableComment = Axis::single('admin/cms_page_comment');
        
        $pagingParams = array(
            'start' => (int) $this->_getParam('start'),
            'limit' => (int) $this->_getParam('limit'),
            'sort'  => $this->_getParam('sort'),
            'dir'   => $this->_getParam('dir')
        );
        
        $this->_helper->json->sendSuccess(array(
            'totalCount' => $tableComment->getCount($filterGrid, $filterTree),
            'comments' => $tableComment
                ->getComments($filterGrid, $filterTree, $pagingParams)
        ));
    }
    
    public function saveCommentAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = $this->_getAllParams();
        
        $tableComment = Axis::single('admin/cms_page_comment');
        
        if ($data['commentId'] != 'new') {
            $currentComment = $tableComment
                ->find($this->_getParam('commentId'))
                ->current();
            $currentComment->author = $data['author'];
            $currentComment->email  = $data['email'];
            $currentComment->status = $data['status'] != '' ?
                $data['status'] : 0;
            $currentComment->content = $data['content'];
            $currentComment->modified_on = Axis_Date::now()->toSQLString();
            $currentComment->save();
            Axis::dispatch('cms_comment_update_success', $data);
        } else {
            $tableComment->insert(array(
                'author'      => $data['author'],
                'email'       => $data['email'],
                'status'      => $data['status'] != '' ? $data['status'] : 0,
                'content'     => $data['content'],
                'created_on'  => Axis_Date::now()->toSQLString(),
                'cms_page_id' => $data['pageId']
            ));
            Axis::dispatch('cms_comment_add_success', $data);
        }
        $this->_helper->json->sendSuccess();
    }
    
    public function quickSaveAction() 
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        $tableComment = Axis::single('admin/cms_page_comment');
        
        foreach ($data as $commentId => $values) {
            $currentComment = $tableComment->find($commentId)->current();
            $currentComment->author = $values['author'];
            $currentComment->status = $values['status'];
            $currentComment->save();
        }
        $this->_helper->json->sendSuccess();
    }
    
    public function deleteCommentAction()
    {
        $this->_helper->layout->disableLayout();
        
        Axis::single('admin/cms_page_comment')->delete(
            $this->db->quoteInto('id IN(?)',
                Zend_Json_Decoder::decode($this->_getParam('data')) 
        ));
        $this->_helper->json->sendSuccess();
    }
    
    public function getPageTreeAction()
    {
        $this->_helper->layout->disableLayout();
        
        $sites = Axis_Collect_Site::collect();
        $result = array();
        
	    function getChilds($node, $root) {
	        $result = array();
	        
	        $tableCmsCategory = Axis::single('admin/cms_category');
	        //if !$root -> load subcategories, else -> load root categories of site
	        if ($root) {
	            $cats = $tableCmsCategory->getRootCategory($node);
	        } else {
	            $cats = $tableCmsCategory->getChildCategory($node);
	        }
	        
	        //get categories
	        foreach ($cats as $cat) {
	            $result[] = array(
	                'leaf'     => false,
	                'id'       => "_" . $cat['id'],
	                'siteId'   => 'null',
	                'catId'    => $cat['id'],
	                'pageId'   => 'null',
	                'text'     => $cat['name'],
	                'iconCls'  => 'icon-folder',
	                'expanded' => true,
	                'cls'      => $cat['is_active'] ? '' : 'disabledNode',
	                'children' => getChilds($cat['id'], false)
	            );
	        }
	        
	        //get pages
	        if (!$root) {
                    $pages = Axis::single('admin/cms_page')
                        ->select(array('name', 'id', 'is_active'))
                        ->join('cms_page_category',
                            'cp.id = cpc.cms_page_id',
                            'cms_category_id')
                        ->where('cpc.cms_category_id = ?', $node)
                        ->fetchAll();
	        
                    foreach ($pages as $page) {
	                $result[] = array(
	                    'leaf'    => true,
	                    'siteId'  => 'null',
	                    'catId'   => 'null',
	                    'pageId'  => $page['id'],
	                    'text'    => $page['name'],
	                    'iconCls' => 'icon-page',
	                    'cls'     => $page['is_active'] ? '' : 'disabledNode'
	                );
	            }
	        }
	        return $result;
	    }
	    
	    //load pages that is not linked with category
	    function getLostPage() {
	        $result = array();
	        
	        $pages = Axis::single('admin/cms_page')->
                    select(array('name', 'id', 'is_active'))
                        ->addLostFilter()
                        ->fetchAll();
	        
	        foreach ($pages as $page) {
                    $result[] = array(
                        'leaf'    => true,
                        'siteId'  => 'null',
                        'catId'   => 'null',
                        'pageId'  => $page['id'],
                        'text'    => $page['name'],
                        'iconCls' => 'icon-page',
                        'cls'     => $page['is_active'] ? '' : 'disabledNode'
                    );
                }
                return $result;
	    }
	    
        //custom root node (uncategorized)
	    $result[] = array(
	        'leaf'     => false,
	        'id'       => 'lost',
	        'siteId'   => 'null',
	        'catId'    => 'null',
	        'pageId'   => 'null',
	        'text'     => Axis::translate('cms')->__('Uncategorized Pages'),
	        'iconCls'  => 'icon-bin',
	        'expanded' => true,
	        'children' => getLostPage()
        );	    
	    
        //autogenerated nodes
        foreach ($sites as $siteId => $siteName) {
            $result[] = array(
                    'leaf'     => false,
                    'id'       => "__" . $siteId,
                    'siteId'   => $siteId,
                    'catId'    => 'null',
                    'pageId'   => 'null',
                    'text'     => $siteName,
                    'iconCls'  => 'icon-folder',
                    'expanded' => true,
                    'children' => getChilds($siteId, true)
            );
        }
        
        $this->_helper->json->sendJson($result, false, false);
    }
}