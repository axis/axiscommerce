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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Admin_CommentController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Page Comments');
        $this->view->status = Axis::model('cms/page_comment')->getStatuses();
        $this->render();
    }

    public function listAction()
    {
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

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row = $this->_getAllParams();
        $isNew = false;
        if (isset($_row['id']) && !$_row['id']) {
            unset($_row['id']);
            $isNew = true;
            $_row['created_on'] = Axis_Date::now()->toSQLString();
        } else {
            $_row['modified'] = Axis_Date::now()->toSQLString();
        }
        $row = Axis::model('cms/page_comment')->getRow($_row);
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

        return $this->_helper->json
            ->setId($row->id)
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::model('cms/page_comment');
        foreach ($_rowset as $id => $_row) {
            $model->find($id)
                ->current()
                ->setFromArray($_row)
                ->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('cms/page_comment')->delete(
            $this->db->quoteInto('id IN(?)', $data
        ));
        return $this->_helper->json->sendSuccess();
    }
    

    public function getPageTreeAction()
    {
        function getChilds($node, $root) {
            $data = array();
            $select = Axis::single('cms/category')->select('*');
            if ($root) {
                $select->where('cc.site_id = ?', $node)
                    ->where('cc.parent_id is NULL');
            } else {
                $select->where('cc.parent_id = ?', $node);
            }
            //get categories
            foreach ($select->fetchAssoc() as $_category) {
                $data[] = array(
                    'leaf'     => false,
                    'id'       => "_" . $_category['id'],
                    'siteId'   => 'null',
                    'catId'    => $_category['id'],
                    'pageId'   => 'null',
                    'text'     => $_category['name'],
                    'iconCls'  => 'icon-folder',
                    'expanded' => true,
                    'cls'      => $_category['is_active'] ? '' : 'disabledNode',
                    'children' => getChilds($_category['id'], false)
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
                    $data[] = array(
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
            return $data;
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

        $data = array();
        //custom root node (uncategorized)
        $data[] = array(
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
        foreach (Axis_Collect_Site::collect() as $siteId => $siteName) {
            $data[] = array(
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

        $this->_helper->json->sendRaw($data);
    }
}