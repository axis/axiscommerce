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
class Axis_Cms_Admin_CategoryController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        function getChilds($node, $root) {
            $data = array();
            $select = Axis::model('cms/category')->select('*');
            if ($root) {
                $select->where('cc.site_id = ?', $node)
                    ->where('cc.parent_id is NULL');
            } else {
                $select->where('cc.parent_id = ?', $node);
            }
            //get categories
            foreach ($select->fetchAssoc() as $_category) {
                $data[] = array(
                    'leaf'      => false,
                    'id'        => $_category['id'],
                    'site_id'   => $_category['site_id'],
                    'text'      => $_category['name'],
                    'iconCls'   => 'icon-folder',
                    'expanded'  => true,
                    'cls'       => $_category['is_active'] ? '' : 'disabledNode',
                    'children'  => getChilds($_category['id'], false)
                );
            }
            return $data;
        }

        $data = array();
        foreach (Axis_Core_Model_Site::collect() as $siteId => $siteName) {
            $data[] = array(
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

        return $this->_helper->json->sendRaw($data);
    }
    
    public function loadAction()
    {
        $id = $this->_getParam('id');
        $row = Axis::single('cms/category')
            ->find($id)
            ->current();

        $data = $row->toArray();
        $content = Axis::model('cms/category_content')
            ->select(array('language_id', '*'))
            ->where('ccc.cms_category_id = ?', $row->id)
            ->fetchAssoc();

        foreach(Axis_Locale_Model_Language::collect() as $languageId => $lName) {
            $data['content']['lang' . '_' . $languageId] =
                isset($content[$languageId]) ? $content[$languageId] : array();
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row = $this->_getAllParams();
        $model        = Axis::model('cms/category');
        $modelContent = Axis::model('cms/category_content');
        
        $row = $model->save($_row);
        
        //save content
        foreach ($_row['content'] as $languageId => $_rowContent) {
            $modelContent->getRow($row->id, $languageId)
                ->setFromArray($_rowContent)
                ->save();
        }
        
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Category was saved successfully'
        ));
        
        return $this->_helper->json
            ->setData(array('id' => $row->id))
            ->sendSuccess();
    }

    public function removeAction()
    {
        $id = $this->_getParam('id');
        Axis::model('cms/category')->delete(
            Axis::db()->quoteInto('id IN(?)', $id)
        );
        Axis::message()->addSuccess(
            Axis::translate('cms')->__(
                'Category was deleted successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }
    
    public function moveAction()
    {
        $id       = $this->_getParam('id');
        $parentId = $this->_getParam('parent_id');
        $siteId   = $this->_getParam('site_id');
        if (empty($parentId)) {
            $parentId = new Zend_Db_Expr('NULL');
        }
        $row = Axis::model('cms/category')->find($id)->current();
        $row->parent_id = $parentId;
        $row->site_id   = $siteId;
        $row->save();

        return $this->_helper->json->sendSuccess();
    }
}