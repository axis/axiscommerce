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
class Axis_Core_Model_Module extends Axis_Db_Table
{
    protected $_name = 'core_module';

    protected $_rowClass = 'Axis_Core_Model_Module_Row';

    protected $_dependentTables = array(
        'Axis_Core_Model_Module_Upgrade',
    );

    private $_processed_modules = null;

    protected $_metadata = array(
        'id' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => '',
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => 1,
            'PRIMARY' => 1,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => 1
        ),

        'package' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'package',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => 64,
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => '',
            'PRIMARY' => '',
            'PRIMARY_POSITION' => '',
            'IDENTITY' => ''
        ),

        'code' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'code',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => 64,
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => '',
            'PRIMARY' => '',
            'PRIMARY_POSITION' => '',
            'IDENTITY' => ''
        ),

        'name' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'name',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => 64,
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => '',
            'PRIMARY' => '',
            'PRIMARY_POSITION' => '',
            'IDENTITY' => ''
        ),

        'version' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'version',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => 10,
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => '',
            'PRIMARY' => '',
            'PRIMARY_POSITION' => '',
            'IDENTITY' => ''
        ),

        'is_active' => array(
            'SCHEMA_NAME' => '',
            'TABLE_NAME' => 'core_module',
            'COLUMN_NAME' => 'is_active',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '',
            'NULLABLE' => '',
            'LENGTH' => '',
            'SCALE' => '',
            'PRECISION' => '',
            'UNSIGNED' => 1,
            'PRIMARY' => '',
            'PRIMARY_POSITION' => '',
            'IDENTITY' => ''
        )
    );

    public function init()
    {
        foreach ($this->_metadata as &$values) {
            $values['TABLE_NAME'] = $this->_prefix . $values['TABLE_NAME'];
        }
    }

    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Retrieve array of installed modules
     *
     * @return array
     */
    public function getList($where = null)
    {
        $select = $this->select(array('code', '*'));

        if (null !== $where) {
            $select->where($where);
        }

        return $select->fetchAssoc();
    }

    /**
     * Retrieve modules list from filesystem
     *
     * @return array
     */
    public function getListFromFilesystem($namespaceToReturn = null)
    {
        $codePath = Axis::config()->system->path . '/app/code';
        try {
            $codeDir = new DirectoryIterator($codePath);
        } catch (Exception $e) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    "Directory %s not readable", $codePath
                )
            );
        }

        $modules = array();
        foreach ($codeDir as $namespace) {
            if ($namespace->isDot() || !$namespace->isDir()) {
                continue;
            }
            $namespaceName = $namespace->getFilename();
            if (null !== $namespaceToReturn && $namespaceToReturn != $namespaceName) {
                continue;
            }
            try {
                $namespaceDir = new DirectoryIterator($namespace->getPathname());
            } catch (Exception $e) {
                continue;
            }
            foreach ($namespaceDir as $module) {
                if ($module->isDot() || !$module->isDir()) {
                    continue;
                }
                $modules[] = $namespaceName . '_' . $module->getFilename();
            }
        }
        return $modules;
    }

    /**
     * Retrieve the config of all modules, or one of module if required
     *
     * @param string $module [optional]
     * @return mixed(array|boolean)
     */
    public function getConfig($module = null)
    {
        if (null === $module) {
            if (!$result = Axis::cache()->load('axis_modules_config')) {
                $modules = Axis::app()->getModules();
                $result = array();
                foreach ($modules as $moduleCode => $path) {
                    if (file_exists($path . '/etc/config.php')
                        && is_readable($path . '/etc/config.php')) {

                        include_once($path . '/etc/config.php');
                        if (!isset($config)) {
                            continue;
                        }
                        $result += $config;
                    }
                }
                Axis::cache()->save(
                    $result, 'axis_modules_config', array('modules')
                );
            }
            $config = $result;
        } else {
            list($namespace, $module) = explode('_', $module, 2);
            $configFile = Axis::config()->system->path . '/app/code/'
                . $namespace . '/' . $module . '/etc/config.php';

            if (!is_file($configFile)) {
                return false;
            }

            include($configFile);

            if (!isset($config)) {
                return false;
            }
        }
        return $config;
    }

    /**
     * @param string $code
     * @return Axis_Core_Model_Module_Row
     */
    public function getByCode($code)
    {
        try {
            $where = $this->select()->where('code = ?', $code);
            $row = $this->fetchRow($where);
            if ($row) {
                return $row;
            }
        } catch (Exception $exc) {
            // first install, skip
        }
        return $this->createFromCode($code);
    }

    /**
     * @param string $code
     * @return Axis_Core_Model_Module_Row
     */
    public function createFromCode($code)
    {
        $row = $this->createRow(array(
            'code' => $code,
            'is_active' => 1
        ));
        $row->setFromArray($row->getConfig());
        return $row;
    }
}