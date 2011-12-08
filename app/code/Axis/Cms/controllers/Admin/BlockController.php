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
class Axis_Cms_Admin_BlockController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Static Blocks');
        $this->render();
    }

    public function listAction()
    {
        $select = Axis::model('cms/block')->select('*')->calcFoundRows();
        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }

    public function loadAction()
    {
        $id = $this->_getParam('id');
        $row = Axis::model('cms/block')->find($id)->current();
        if (!$row) {
            Axis::message()->addError(
                Axis::translate('Axis_Cms')->__(
                    'Block %s not found', $id
            ));
            return $this->_helper->json->sendFailure();
        }

        $data = $row->toArray();
        $content = Axis::single('cms/block_content')
            ->select(array('language_id', '*'))
            ->where('block_id = ? ', $row->id)
            ->fetchAssoc();
        foreach (Axis_Locale_Model_Language::getConfigOptionsArray() as $languageId => $values) {
            $data['content']['lang_' . $languageId] = array();
            if (!isset($content[$languageId])) {
                continue;
            }
            $data['content']['lang_' . $languageId] = $content[$languageId];
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row = $this->_getAllParams();
        $row  = Axis::model('cms/block')->save($_row);
        
        //save cms block content
        $languageIds  = array_keys(Axis_Locale_Model_Language::getConfigOptionsArray());
        $modelContent = Axis::model('cms/block_content');
        foreach ($languageIds as $languageId) {
            if (!isset($_row['content'][$languageId])) {
                continue;
            }
            $modelContent->getRow($row->id, $languageId)
                ->setFromArray($_row['content'][$languageId])
                ->save();
        }
        
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return $this->_helper->json
            ->setData(array('id' => $row->id))
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::model('cms/block');
        foreach ($_rowset as $_row) {
            $model->save($_row);
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
        Axis::single('cms/block')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        return $this->_helper->json->sendSuccess();
    }
}