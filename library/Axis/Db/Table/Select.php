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
class Axis_Db_Table_Select extends Zend_Db_Table_Select
{
    const SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';

    /**
     * Table integrity override.
     *
     * @var bool
     */
    protected $_integrityCheck = false;

    /**
     *
     * @var bool
     */
    protected $_useCorrelationName = true;

    /**
     * Class constructor
     *
     * @param Zend_Db_Table_Abstract $adapter
     */
    public function __construct(Zend_Db_Table_Abstract $table)
    {
        self::$_partsInit = array_merge(array(
            self::SQL_CALC_FOUND_ROWS => false
        ), self::$_partsInit);

        parent::__construct($table);
    }

    /**
     *
     * @param bool $flag
     * @return Axis_Db_Table_Select Fluent interface
     */
    public function calcFoundRows($flag = true)
    {
        $this->_parts[self::SQL_CALC_FOUND_ROWS] = (bool) $flag;
        return $this;
    }

    /**
     * Render SQL_CALC_FOUND_ROWS clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderSql_calc_found_rows($sql)
    {
        if ($this->_parts[self::SQL_CALC_FOUND_ROWS]) {
            $sql .= ' ' . self::SQL_CALC_FOUND_ROWS;
        }

        return $sql;
    }

    /**
     * use carefully: FOUND_ROWS
     * Example:
     * <code>
     * <?php
     * $select->calcFoundRows();
     * $rowset = $select->fetchAll();
     * $count = $select->foundRows();
     * ?>
     * </code>
     *
     * @return int|bool
     */
    public function foundRows()
    {
        if (true === $this->_parts[self::SQL_CALC_FOUND_ROWS]) {
            return (int) $this->getAdapter()->fetchOne('SELECT FOUND_ROWS()');
        }
        return false;
    }


    /**
     * use carefully: FOUND_ROWS
     *
     * @param string $column
     * @return int
     */
    public function count($column = Zend_Db_Table_Select::SQL_WILDCARD)
    {
        if (true === $this->_parts[self::SQL_CALC_FOUND_ROWS]) {
            return (int) $this->getAdapter()->fetchOne('SELECT FOUND_ROWS()');
        }
        // dirty count hack
        $this->calcFoundRows(false);
        $this->assemble();
        $this->_parts[self::COLUMNS] = array();
        $column = $this->getAdapter()->quoteInto('COUNT(?)', $column);
        $this->columns($column);
        return (int) $this->getAdapter()->fetchOne($this);
    }

    /**
     * Sets the use auto add table correlation name flag.
     *
     * @param bool $flag
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function setUseCorrelationName($flag = true)
    {
        $this->_useCorrelationName = $flag;
        return $this;
    }

    /**
     * Generate a short table name
     *
     * @param string $name A qualified identifier.
     * @return string unique correlation table name.
     */
    private function _getCorrelationTableName($name)
    {
        if(false === function_exists('_addCounter')) {
            function _addCounter($name, $keys, $i = 1) {
                $returnName = $i > 1 ? $name . $i : $name;
                if (!in_array($returnName, $keys)) {

                    return $returnName;
                }
                return _addCounter($name, $keys, ++$i);
            }
        }
        $parts = explode('_', $name);
        $shortName = '';
        foreach ($parts as $part) {
            $shortName .= $part[0];
        }
        return _addCounter($shortName, array_keys($this->_parts[self::FROM]));
    }

    /**
     * Populate the {@link $_parts} 'join' key
     *
     * Does the dirty work of populating the join key.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  null|string $type Type of join; inner, left, and null are currently supported
     * @param  array|string|Zend_Db_Expr $name Table name
     * @param  string $cond Join on this condition
     * @param  array|string $cols The columns to select from the joined table
     * @param  string $schema The database name to specify, if any.
     * @return Axis_Db_Table_Select This Axis_Db_Table_Select object
     */
    protected function _join($type, $name, $cond, $cols, $schema = null)
    {
//        if ($type != self::FROM && count($this->_parts[self::FROM])) {
//            throw new Zend_Db_Select_Exception("");
//        }

        if (true === $this->_useCorrelationName && !is_array($name)) {
            $name = array($this->_getCorrelationTableName($name) => $name);
        }
        $prefix = $this->_info[Axis_Db_Table_Abstract::PREFIX];
        if (is_array($name)) {
            foreach ($name as $_correlationName => $_tableName) {
                $tableName = $prefix . $_tableName;
                $correlationName = $_correlationName;
                break;
            }
            $name = array($correlationName => $tableName);
        } else {
            $name = $prefix . $name;
        }
        return parent::_join($type, $name, $cond, $cols, $schema);
    }

