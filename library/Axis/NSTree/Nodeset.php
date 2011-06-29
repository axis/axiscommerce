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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_NSTree
 * @copyright   Copyright 2008-2011 Axis
 * @copyright   Marat Komarov <bassguitarrer@gmail.com>
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_NSTree
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_NSTree_Nodeset implements RecursiveIterator
{
    const NODES = 'nodes';
    const TABLE = 'table';

    protected $_nodes;

    protected $_db;

    protected $_table;

    /**
     * Iterator pointer.
     */
    protected $_pointer = 0;

    protected $_count;

    protected $_rows = array();

    public function __construct($config = array())
    {
        if (isset($config['db'])) {

            // convenience variable
            $db = $config['db'];

            // use an object from the registry?
            if (is_string($db)) {
                $db = Zend_Registry::get($db);
            }

            // save the connection
            $this->_db = $db;
        }

        if (isset($config[self::NODES]))
        {
            $this->_nodes = $config[self::NODES];
        }
        if (isset($config[self::TABLE]))
        {
            $this->_table = $config[self::TABLE];
        }

        $this->_count = count($this->_nodes);
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     *
     * @return mixed current element from the collection
     */
    public function current()
    {
        // is the pointer at a valid position?
        if (! $this->valid()) {
            return false;
//            throw new Axis_NSTree_Node_Exception('The tree\'s pointer out of date.');
        }

        // do we already have a row object for this position?
        if ( empty($this->_rows[$this->_pointer]) ) {
            $node = $this->_nodes[$this->_pointer];

            // create a row object
            $this->_rows[$this->_pointer] = new Axis_NSTree_Node(array(
                'db'    => $this->_db,
                'table' => $this->_table,
                'struct'=> $node->struct,
                'data'  => $node->data,
                'stored' => true
            ));
        }

        // return the row object
        return $this->_rows[$this->_pointer];
    }

    public function key()
    {
        return $this->_pointer;
    }

    public function next()
    {
        return ++$this->_pointer;
    }

    public function count()
    {
        return $this->_count;
    }

    public function exists()
    {
        return $this->_count > 0;
    }

    public function valid()
    {
        return $this->_pointer < $this->count();
    }

    public function rewind()
    {
        $this->_pointer = 0;
    }

    public function hasChildren()
    {
        return !empty($this->_nodes[$this->_pointer]->children);
    }

    public function getChildren()
    {
        return new Axis_NSTree_Nodeset(array(
            'db' => $this->_db,
            'table' => $this->_table,
            'nodes' => $this->_nodes[$this->_pointer]->children
        ));
    }
}