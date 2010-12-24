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
class Axis_Admin_Cms_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Categories/Pages');
        $this->render();
    }

    public function getSiteTreeAction()
    {
        $this->_helper->layout->disableLayout();

        function getChilds($node, $root) {
            $result = array();
            $tableCmsCategory = Axis::model('cms/category');

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
                    'leaf'      => false,
                    'id'        => $cat['id'],
                    'site_id'   => $cat['site_id'],
                    'text'      => $cat['name'],
                    'iconCls'   => 'icon-folder',
                    'expanded'  => true,
                    'cls'       => $cat['is_active'] ? '' : 'disabledNode',
                    'children'  => getChilds($cat['id'], false)
                );
            }
            return $result;
        }

        $result = array();
        foreach (Axis_Collect_Site::collect() as $siteId => $siteName) {
            $result[] = array(
                'leaf'     => false,
                'id'       => "_" . $siteId, // preventing duplicate ids. siteId == $cat['id]
                'site_id'  => $siteId,
                'text'     => $siteName,
                'checked'  => 'undefined',
                'iconCls'  => 'icon-folder',
                'cls'      => 'root-node',
                'expanded' => true,
                'children' => getChilds($siteId, true)
            );
        }

        $this->_helper->json->sendJson($result, false, false);
    }

    public function saveCategoryAction()
    {
        $this->_helper->layout->disableLayout();

        $id = Axis::model('cms/category')->save($this->_getAllParams());

        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Category was saved successfully'
        ));
        $this->_helper->json
            ->setData(array('id' => $id))
            ->sendSuccess();
    }

    public function moveCategoryAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();
        if (empty($data['parent_id'])) {
            $data['parent_id'] = new Zend_Db_Expr('NULL');
        }
        Axis::model('cms/category')->find($data['id'])
            ->current()
            ->setFromArray($data)
            ->save();

        $this->_helper->json->sendSuccess();
    }

    public function getCategoryDataAction()
    {
        $this->_helper->layout->disableLayout();

        $rowCategory = Axis::single('cms/category')
            ->find($this->_getParam('id'))
            ->current();

        $category = $rowCategory->toArray();
        $content = Axis::model('cms/category_content')
            ->select(array('language_id', '*'))
            ->where('ccc.cms_category_id = ?', $rowCategory->id)
            ->fetchAssoc();

        foreach(Axis_Collect_Language::collect() as $languageId => $lName) {
            $category['content']['lang' . '_' . $languageId] =
                isset($content[$languageId]) ? $content[$languageId] : array();
        }

        $this->_helper->json->sendSuccess(array(
            'data' => $category
        ));
    }

    public function deleteCategoryAction()
    {
        $this->_helper->layout->disableLayout();

        $categoryId = $this->_getParam('id');

        $success = Axis::single('admin/cms_category')->delete(
            Axis::db()->quoteInto('id IN(?)', $categoryId)
        );
        if ($success) {
            Axis::message()->addSuccess(
                Axis::translate('cms')->__(
                    'Category was deleted successfully'
            ));
        }
        $this->_helper->json->sendJson(array(
            'success' => $success
        ));
    }

    public function getPagesAction()
    {
        $this->_helper->layout->disableLayout();

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

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function getPageDataAction()
    {
        $this->_helper->layout->disableLayout();

        $rowPage = Axis::single('cms/page')
            ->find($this->_getParam('id'))
            ->current();

        $content = Axis::model('cms/page_content')
            ->select(array('language_id', '*'))
            ->where('cpc.cms_page_id = ?', $rowPage->id)
            ->fetchAssoc();

        $category = Axis::model('cms/page_category')
            ->select('cms_category_id')
            ->where('cpc.cms_page_id = ?', $rowPage->id)
            ->fetchCol();

        $page = $rowPage->toArray();
        $page['category'] = $category;
        foreach(Axis_Collect_Language::collect() as $languageId => $lName) {
            $page['content']['lang_' . $languageId] =
                isset($content[$languageId]) ? $content[$languageId] : array();
        }

        $this->_helper->json->sendSuccess(array(
            'data' => $page
        ));
    }

    public function savePageAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();

        $id = Axis::model('cms/page')->save($data);

        if (!isset($data['id']) || ($data['id'] != $id)) {
            Axis::dispatch('cms_page_add_success', array(
                'page_id' => $id
            ));
        } else {
            Axis::dispatch('cms_page_update_success', array(
                'page_id' => $id
            ));
        }
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was saved successfully'
        ));

        $this->_helper->json
            ->setData(array('id' => $id))
            ->sendSuccess();
    }

    public function batchPageSaveAction()
    {
        $this->_helper->layout->disableLayout();

        $pages = Zend_Json_Decoder::decode($this->_getParam('data'));

        $modelPage = Axis::single('cms/page');
        foreach ($pages as $pageId => $values) {
            $modelPage->update(array(
                'name'        => $values['name'],
                'is_active'   => (int) $values['is_active'],
                'comment'     => (int) $values['comment'],
                'layout'      => $values['layout'],
                'show_in_box' => $values['show_in_box']
            ), Axis::db()->quoteInto('id = ?', $pageId));
        }
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was saved successfully'
        ));
        $this->_helper->json->sendSuccess();
    }

    public function deletePageAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::model('cms/page')->delete(
            Axis::db()->quoteInto('id IN (?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Page was deleted successfully'
        ));
        $this->_helper->json->sendSuccess();
    }
}