    /**
     * Adds a JOIN table and columns to the query.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Axis_Db_Table_Select This Axis_Db_Table_Select object.
     */
    public function join($name, $cond, $cols = array(), $schema = null)
    {
        return $this->joinInner($name, $cond, $cols, $schema);
    }

    /**
     * Add a RIGHT OUTER JOIN table and colums to the query.
     * Right outer join is the complement of left outer join.
     * All rows from the right operand table are included,
     * matching rows from the left operand table included,
     * and the columns from the left operand table are filled
     * with NULLs if no row exists matching the right table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Axis_Db_Table_Select This Axis_Db_Table_Select object.
     */
    public function joinRight($name, $cond, $cols = array(), $schema = null)
    {
        return $this->_join(self::RIGHT_JOIN, $name, $cond, $cols, $schema);
    }

    /**
     * Add a LEFT OUTER JOIN table and colums to the query
     * All rows from the left operand table are included,
     * matching rows from the right operand table included,
     * and the columns from the right operand table are filled
     * with NULLs if no row exists matching the left table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Axis_Db_Table_Select
     */
    public function joinLeft($name, $cond, $cols = array(), $schema = null)
    {
        return $this->_join(self::LEFT_JOIN, $name, $cond, $cols, $schema);
    }

    /**
     * Add an INNER JOIN table and colums to the query
     * Rows in both tables are matched according to the expression
     * in the $cond argument.  The result set is comprised
     * of all cases where rows from the left table match
     * rows from the right table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinInner($name, $cond, $cols = array(), $schema = null)
    {
        return $this->_join(self::INNER_JOIN, $name, $cond, $cols, $schema);
    }

    /**
     * Adds a FROM table and optional columns to the query.
     *
     * The table name can be expressed
     *
     * @param  array|string|Zend_Db_Expr|Zend_Db_Table_Abstract $name The table name or an
                                                                      associative array relating
                                                                      table name to correlation
                                                                      name.
     * @param  array|string|Zend_Db_Expr $cols The columns to select from this table.
     * @param  string $schema The schema name to specify, if any.
     * @return Axis_Db_Table_Select This Axis_Db_Table_Select object.
     */
    public function from(
        $name, $cols = Zend_Db_Table_Select::SQL_WILDCARD, $schema = null)
    {
        parent::from($name, $cols, $schema);

//        $from = $this->_parts[self::FROM];
//        if (1 < count($from)) {
//            $keys = array_keys($from);
//            $this->_parts[self::FROM] =
//                array(array_pop($keys) => array_pop($from)) + $from;
//        }

        return $this;
    }

    /**
     * Performs a validation on the select query before passing back to the parent class.
     * Ensures that only columns from the primary Zend_Db_Table are returned in the result.
     *
     * @return string|null This object as a SELECT string (or null if a string cannot be produced)
     */
    public function assemble()
    {
        $fields  = $this->getPart(Zend_Db_Table_Select::COLUMNS);
        $primary = $this->_info[Zend_Db_Table_Abstract::NAME];
        $schema  = $this->_info[Zend_Db_Table_Abstract::SCHEMA];

        if (count($this->_parts[Zend_Db_Table_Select::UNION]) == 0) {

            // If no fields are specified we assume all fields from primary table
            if (!count($fields)) {

                $prefix  = $this->_info[Axis_Db_Table_Abstract::PREFIX];
                $shortPrimary = substr($primary, strlen($prefix));

                $this->from(
                    $shortPrimary, Zend_Db_Table_Select::SQL_WILDCARD, $schema
                );
                $fields = $this->getPart(Zend_Db_Table_Select::COLUMNS);
            }

            $from = $this->getPart(Zend_Db_Table_Select::FROM);

            if ($this->_integrityCheck !== false) {
                foreach ($fields as $columnEntry) {
                    list($table, $column) = $columnEntry;

                    // Check each column to ensure it only references the primary table
                    if ($column) {
                        if (!isset($from[$table])
                            || $from[$table]['tableName'] != $primary) {
                            require_once 'Zend/Db/Table/Select/Exception.php';
                            throw new Zend_Db_Table_Select_Exception(
                                'Select query cannot join with another table'
                            );
                        }
                    }
                }
            }
        }

        return parent::assemble();
    }

    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($bind = array(), $fetchMode = null)
    {
        $this->bind($bind);
        return $this->getAdapter()->fetchAll($this, $this->getBind(), $fetchMode);
    }

