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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template_Box extends Axis_Db_Table
{
    protected $_name = 'core_template_box';

    /**
     * Retrieve the list of all boxes, including active cms boxes
     *
     * @param bool $includeCms
     * @return array
     */
    public function getList($includeCms = true)
    {
        $modules = Axis::single('core/module')->getList();

        if (!($boxes = Axis::cache()->load('boxes_list'))) {
            $boxes = array();
            foreach ($modules as $moduleCode => $values) {
                list($namespace, $module) = explode('_', $moduleCode, 2);
                $path = Axis::config()->system->path
                      . '/app/code/' . $namespace . '/' . $module . '/Box';

                if (!is_readable($path)) {
                    continue;
                }
                $dir = opendir($path);
                while (($file = readdir($dir))) {
                    if (!is_readable($path . '/' . $file)
                        || !is_file($path . '/' . $file)) {

                        continue;
                    }
                    $box = substr($file, 0, strpos($file, '.'));

                    if (in_array($box, array('Abstract', 'Block'))) {
                        continue;
                    }
                    $boxes[] = ucwords($namespace . '_' . $module . '_' . $box);

                }
            }
            Axis::cache()->save($boxes, 'boxes_list', array('modules'));
        }

        /* this part is not needed to be cached */
        if ($includeCms && in_array('Axis_Cms', array_keys($modules))) {
            $cmsBoxes = Axis::model('cms/block')->select('*')
                ->where('is_active = 1')
                ->fetchAll();
            foreach ($cmsBoxes as $box) {
                $boxes[] = 'Axis_Cms_Block_' . $box['name'];
            }
        }
        sort($boxes);

        return $boxes;
    }

    /**
     * Retrieve the information about boxes within provided pages and template
     *
     *  @param int $templateId
     *  @param  array $pagesIds
     *  @return array Boxes and inforamtion about them
     */
    public function getCustomInfo($templateId, $pagesIds)
    {
        return $this->select(array('id', 'class', 'block', 'config'))
            ->joinInner('core_template_box_page',
                'ctbp.box_id = ctb.id',
                array('box_show',
                    'sort_order',
                    'other_block' => 'block',
                    'template',
                    'tab_container',
                    'page_id'
                )
            )->where('ctb.template_id = ?', $templateId)
            ->where('ctb.box_status = 1')
            ->where('ctbp.page_id IN(?)', $pagesIds)
            ->order('ctb.sort_order')
            ->fetchAll()
            ;
    }

    /**
     * Save or insert box data
     *
     * @param int $templateId
     * @param array $data
     * @param string(normal|batch) mode
     * @return bool
     */
    public function save($templateId, $data, $mode = 'normal')
    {
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return false;
        }

        foreach ($data as $id => $item) {
            if (!isset($item['id'])
                || !$row = $this->find($item['id'])->current()) {

                $row = $this->createRow();
                $row->template_id = $templateId;
            } else {
                unset($item['id']);
            }
            $row->setFromArray($item);
            $row->save();

            /* insert assignments to pages */
            $modelBoxPage = Axis::single('core/template_box_page');
            if ('batch' != $mode) {

                $modelBoxPage->delete('box_id = ' . intval($row->id));

                foreach ($item['box'] as $pageId => $box) {
                    if (isset($item['show'][$pageId])
                        && $item['show'][$pageId]) {

                        $show = 1;
                    } elseif (isset($item['hide'][$pageId])
                        && $item['hide'][$pageId]) {

                        $show = 0;
                    } else {
                        continue;
                    }
                    $modelBoxPage->insert(array(
                        'box_id'        => $row->id,
                        'box_show'      => $show,
                        'page_id'       => $pageId,
                        'sort_order'    => $row->sort_order,
                        'tab_container' => $box['tab_container'],
                        'template'      => $box['template'],
                        'block'         => $box['block']
                    ));
                }
            } else {
                // get rows that remains visible
                $pageIds = array_filter(explode(',', trim($item['show'], ', ')));
                if (!count($pageIds)) {
                    $modelBoxPage->delete(array(
                        'box_id = ' . intval($row->id),
                        'box_show = 1'
                    ));
                    continue;
                }

                $boxIds = array_fill(0, count($pageIds), $row->id);
                $rowsToUpdate = $modelBoxPage->find($boxIds, $pageIds);

                // delete all others visible
                $modelBoxPage->delete(array(
                    'box_id = ' . intval($row->id),
                    'box_show = 1',
                    $this->_db->quoteInto('page_id NOT IN (?)', $pageIds)
                ));

                // update existing
                foreach ($rowsToUpdate as $rowToUpdate) {
                    unset(
                        $pageIds[array_search($rowToUpdate->page_id, $pageIds)]
                    );
                    $rowToUpdate->setFromArray(array(
                        'sort_order' => $row->sort_order
                    ));
                    $rowToUpdate->save();
                }

                // insert new
                foreach ($pageIds as $pageId) {
                    $modelBoxPage->insert(array(
                        'box_id'   => $row->id,
                        'sort_order' => $row->sort_order,
                        'page_id'  => $pageId,
                        'box_show' => 1
                    ));
                }
            }
        }

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Box was saved successfully'
        ));
        return true;
    }

    /**
     * Insert box
     * @param string class 'Catalog_Navigation'
     * @param string block 'left' or  'footer' , etc...
     * @param mixed $pages
     *  'module/controler/action',
     *  array(
     *      'module/controler/action',
     *      'module2/controler/action'
     *  )
     * @param int $templateId If template is not provided, current templateId will be used
     * @param int boxStatus 1 or 0 default 1
     * @param int sort_order 100 (default '100')
     * @param array $extraOptions
     *  'module/controler/action' => array(
     *      'box_show' => 0|1,
     *      'template' => 'template',
     *      'tab_container' => 'container',
     *      'block' => 'block'
     *      'sort_order' => sort_order
     *  )
     * @return Axis_Core_Model_Template_Box Provides fluent interface
     */
    public function add(
        $class,
        $block = 'content',
        $pages = '*/*/*',
        $sortOrder = 100,
        $config = '',
        $templateId = null,
        $boxStatus = 1,
        $extraOptions = array())
    {
        if (!is_array($pages)) {
            $pages = array($pages);
        }
        if (null === $templateId) {
            $templateId = isset(Axis::config()->design->main->frontTemplateId) ?
                Axis::config()->design->main->frontTemplateId : 1;
        }
        $data = array(
            'block'       => $block,
            'class'       => $class,
            'sort_order'  => $sortOrder,
            'box_status'  => $boxStatus,
            'template_id' => $templateId,
            'config'      => $config
        );
        $boxId = $this->insert($data);

        foreach ($pages as $page) {
            $pageId = Axis::single('core/page')->getPageIdByRequest($page);
            if (!$pageId) {
                $request = explode('/', $page);
                $pageId = Axis::single('core/page')->insert(array(
                    'module_name' => $request[0],
                    'controller_name' => $request[1],
                    'action_name' => $request[2]
                ));
            }
            Axis::single('core/template_box_page')->insert(array(
                'box_id'     => $boxId,
                'page_id'    => $pageId,
                'box_show'   => $data['box_status'],
                'template'   => new Zend_Db_Expr('NULL'),
                'block'      => new Zend_Db_Expr('NULL'),
                'sort_order' => $data['sort_order']
            ));
        }

        foreach ($extraOptions as $page => $values) {
            $pageId = Axis::single('core/page')->getPageIdByRequest($page);

            if (!$pageId) {
                $request = explode('/', $page);
                $pageId = Axis::single('core/page')->insert(array(
                    'module_name'     => $request[0],
                    'controller_name' => $request[1],
                    'action_name'     => $request[2]
                ));
                // there are no boxes assigned to page, if it was just created,
                $row = Axis::single('core/template_box_page')->createRow();
            } elseif (!$row = Axis::single('core/template_box_page')
                ->find($boxId, $pageId)->current()) {

                $row = Axis::single('core/template_box_page')->createRow();
            }
            $row->setFromArray($values);
            $row->box_id = $boxId;
            $row->page_id = $pageId;
            $row->save();
        }

        return $this;
    }

    /**
     * Remove box
     * @param string className (ModuleName_BoxName | ModuleName)
     * @return Axis_Core_Model_Template_Box provides fluent interface
     */
    public function remove($className)
    {
        list($namespace, $moduleName, $boxName) = explode('_', $className);

        if (empty($boxName)) {
            $boxName = '%';
        }
        if (empty($moduleName)) {
            $moduleName = '%';
        }

        $this->delete("class LIKE '{$namespace}_{$moduleName}_{$boxName}'");

        return $this;
    }
}