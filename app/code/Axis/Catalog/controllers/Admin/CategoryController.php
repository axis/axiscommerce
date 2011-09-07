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
class Axis_Catalog_Admin_CategoryController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $data = Axis::model('catalog/category')->getNestedTreeData();

        foreach ($data as &$_category) {
            if ($_category['lvl'] == 0) {
                $_category['disable_edit'] = true;
                $_category['disable_delete'] = true;
            } else {
                $_category['disable_edit'] = false;
                $_category['disable_delete'] = false;
            }
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function getRootCategoriesAction()
    {
        $data = Axis::model('catalog/category')->getRootCategories();
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $categoryId = $this->_getParam('categoryId');
        $row = Axis::model('catalog/category')->select('*')
            ->addKeyWord()
            ->where('cc.id = ?', $categoryId)
            ->fetchRow()
            ;
        if (!$row) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Category not exist'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $data = $row->toArray();
        $rowset = Axis::model('catalog/category_description')->select()
            ->where('category_id = ?', $categoryId)
            ->fetchAll();

        foreach ($rowset as $row) {
            foreach ($row as $label => $value) {
                $data[$label . '_' . $row['language_id']] = $value;
            }
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }

    /**
     * Save product category
     */
    public function saveAction()
    {
        $success = true;

        $modelCategory = Axis::model('catalog/category');
        /* @var $modelCategory Axis_Catalog_Model_Category */

        $parentId   = $this->_getParam('parent_id', 0);
        $categoryId = $this->_getParam('id', 0);
        $catKeyWord = strtolower($this->_getParam('key_word'));
        $siteId     = $this->_getParam('site_id');

        /* if human url already exist */
        $modelHurl = Axis::model('catalog/hurl');
        if (!empty($catKeyWord) &&
            $modelHurl->hasDuplicate($catKeyWord, $siteId, $categoryId)) {

            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Duplicate entry (url)'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $image = $this->_getParam('image');

        // unlink images if requested
        foreach ($image as $imageType => $values) {
            if (isset($image[$imageType]['delete'])
                && !empty($image[$imageType]['src'])) {

                @unlink(
                    Axis::config()->system->path
                    . '/media/category'
                    . $image[$imageType]['src']
                );
                $image[$imageType]['src'] = '';
            }
        }

        // save category
        $data = array(
            'status'        => $this->_getParam('status', 'enabled'),
            'image_base'    => $image['base']['src'],
            'image_listing' => $image['listing']['src']
        );

        if ($categoryId) {
            $data['modified_on'] = Axis_Date::now()->toSQLString();
            $modelCategory->update($data, array(
                $this->db->quoteInto('id = ?', $categoryId)
            ));
            $event = 'catalog_category_update_success';
        } else {
            $data['created_on'] = Axis_Date::now()->toSQLString();
            $categoryId = $modelCategory->insertItem($data, $parentId);
            $event = 'catalog_category_add_success';
        }
        Axis::dispatch($event, array(
            'category_id' => $categoryId,
            'data' => $data
        ));

        if (!$categoryId) {
            Axis::message()->addError('Unable to save category');
            return $this->_helper->json->sendFailure();
        }
        /* Save category description */
        $categoryName        = $this->_getParam('name');
        $categoryDescription = $this->_getParam('description');
        $metaTitle           = $this->_getParam('meta_title');
        $metaDescription     = $this->_getParam('meta_description');
        $metaKeyword         = $this->_getParam('meta_keyword');

        $mCategoryDescription = Axis::model('catalog/category_description');
        foreach (array_keys(Axis_Collect_Language::collect()) as $languageId) {
            if (!isset($categoryName[$languageId])) {
                continue;
            }
            $row = $mCategoryDescription->save(array(
                'category_id'         => $categoryId,
                'language_id'         => $languageId,
                'name'                => $categoryName[$languageId],
                'description'         => $categoryDescription[$languageId],
                'meta_title'          => $metaTitle[$languageId],
                'meta_description'    => $metaDescription[$languageId],
                'meta_keyword'        => $metaKeyword[$languageId],
                'image_base_title'    => $image['base']['title'][$languageId],
                'image_listing_title' => $image['listing']['title'][$languageId]
            ));
            if (!$row) {
                return $this->_helper->json->sendFailure();
            }
        }

        /* Save Human Url */
        $modelHurl->delete(array(
            $this->db->quoteInto('key_id = ?', $categoryId),
            $this->db->quoteInto('key_type = ?', 'c')
        ));

        if (!empty($catKeyWord)) {
            $hurlSuccess = $modelHurl->save(array(
                'key_word' => $catKeyWord,
                'key_type' => 'c',
                'site_id'  => $siteId,
                'key_id'   => $categoryId
            ));
            if (!$hurlSuccess) {
                return $this->_helper->json->sendFailure();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Category successfully saved'
            )
        );

        return $this->_helper->json
            ->setData(array('category_id' => $categoryId))
            ->sendSuccess()
        ;
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        $model = Axis::model('catalog/category');
        foreach ($data as $categoryId) {
            $model->deleteItem($categoryId);
            Axis::dispatch('catalog_category_remove_success', array(
                'category_id' => $categoryId
            ));
        }

        return $this->_helper->json->sendSuccess();
    }

    public function moveAction()
    {
        $moveType       = $this->_getParam('moveType');
        $newParentId    = $this->_getParam('newParentId');
        $categoryId     = $this->_getParam('catId');

        $model = new Axis_NSTree_Table();

        switch ($moveType) {
            case 'moveTo':
                $success = $model->replaceNode($categoryId, $newParentId);
                break;
            case 'moveBefore':
                $success = $model->replaceBefore($categoryId, $newParentId);
                break;
        }

        if (!$success) {
            return $this->_helper->json->sendFailure();
        }
        return $this->_helper->json->sendSuccess();
    }

    public function saveImageAction()
    {
        $this->_helper->layout->disableLayout();

        try {
            $uploader = new Axis_File_Uploader('image');
            $file = $uploader
                ->setAllowedExtensions(array('jpg','jpeg','gif','png'))
                ->setUseDispersion(true)
                ->save(Axis::config()->system->path . '/media/category');

            $data = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $data = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
        }

        return $this->getResponse()->appendBody(Zend_Json::encode($data));
    }
}