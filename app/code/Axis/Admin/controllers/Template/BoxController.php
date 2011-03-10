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
class Axis_Admin_Template_BoxController extends Axis_Admin_Controller_Back
{
    private $_templateId;

    public function init()
    {
        parent::init();
        $this->_helper->layout->disableLayout();
        $this->_templateId = (int) $this->_getParam('template_id', 0);
    }
    
    public function indexAction()
    {
        $where = NULL;
        if ($this->_templateId) {
            $where = 'template_id = ' . $this->_templateId;
        }
        $boxes = Axis::single('core/template_box')->fetchAll($where)->toArray();
        Zend_Debug::dump($boxes);
    }

    public function listAction()
    {
        $select = Axis::model('core/template_box')->select('*')
            ->calcFoundRows()
            ->addPageIds()
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

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getAllParams();
        
        Axis::single('core/template_box')->getRow($data)->save();
        
        $modelAssign = Axis::model('core/template_box_page');
        $modelAssign->delete(
            Axis::db()->quoteInto('box_id = ?', $data['id'])
        );
        foreach ($data['assign'] as $pageId => $_rowData) {
            if ('' === $_rowData['box_show']) {
                continue;
            }
            foreach ($_rowData as &$_columnValue) {
                if ('' === $_columnValue) {
                    $_columnValue = null;
                }
            }
            $modelAssign->createRow($_rowData)->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Box was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }

    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Zend_Json::decode($this->_getParam('data'));
        $modelBlock = Axis::single('core/template_box');
        $modelAssign = Axis::model('core/template_box_page');
        foreach ($data as $rowData) {
            
            $row = $modelBlock->getRow($rowData);
            $row->template_id = $this->_templateId;
            $row->save();

            $assigns = array_filter(explode(',', $rowData['page_ids']));
            $modelAssign->delete(array(
                Axis::db()->quoteInto('box_id = ?', $row->id),
                Axis::db()->quoteInto('page_id NOT IN (?)', $assigns)
            ));
            foreach ($assigns as $pageId) {
                $_row = $modelAssign->find($row->id, $pageId)->current();
                if (!$_row) {
                    $_row = $modelAssign->createRow(array(
                        'box_id'   => $row->id,
                        'page_id'  => $pageId,
                        'box_show' => 1
                    ));
                }
                $_row->sort_order = $row->sort_order;
                $_row->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Box was saved successfully'
        ));
        
        return $this->_helper->json->sendSuccess();
    }

    public function editAction()
    {
        $boxId = $this->_getParam('id');
        $modelTemplateBox = Axis::model('core/template_box');
        $this->view->box = $modelTemplateBox->find($boxId)
            ->current()
            ->toArray();
        $this->view->templateId = $this->_templateId;

        $this->view->assignments = Axis::model('core/template_box_page')
            ->select(array('page_id', '*'))
            ->where('box_id = ?', $boxId)
            ->fetchAssoc()
            ;
        $blockClasses = array();
        foreach ($modelTemplateBox->getList() as $box) {
            $type = 'dynamic';
            if (strstr($box, 'Axis_Cms_Block')) {
                $type = 'static';
            }
            $blockClasses[$type][$box] = $box;
        }
        $this->view->boxClasses = $blockClasses;

        $modelPage = Axis::model('core/page');
        $this->view->pages = $modelPage->select()->order(
            array('module_name', 'controller_name', 'action_name')
        )->fetchAll();

        $this->render('form');
    }

    public function deleteAction()
    {
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));

        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        Axis::single('core/template_box')->delete(
            $this->db->quoteInto('id IN (?)', $ids)
        );

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}