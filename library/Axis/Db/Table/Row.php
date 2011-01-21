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
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
//    /**
//     * @var array
//     */
//    protected $_dataTypes = array(
//        'bit'       => 'int',
//        'tinyint'   => 'int',
//        'bool'      => 'bool',
//        'boolean'   => 'bool',
//        'smallint'  => 'int',
//        'mediumint' => 'int',
//        'int'       => 'int',
//        'integer'   => 'int',
//        'bigint'    => 'float',
//        'serial'    => 'int',
//        'float'     => 'float',
//        'double'    => 'float',
//        'decimal'   => 'float',
//        'dec'       => 'float',
//        'fixed'     => 'float',
//        'year'      => 'int'
//    );

    /**
     *
     * @var string
     */
    protected $_prefix;

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->_prefix = $this->getTable()->info(Axis_Db_Table::PREFIX);

//        // auto type converting
//        $cols = $this->getTable()->info(Zend_Db_Table_Abstract::METADATA);
//        foreach ($cols as $name => $col) {
//            $dataType = strtolower($col['DATA_TYPE']);
//            if (array_key_exists($dataType, $this->_dataTypes)) {
//                settype($this->_data[$name], $this->_dataTypes[$dataType]);
//            }
//        }
    }

    /**
     * Sets all data in the row from an array.
     *
     * @param  array $data
     * @return Axis_Db_Table_Row Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        foreach ($this->getTable()->info('cols') as $fieldName) {
            if (isset($data[$fieldName])) {
                $this->$fieldName = $data[$fieldName];
            }
        }
        return $this;
    }

    /**
     * Returns the table object, or null if this is disconnected row
     *
     * @return Zend_Db_Table_Abstract|null
     */
    public function getTable()
    {
        $table = $this->_table;
        if (null === $table && !empty($this->_tableClass)) {
            $tableClass = $this->_tableClass;
            $table = Axis::single($tableClass);
            $this->setTable($table);
        }
        return $table;
    }

    /**
     * Retrun current datebase adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->getTable()->getAdapter();
    }

    /**
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save()
    {
        try {
            return parent::save();
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            return false;
        }
    }

    /**
     * @return Axis_Db_Table_Row
     */
    public function cache()
    {
        $frontend = Axis::single('Axis_Cache_Frontend_Query');

        $primaryKeys = array();
        foreach ($this->_primary as $primary) {
            $primaryKeys[$primary] = $this->{$primary};
        }

        $args = func_get_args();
        $args = array_merge($primaryKeys, $args);

        return $frontend->setInstance($this, serialize($args));
    }
}