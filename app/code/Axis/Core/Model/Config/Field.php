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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Field extends Axis_Db_Table
{
    protected $_name = 'core_config_field';
    protected $_rowClass = 'Axis_Core_Model_Config_Field_Row';

    /**
     *
     * @param string $key
     * @param int $siteId[optional]
     * @return array
     */
    public function getFieldsByKey($key, $siteId = 1)
    {
        $hasCache =  (bool) Zend_Registry::isRegistered('cache') ?
            Axis::cache() instanceof Zend_Cache_Core : false;

        if (!$hasCache
            || !$fields = Axis::cache()->load("config_{$key}_site_{$siteId}")) {

            $fields = $this->select(array('path', 'config_type', 'model'))
                ->joinInner(
                    'core_config_value',
                    'ccv.config_field_id = ccf.id',
                    'value'
                )
                ->where('ccf.path LIKE ?', $key . '/%')
                ->where('ccv.site_id IN(?)', array(0, $siteId))
                ->fetchAssoc()
                ;

            if ($hasCache) {
                Axis::cache()->save(
                    $fields, "config_{$key}_site_{$siteId}", array('config')
                );
            }
        }
        return $fields;
    }

    /**
     * Insert or update config field
     *
     * @param array $data
     * @return bool
     */
    public function save(array $data = null)
    {
        if (null === $data['path'] || $data['path'] == '') {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Incorrect field path'
            ));
            return false;
        }
        if ($data['config_options'] == '') {
            $data['config_options'] = new Zend_Db_Expr('NULL');
        }

        $data['lvl'] = count(explode('/', $data['path']));

        if ($data['lvl'] <= 2) {
            $data['config_type'] = '';
        }

        $row = $this->fetchRow(
            $this->getAdapter()->quoteInto(
                'path = ?', $data['path']
            )
        );

        if (null === $row) {
            $row = $this->createRow($data);
        } else {
            $row->setFromArray($data);
        }
        $row->save();
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }

    /**
     * Removes config field, and all of it childrens
     * Provide fluent interface
     * @param string $path
     * @return bool
     */
    public function remove($path)
    {
        $this->delete("path LIKE '{$path}%'");
        Axis::single('core/config_value')->remove($path);
        return $this;
    }

    /**
     * Add config field row, and config value,
     * if $data['path'] has third level of config
     *
     * @param string path 'root'|'root/branch/config_field'
     * @param string title 'root'|'root/branch/config_field'
     * @param string $value Config field value
     * @param string $type 'bool|multiple|string|select|text|handler'
     * @param string $description Config field description
     * @param array $data
     *  model => '',
     *  model_assigned_with => '',
     *  config_options = 'red,blue,green',
     * @return Axis_Core_Model_Config_Field Provides fluent interface
     */
    public function add(
            $path,
            $title,
            $value = '',
            $type = 'string',
            $description = '',
            $data = array())
    {
        $configEntries = explode('/', $path);
        $checkBeforeInsert = true;
        $title = explode('/', $title);

        if (is_array($description)) {
            $data = $description;
            $description = '';
        }

        $row = array('lvl' => 0);
        foreach ($configEntries as $configEntry) {
            if (++$row['lvl'] == 1) {
                $row['path'] = $configEntry;
            } else {
                $row['path'] .= '/' . $configEntry;
            }

            $row['title'] = isset($title[$row['lvl']-1]) ?
                $title[$row['lvl']-1] : $title[0];

            $row = array_merge(array(
                'config_type' => 'string',
                'description' => '',
                'model'       => isset($data['model']) ? $data['model'] : '',
                'model_assigned_with' => isset($data['model_assigned_with']) ?
                    $data['model_assigned_with'] : '',
                'translation_module' => isset($data['translation_module']) ?
                    $data['translation_module'] : new Zend_Db_Expr('NULL')
            ), $row);

            if ($row['lvl'] == 3) {
                $row['config_type'] = $type;
                $row['description'] = $description;
                $row = array_merge($data, $row);
            }

            if ($checkBeforeInsert) {
                if ($rowField = $this->fetchRow(
                        $this->getAdapter()->quoteInto(
                            'path = ?', $row['path']
                        )
                )) {
                    continue;
                } else {
                    $checkBeforeInsert = false;
                }
            }
            $rowField = $this->createRow($row);
            $rowField->save();
        }

        if ($row['lvl'] == 3) {
            $rowValue = Axis::single('core/config_value')->fetchRow(
                $this->getAdapter()->quoteInto(
                    'path = ?', $row['path']
                )
            );
            if (!$rowValue) {
                $rowValue = Axis::single('core/config_value')->createRow();
            }
            if ($row['config_type'] == 'handler') {
                $class = 'Axis_Config_Handler_' . ucfirst($row['model']);
                $value = call_user_func(
                    array($class, 'getSaveValue'), $value
                );
            }
            $rowValue->setFromArray(array(
                'config_field_id' => $rowField->id,
                'path'            => $row['path'],
                'site_id'         => 0,
                'value'           => $value
            ));
            $rowValue->save();
        }

        return $this;
    }

    /**
     * Return nodes on Ext.tree format
     *
     * @param string $node
     * @return array
     */
    public function getNodes($node)
    {
        if ('0' == $node) {
            $nodes = $this->fetchAll('lvl = 1', 'title ASC');
        } else {
            $nodes = $this->fetchAll(
                array('lvl = 2', "`path` like '$node/%'"), 'title ASC'
            );
        }

        $i = 0;
        foreach ($nodes as $item) {
            $result[$i] = array(
                'text' => Axis::translate($item->getTranslationModule())
                    ->__($item->title),
                'id'   => $item->path,
                'leaf' => false
            );
            if ($node != '0') {
                $result[$i]['children'] = array();
                $result[$i]['expanded'] = true;
            }
            ++$i;
        }
        return $result;
    }
}