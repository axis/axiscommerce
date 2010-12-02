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
 * @subpackage  Axis_Db_Table_Select
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table_Select
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Db_Table_Select_Disassemble
{
    protected $_joinKeys = array(//'from',
        'join'         => 'join',
        'inner join'   => 'joinInner',
        'right join'   => 'joinRight',
        'full join'    => 'joinFull',
        'cross join'   => 'joinCross',
        'natural join' => 'joinNatural',
        'left join'    => 'joinLeft'
    );
    protected $_parts = array();
    protected $_tables = array();
    protected $_columns = array();

    protected function _isJoin($string)
    {
        return in_array($string, array_keys($this->_joinKeys));
    }

    protected function _tableReferance($string)
    {
        $matches = array();
        preg_match(
            '/^(?:(\w+)\.)?(\w+)(?:\sAS)?(?:\s(\w+))?(?:\sON\s(.+))?$/',
            $string,
            $matches
        );

        return array(
            'expr' => $string,
            'scheme'      => empty($matches[1]) ? null : $matches[1],
            'tableName'   => $matches[2],
            'correlation' => empty($matches[3]) ? null : $matches[3],
            'condition'   => empty($matches[4]) ? null : $matches[4]
        );
    }

    protected function _tableCorrelation($expresion)
    {
        if (null === $expresion) {
            $expresion = $this->_tables[0]['tableName'];
        }
        foreach ($this->_tables as $table) {
            if ($table['tableName'] !== $expresion) {
                continue;
            }
            if (null !== $table['correlation'])  {
                return $table['correlation'];
            }
            return $table['tableName'];
        }
        return $expresion;
    }

    protected function _prepareQuery($query)
    {
        $r = array(
            '/\s+/'    => ' ',
            '/\r/'     => '',
            '/\n/'     => '',
            '/\t/'     => '',
            '/\sas\s/' => ' AS ',
        );
        $query = preg_replace(array_keys($r), array_values($r), trim($query));
        $regex = '/[\s]*\b(' .
            'SELECT|' .
            'FROM|' .
            'INNER\s+JOIN|' .
            'LEFT\s+JOIN|' .
            'RIGHT\s+JOIN|' .
            'FULL\s+JOIN|' .
            'CROSS\s+JOIN|' .
            'NATURAL\s+JOIN|' .
            'JOIN|' .
            'WHERE|' .
            'GROUP\s+BY|' .
            'HAVING|' .
            'ORDER\s+BY|' .
            'LIMIT' .
            ')[\s]+/i';

        $match = preg_split($regex, $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        array_shift($match);
        $count = count($match);
        $parts = array();
        for ($i = 0; $i < $count; $i++) {
            $key = strtolower($match[$i]);
            $value = $match[++$i];
            if ('from' !== $key && !$this->_isJoin($key)) {
                $parts[$key] = $value;
                continue;
            }
            $parts[$key][] = $value;
            $table = $this->_tableReferance($value);
            $table['type'] = $key;
            $this->_tables[] = $table;


        }
        $this->_parts = $parts;

        ////////////////////////////////////////////////////////////////////////
        $select = $this->_parts['select'];
        //$select = preg_replace('/\s/', '', $select);
        foreach( explode(',', $select)  as $expr) {
            $column = null;
            $tableExpr = $expr;
            if (strstr($expr, '.')) {
                list($tableExpr, $column) = explode('.', $expr);
            }

            if (null === $column) {
                $column = $tableExpr;
                $tableExpr = null;
            }
            if (null !== $tableExpr) {
                $tableExpr = trim($tableExpr);
            }
            $column = trim($column);
            $correlation = null;
            if (strstr($column, ' AS ')) {
                list($column, $correlation) = explode(' AS ', $column);
            }

            if (null === $correlation) {
                $columns[$this->_tableCorrelation($tableExpr)][] = $column;
            } else {
                $columns[$this->_tableCorrelation($tableExpr)][$correlation] = $column;
            }
        }
        $this->_columns = $columns;

        return $parts;
    }

    public function getTables()
    {
        return $this->_tables;
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    protected function _replaceLongTableName($string)
    {
        foreach ($this->getTables() as $table) {
            if (null === $table['correlation']) {
                continue;
            }
            $string = str_replace(
                $table['tableName'] . '.', $table['correlation'] . '.', $string
            );
        }
        return $string;
    }

    protected function _getTableNameString(array $table)
    {
        return (null === $table['correlation']) ? "'{$table['tableName']}'" :
            "array('{$table['correlation']}' => '{$table['tableName']}')";

    }

    protected function _getColumnsString(array $table)
     {

        $columns = array();
        if (isset($this->_columns[$table['tableName']])) {
            $columns = $this->_columns[$table['tableName']];
        } elseif (isset($this->_columns[$table['correlation']])) {
            $columns = $this->_columns[$table['correlation']];
        }
        $columnsStr = '';
        if (count($columns)) {
            $columnsAsArray = count($columns) > 1 ? true : false;
            foreach ($columns as $correlation => $column) {
                if (is_string($correlation)) {
                    $columnsAsArray = true;
                    $columnsStr .= ", '{$correlation}' => '{$column}'";
                } else {
                    $columnsStr .= ", '{$column}'";
                }
            }
            $columnsStr = trim(ltrim($columnsStr, ','));
            if ($columnsAsArray && !empty($columnsStr)) {
                $columnsStr = "array({$columnsStr})";
            }
        }
        return empty($columnsStr) ?
            (isset($table['schema']) && null !== $table['schema'] ? ", array()" : '')
                : ', ' . $columnsStr;
    }

    protected function _getSchemaString(array $table)
    {
        return !isset($table['schema']) || null === $table['schema'] ?
            '' : ', ' . $table['schema'];
    }

    protected function _getConditionString(array $table)
    {
        $condition = $this->_replaceLongTableName($table['condition']);
        return ", '{$condition}'";
    }

    protected function _addJoin($table) {

        $tableNameStr = $this->_getTableNameString($table);
        $columnsStr = $this->_getColumnsString($table);
        $schemaStr = $this->_getSchemaString($table);
        if ('from' === $table['type']) {
            return "\r\t->from(" . $tableNameStr . $columnsStr . $schemaStr . ')';
        }
        $conditionStr = $this->_getConditionString($table);

        return "\r\t->" .
            $this->_joinKeys[$table['type']] . '(' .
            $tableNameStr .
            $conditionStr .
            $columnsStr .
            $schemaStr .
            ')' ;
    }

    protected function _addWhere($where)
    {
        $result = '';

        $where = 'AND ' . $where;

        $replacement = true;
        while ($replacement) {

            $replacement = false;
            $parts = preg_split(
                '/(\(.+\)+)/U', $where, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );

            foreach ($parts as $part) {
                if (!preg_match('/\(.+?\)/', $part)) {
                    continue;
                }
                if (strstr($part, ' AND ') || strstr($part, ' OR ')) {
                    continue;
                }
                $replacement = preg_replace('/^\s*\((.+)\)\s*$/', '$1', $part);
                $where = str_replace($part, $replacement, $where);
            }
        }

        $parts = preg_split(
            '/(\(.*\))/U', $where, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        foreach ($parts as $part) {
            if (preg_match('/^\(.*\)$/', $part)) {
                $replacement = str_replace(
                    array('AND', 'OR'), array('AND--', 'OR--'), $part
                );
                $where = str_replace($part, $replacement, $where);
            }
        }
        $parts = preg_split(
            '/(AND|OR)\s/', $where, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $type = 'where';
        for ($i = 0 ; $i < count($parts) ; $i++) {
            if ('OR' == $parts[$i]) {
                $type = 'orWhere';
            } else {
                $type = 'where';
            }
//            $subQuery = str_replace('--', '', trim($parts[++$i],'() '));
            $subQuery = preg_replace('/^\s*\((.+)\)\s*$/', '$1', $parts[++$i]);
            $subQuery = str_replace('--', '', $subQuery);
            $result .= "\r\t" . "->{$type}(\""
                    . $this->_replaceLongTableName(trim($subQuery)) . '")';
        }
        return $result;
    }

    //http://dev.mysql.com/doc/refman/4.1/en/select.html
    //http://dev.mysql.com/doc/refman/4.1/en/join.html
     /*
         SELECT
    [ALL | DISTINCT | DISTINCTROW ]
      [HIGH_PRIORITY]
      [STRAIGHT_JOIN]
      [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
      [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
    select_expr [, select_expr ...]
    [FROM table_references
    [WHERE where_condition]
    [GROUP BY {col_name | expr | position}
      [ASC | DESC], ... [WITH ROLLUP]]
    [HAVING where_condition]
    [ORDER BY {col_name | expr | position}
      [ASC | DESC], ...]
    [LIMIT {[offset,] row_count | row_count OFFSET offset}]
    [PROCEDURE procedure_name(argument_list)]
    [INTO OUTFILE 'file_name' export_options
      | INTO DUMPFILE 'file_name'
      | INTO @var_name [, @var_name]]
    [FOR UPDATE | LOCK IN SHARE MODE]]
         */
    public function  __construct($query)
    {
        $this->_prepareQuery($query);
    }

    public function __toString()
    {
        $result = '$this->select()';
        foreach ($this->getTables() as $table) {
            $result .= $this->_addJoin($table);
        }
        $parts = $this->_parts;
        if (isset($parts['where'])) {
            $result .= $this->_addWhere($parts['where']);
        }

        if (isset($parts['group by'])) {
            $groups = explode(',', $parts['group by']);
            foreach ($groups as $group) {
                $result .= "\r\t" . '->group("'
                        . $this->_replaceLongTableName(trim($group)) . '")';
            }
        }

        if (isset($parts['having'])) {
            $result .= "\r\t" . '->having("'
                    . $this->_replaceLongTableName($parts['having']) . '")';
        }

        if (isset($parts['order by'])) {
            $orders = explode(',', $parts['order by']);
            foreach ($orders as $order) {
                $result .= "\r\t" . '->order("'
                        . $this->_replaceLongTableName(trim($order)) . '")';
            }
        }
        if (isset($parts['limit'])) {
            list($limit, $offset) = explode(' OFFSET ', $parts['limit']);
            $result .= "\r\t" . '->limit(' . trim($limit) .
                (null === $offset ? '' : ', ' . $offset)   . ')';
        }
        return  $result;
    }
}
