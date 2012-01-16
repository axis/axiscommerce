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
 * @copyright   Copyright 2008-2012 Axis
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

    protected $_primaty = 'id';

    protected $_selectClass = 'Axis_Core_Model_Template_Box_Select';

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
     * Insert box
     * @param string $class 'Catalog_Navigation'
     * @param string $container 'left' or  'footer' , etc...
     * @param mixed $pages
     *  'module/controler/action',
     *  array(
     *      'module/controler/action',
     *      'module2/controler/action' => array('box_show' => false)
     *  )
     * @param int $templateId If template is not provided, current templateId will be used
     * @param bool $status 1 or 0 default 1
     * @param int $sort_order 100 (default '100')
     * @return Axis_Core_Model_Template_Box Provides fluent interface
     */
    public function add(
        $class,
        $container = 'content',
        $assigns = '*/*/*',
        $sortOrder = 100,
        $config = '{}',
        $templateId = null,
        $status = true
        )
    {
        if (!is_array($assigns)) {
            $assigns = array($assigns);
        }
        if (null === $templateId) {
            $templateId = Axis::config('design/main/frontTemplateId');
        }
        $row = $this->createRow(array(
            'block'       => $container,
            'class'       => $class,
            'sort_order'  => $sortOrder,
            'box_status'  => $status,
            'template_id' => $templateId,
            'config'      => $config
        ));
        $row->save();
        
        $modelPage = Axis::model('core/page');
        $modelAssign = Axis::model('core/template_box_page');
        foreach ($assigns as $page => $options) {
            $data = array();
            
            if (is_array($options)) {
                $data = $options;
            } else {
                $page = $options;
            }
            $page = $modelPage->add($page)->getIdByPage($page);
            
            $data = array_merge(array(
                'box_show'   => $status,
                'template'   => new Zend_Db_Expr('NULL'),
                'block'      => new Zend_Db_Expr('NULL'),
                'sort_order' => new Zend_Db_Expr('NULL')
            ), $data);
            $data = array_merge($data, array(
                'box_id'  => $row->id,
                'page_id' => $page
            ));
            $modelAssign->createRow($data)->save();
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