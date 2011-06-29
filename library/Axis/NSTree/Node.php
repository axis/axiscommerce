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
class Axis_NSTree_Node extends Zend_Db_Table_Row
{
    const STRUCT = 'struct';

    protected $_struct;

    public function __construct($config)
    {
        if (isset($config[self::STRUCT]))
        {
            $this->_struct = $config[self::STRUCT];
        }
        parent::__construct($config);
    }

    public function getPrimary()
    {
        return $this->_struct['primary'];
    }

    public function getDataPrimary()
    {
        $info = $this->_table->info();
        return $info['primary'];
    }

    public function getStructure()
    {
        return $this->_struct;
    }

    public function getLevel()
    {
        return $this->_struct['level'];
    }

    public function isRoot()
    {
        return (0 == $this->getLevel());
    }

    public function isLeaf()
    {
        return !$this->hasChildren();
    }

    public function hasChildren()
    {
        return $this->_struct['right'] - $this->_struct['left'] > 1;
    }

    // interface like Zend_Db_Table_Row

    public function save()
    {
        if (!isset($this->_struct)) {
            throw new Axis_NSTree_Node_Exception(
                'Операция save() для узлов присутствующих в дереве. ' .
                'Предварительно вставьте узел appendChild() или insertSibling()'
            );
        }

        parent::save();
    }
}
