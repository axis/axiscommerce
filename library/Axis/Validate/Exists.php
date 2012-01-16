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
 * @package     Axis_Validate
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Validate
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Validate_Exists extends Zend_Validate_Abstract
{
    /**
     * @var Zend_Db_Table_Abstract
     */
    private $_model;

    private $_where = null;

    /**
     * Field for check
     *
     * @var string
     */
    private $_field;

    private $_not = false;

    public function __construct($model, $field, $where = null, $not = false)
    {
        $this->_model = $model;
        $this->_field = $field;
        $this->_where = $where;
        if ($not) {
            $this->_not = $not;
        }
    }

    public function isValid($value)
    {
        $db = $this->_model->getAdapter();
        $where = $db->quoteInto($this->_field . ' = ?', $value);
        if (null !== $this->_where) {
            $where .= " AND {$this->_where}";
        }
        $rows = $this->_model->fetchAll($where);

        if (count($rows)) {
            $this->_messages[] = Axis::translate('core')->__(
                "Record %s already exist", $value
            );
            return $this->_not;
        }
        $this->_messages[] = Axis::translate('core')->__(
            "Record %s doesn't found", $value
        );
        return !$this->_not;
    }
}