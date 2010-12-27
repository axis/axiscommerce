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
        $this->view->pageTitle = Axis::translate('cms')->__('Page Comments');
        $this->view->status = Axis::model('cms/page_comment')->getStatuses();
        $this->render();
    }

    public function getCommentsAction()
    {
        $this->_helper->layout->disableLayout();

        $filterTree = $this->_getParam('filterTree');

        $select = Axis::model('cms/page_comment')->select('*')
            ->calcFoundRows()
            ->addPageName()
            ->addCategoryName()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        if ($this->_hasParam('uncategorized')) {
            $select->addFilterByUncategorized();
        }

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function saveCommentAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();
        $isNew = false;
        if (isset($data['id']) && !$data['id']) {
            unset($data['id']);
            $isNew = true;
            $data['created_on'] = Axis_Date::now()->toSQLString();
        } else {
            $data['modified'] = Axis_Date::now()->toSQLString();
        }
        $row = Axis::model('cms/page_comment')->getRow($data);
        $row->save();

        if ($isNew) {
            Axis::dispatch('cms_comment_add_success', $row);
        } else {
            Axis::dispatch('cms_comment_update_success', $row);
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));

        $this->_helper->json->sendSuccess(array(
            'id' => $row->id
        ));
    }

    public function quickSaveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        $mComment = Axis::model('cms/page_comment');
        foreach ($data as $commentId => $values) {
            $mComment->find($commentId)
                ->current()
                ->setFromArray($values)
                ->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));

        $this->_helper->json->sendSuccess();
    }

    public function deleteCommentAction()
    {
        $this->_helper->layout->disableLayout();

        Axis::single('cms/page_comment')->delete(
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

            $tableCmsCategory = Axis::single('cms/category');
            if ($root) {
                $cats = $tableCmsCategory->select('*')
                    ->where('cc.site_id = ?', $node)
                    ->where('cc.parent_id is NULL')
                    ->fetchAssoc();
            } else {
                $cats = $tableCmsCategory->select('*')
                    ->where('cc.parent_id = ?', $node)
                    ->fetchAssoc();
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
                    $pages = Axis::single('cms/page')
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

            $pages = Axis::single('cms/page')
                ->select(array('name', 'id', 'is_active'))
                ->addFilterByUncategorized()
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