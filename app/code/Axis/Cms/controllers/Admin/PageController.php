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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Admin_PageController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Categories/Pages');
        $this->render();
    }
    
    public function listAction()
    {
        $select = Axis::model('cms/page')->select('*')
            ->calcFoundRows()
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

    public function loadAction()
    {
        $id = $this->_getParam('id');
        $rowPage = Axis::single('cms/page')
            ->find($id)
            ->current();

        $content = Axis::model('cms/page_content')
            ->select(array('language_id', '*'))
            ->where('cpc.cms_page_id = ?', $rowPage->id)
            ->fetchAssoc();

        $category = Axis::model('cms/page_category')
            ->select('cms_category_id')
            ->where('cpc.cms_page_id = ?', $rowPage->id)
            ->fetchCol();

        $data = $rowPage->toArray();
        $data['category'] = $category;
        foreach(Axis_Collect_Language::collect() as $languageId => $lName) {
            $data['content']['lang_' . $languageId] =
                isset($content[$languageId]) ? $content[$languageId] : array();
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row          = $this->_getAllParams();
        $model         = Axis::model('cms/page');
        $modelContent  = Axis::model('cms/page_content');
        $modelCategory = Axis::model('cms/page_category');
        
        $row = $model->save($_row);
        
        //save page content
        foreach ($_row['content'] as $languageId => $values) {
            if (empty($values['link'])) {
                $values['link'] = $row->name;
            }
            $modelContent->getRow($row->id, $languageId)
                ->setFromArray($values)
                ->save();
        }
        
        //save category relation
        $modelCategory->delete(
            Axis::db()->quoteInto('cms_page_id = ?', $row->id)
        );
        $categories = array_filter(
            Zend_Json::decode($_row['category'])
        );
        foreach ($categories as $categoryId) {
            $modelCategory->createRow(array(
                'cms_category_id' => $categoryId,
                'cms_page_id'     => $row->id
            ))->save();
        }

        if (!isset($_row['id']) || ($_row['id'] != $row->id)) {
            Axis::dispatch('cms_page_add_success', array(
                'page_id' => $row->id
            ));
        } else {
            Axis::dispatch('cms_page_update_success', array(
                'page_id' => $row->id
            ));
        }
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was saved successfully'
        ));

        return $this->_helper->json
            ->setData(array('id' => $row->id))
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::single('cms/page');
        foreach ($_rowset as $_row) {
            $model->getRow($_row)->save();
        }
        
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::model('cms/page')->delete(
            Axis::db()->quoteInto('id IN (?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was deleted successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }
}