    /**
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchRow($bind = array(), $fetchMode = null)
    {
        //todo also find
//        if (null === $fetchMode && true !== $this->_integrityCheck) {
//            return $this->getTable()->fetchRow($this);
//        }
        $this->bind($bind);
        return $this->getAdapter()->fetchRow($this, $this->getBind(), $fetchMode);
    }

    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchAssoc($bind = array())
    {
        $this->bind($bind);
        return $this->getAdapter()->fetchAssoc($this, $this->getBind());
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * The first column in each row is used as the array key.
     *
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($bind = array())
    {
        $this->bind($bind);
        return $this->getAdapter()->fetchCol($this, $this->getBind());
    }

    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchPairs($bind = array())
    {
        $this->bind($bind);
        return $this->getAdapter()->fetchPairs($this, $this->getBind());
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return string
     */
    public function fetchOne($bind = array())
    {
        $this->bind($bind);
        return $this->getAdapter()->fetchOne($this, $this->getBind());
    }

    /**
     *  Debug function
     *
     * @return Axis_Db_Table_Select Fluent
     */
    public function firephp()
    {
        if ('development' === Axis::app()->getEnvironment()) {
            Axis_FirePhp::log($this->__toString());
        }
        return $this;
    }

    /**
     * Set bind variables
     *
     * @param mixed $bind
     * @return Zend_Db_Select
     */
    public function bind($bind)
    {
        if (!empty($bind)) {
            if (!is_array($bind)) {
                $bind = array($bind);
            }
            parent::bind($bind);
        }
        return $this;
    }

    /**
     * Add filters to select. Calls addFilter method inside a loop
     *
     * @param array $filters
     * <pre>
     *  array(
     *      0 => array(
     *          field       => table_column
     *          value       => column_value
     *          operator    => =|>|<|IN|LIKE    [optional]
     *          table       => table_correlation[optional]
     *      )
     *  )
     * </pre>
     * @return Axis_Db_Table_Select
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            if (empty($filter['operator'])) {
                $filter['operator'] = '=';
            }
            if (!isset($filter['table'])) {
                $filter['table'] = null;
            }
            $this->addFilter(
                $filter['field'],
                $filter['value'],
                $filter['operator'],
                $filter['table']
            );
        }

        return $this;
    }

    /**
     * Add filter to select
     *
     * Example of usage:
     * <code>
     *  $result = Axis::model('catalog/product')
     *      ->select(array('id', 'is_active'))
     *      ->addFilter('cp.sku', 'Forrest Gump')
     *      ->fetchPairs();
     * </code>
     *
     * @param string $column        Table column name to filter.
     *                              Can be passed with correlation name: cp.sku
     * @param mixed  $value         Column value to search for.
     *                              If array is passed, operator will be automatically setted to 'IN'
     * @param string $operator      [optional] Mysql operator to compare value
     * @param string $table         [optional] Table correlation name
     * @return Axis_Db_Table_Select
     */
    public function addFilter($column, $value, $operator = '=', $table = null)
    {
        $dot = '.';
        if (null === $table) {
            if (strstr($column, '.')) {
                $dot   = '';
                $table = '';
            } else {
                $table = key($this->getPart(Zend_Db_Select::FROM));
                if (empty($table)) {
                    $dot = '';
                }
            }
        } else if (empty($table)) {
            $dot = '';
        }

        if (null === $operator) {
            $operator = '=';
        } else {
            $operator = strtoupper($operator);
        }

        if (is_array($value) && 'NOT IN' != $operator) {
            $operator   = 'IN';
            $bind       = '(?)';
        } else {
            switch ($operator) {
                case 'IN':
                case 'NOT IN':
                    $bind = '(?)';
                    break;
                case 'LIKE':
                    $value = '%' . $value . '%';
                    $bind = '?';
                    break;
                default:
                    $bind = '?';
                    break;
            }
        }

        return $this->where(
            "{$table}{$dot}{$column} {$operator} {$bind}", $value
        );
    }
}