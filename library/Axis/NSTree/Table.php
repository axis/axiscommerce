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
 * @copyright   Copyright 2008-2010 Axis
 * @copyright   Marat Komarov <bassguitarrer@gmail.com>
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  NSTree
 * @author      Axis Core Team <core@axiscommerce.com>
 */

class Axis_NSTree_Table extends Axis_Catalog_Model_Category
{
    const LEFT         = 'left';
    const RIGHT        = 'right';
    const LEVEL        = 'level';
    const TREEID       = 'treeId';
    const DATA_FOREIGN = 'dataForeign';

    protected $_level = 'lvl';

    protected $_left = 'lft';

    protected $_right = 'rgt';
    
    protected $_treeId = 'site_id';

    protected $_dataForeign;

    public function __construct($config = array())
    {
        if (isset($config[self::LEFT]))
        {
            $this->_left = $config[self::LEFT];
        }
        if (isset($config[self::RIGHT]))
        {
            $this->_right = $config[self::RIGHT];
        }
        if (isset($config[self::LEVEL]))
        {
            $this->_level = $config[self::LEVEL];
        }
        if (isset($config[self::DATA_FOREIGN]))
        {
            $this->_dataForeign	= $config[self::DATA_FOREIGN];
        }

        parent::__construct($config);
    }


    /**
     * Выборка системных полей узла.
     *
     * Системные это primary, left, right, level, dataForeign?
     *
     * @param int $id
     * @param int $axis
     * @param array $params
     * @return Zend_Db_Select
     */
    public function makeSelect($id, $axis, $params=array())
    {
        $select = $this->_db->select();

        $info = $this->info();
        $cols = array();

        if (is_array($this->_primary)) {
            $pkIdentity = $this->_primary[(int)$this->_identity];
        } else {
            $pkIdentity = $this->_primary;
        }

        foreach (array_values($info['cols']) as $column) {
            $cols[] = new Zend_Db_Expr("s1.$column AS $column");
        }

        $select->from(array('s1' => $this->_prefix . 'catalog_category'), $cols);
        if ($axis != Axis_NSTree::AXIS_SELF) {
            $select->from(array('s2' => $this->_prefix . 'catalog_category'), array());
        }


        $indentCond = 's%d.%s = ?';
        $indentValue = $id ? $id : '1';
        $indentCond = sprintf($indentCond,
            $axis == Axis_NSTree::AXIS_SELF ? '1' : '2',
            $id ? $pkIdentity : $this->_left
        );

        $select->where($indentCond, $indentValue);


        switch ($axis) {
            case Axis_NSTree::AXIS_CHILD:
            case Axis_NSTree::AXIS_CHILD_OR_SELF:
            case Axis_NSTree::AXIS_LEAF:
            case Axis_NSTree::AXIS_DESCENDANT:
            case Axis_NSTree::AXIS_DESCENDANT_OR_SELF:

                if ($axis == Axis_NSTree::AXIS_CHILD) {
                    $select->where("s1.{$this->_level} = s2.{$this->_level} + 1");
                }

                if ($axis == Axis_NSTree::AXIS_CHILD_OR_SELF) {
                    $select->where("s1.{$this->_level} - s2.{$this->_level} <= ?", "1");
                }

                if ($axis == Axis_NSTree::AXIS_LEAF) {
                    $select->where("s1.{$this->_left} = s1.{$this->_right} - 1");
                }

                if ($axis == Axis_NSTree::AXIS_DESCENDANT_OR_SELF ||
                $axis == Axis_NSTree::AXIS_CHILD_OR_SELF) {
                    $select->where("(s1.{$this->_left} BETWEEN s2.{$this->_left} AND s2.{$this->_right})");
                }
                else {
                    $select->where("s1.{$this->_left} > s2.{$this->_left} AND s1.{$this->_right} < s2.{$this->_right}");
                }

                break;

            case Axis_NSTree::AXIS_PARENT:
                $select->where("s1.{$this->_level} = s2.{$this->_level} - 1");
                // брейка нет

            case Axis_NSTree::AXIS_ANCESTOR:
            case Axis_NSTree::AXIS_ANCESTOR_OR_SELF:
                if ($axis == Axis_NSTree::AXIS_ANCESTOR_OR_SELF) {
                    $select->where("s1.{$this->_left} <= s2.{$this->_left}");
                    $select->where("s1.{$this->_right} >= s2.{$this->_right}");
                }
                else {
                    $select->where("s1.{$this->_left} < s2.{$this->_left}");
                    $select->where("s1.{$this->_right} > s2.{$this->_right}");
                }

                break;

            case Axis_NSTree::AXIS_FOLLOWING_SIBLING:
            case Axis_NSTree::AXIS_PRECENDING_SIBLING:
                if ($info = $this->getParentInfo($id)) {
                    $select->where("s2.{$this->_level} = s1.{$this->_level}");
                    $select->where("s1.{$this->_left} > ?", $info[$this->_left]);
                    $select->where("s1.{$this->_right} < ?", $info[$this->_right]);

                    if ($axis == Axis_NSTree::AXIS_FOLLOWING_SIBLING) {
                        $select->where("s1.{$this->_left} > s2.{$this->_right}");
                    }
                    else {
                        $select->where("s1.{$this->_right} < s2.{$this->_left}");
                    }
                }
                else {
                    throw new Axis_NSTree_Exception('parent node was not found');
                    return;
                }

                break;

        }

        // Обрабатываем дополнительные параметры ...
        if (isset($params["depth"]) &&
            in_array( $axis, array(Axis_NSTree::AXIS_ANCESTOR,
            Axis_NSTree::AXIS_ANCESTOR_OR_SELF,
            Axis_NSTree::AXIS_DESCENDANT,
            Axis_NSTree::AXIS_DESCENDANT_OR_SELF))
        )
        {

            $depth = abs($params["depth"]);

            if (in_array ($axis, array(Axis_NSTree::AXIS_DESCENDANT,
                Axis_NSTree::AXIS_DESCENDANT_OR_SELF))) {

                $select->where("s1.{$this->_level} <= s2.{$this->_level} + ?", $depth);
            }
            else {
                $select->where("s1.{$this->_level} >= s2.{$this->_level} - ?", $depth);
            }
        }
        elseif (isset($params['slice1']) && $params['slice2'])
        {
            $slice1 = abs($params["slice1"]);
            $slice2 = abs($params["slice2"]);

            if ($slice2 < $slice1) {
                throw new Axis_NSTree_Exception('параметр slice2 должен быть больше slice1');
            }
            else {
                $ax_desc = in_array ($axis, array(Axis_NStree::AXIS_DESCENDANT,
                    Axis_NSTree::AXIS_DESCENDANT_OR_SELF));
                $select->where("( "
                    . "s1.{$this->_level} BETWEEN "
                    . "s2.{$this->_level} " . ($ax_desc ? '+'.$slice1 : '-'.$slice2) . " AND "
                    . "s2.{$this->_level} " . ($ax_desc ? '+'.$slice2 : '-'.$slice1)
                    . " )"
                );
            }
        }

        $select->order(new Zend_Db_Expr("s1.{$this->_left}"));

        return $select;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getNodeInfo($id)
    {
        $select = $this->makeSelect($id, Axis_NSTree::AXIS_SELF);
        return $this->_db->fetchRow($select->__toString());
    }

    /**
     * @param int $id
     * @return array
     */
    public function getParentInfo($id)
    {
        $select = $this->makeSelect($id, Axis_NSTree::AXIS_PARENT);
        return $this->_db->fetchRow($select->__toString());
    }

    /**
     * Резервирует место в дереве для дочернего узла
     *
     * @param int $id
     * @return int Уникальный ключ нового узла
     */
    public function allocChild($id, $pos)
    {
        $p = $this->getNodeInfo($id);

        if ($pos == Axis_NSTree::AT_BEGIN) {
            $v = $p[$this->_left];
            $left = $p[$this->_left] + 1;
            $right = $p[$this->_left] + 2;
        }
        elseif ($pos == Axis_NSTree::AT_END) {
            $v = $p[$this->_right];
            $left = $p[$this->_right];
            $right = $p[$this->_right] + 1;
        }
        else {
            throw new Axis_NSTree_Exception("invalid node position '$pos'");
        }

        $sql = "
            UPDATE " . $this->_prefix . 'catalog_category' . "
            SET
                left  =
                    CASE
                        WHEN left > :v THEN left + 2
                        ELSE left
                    END,
                right =
                    CASE
                        WHEN right >= :v THEN right + 2
                        ELSE right
                    END
                WHERE treeId = :tId AND
                right >= :v
        ";
        $sql = $this->_inflectQuery($sql);
        $this->_db->query($sql, array("v" => $v, 'tId' => $p[$this->_treeId]));

        $columns = array(
            $this->_left     => $left,
            $this->_right    => $right,
            $this->_level    => $p[$this->_level] + 1
        );
        return $this->insert($columns);
    }

    /**
     * Резервирует место в дереве для братского узла
     *
     * @param int $id
     * @param int $pos Axis_NSTree::BEFORE, Axis_NSTree::AFTER
     * @return int
     */
    public function allocSibling($id, $pos)
    {
        $n = $this->getNodeInfo($id);

        if ($pos == Axis_NSTree::BEFORE) {
            $v = $n[$this->_left];
            $left = $n[$this->_left];
            $right = $n[$this->_left] + 1;
        }
        elseif ($pos == Axis_NSTree::AFTER) {
            $v = $n[$this->_right];
            $left = $n[$this->_right] + 1;
            $right = $n[$this->_right] + 2;
        }
        else {
            throw new Axis_NSTree_Exception("invalid node position '$pos'");
        }

        $sql = "
             UPDATE " . $this->_prefix . 'catalog_category' . "
            SET
                left =
                    CASE
                    WHEN left >= :v THEN
                        left + 2
                    ELSE
                        left
                    END,
                right =
                    CASE
                    WHEN right >= :v THEN
                        right + 2
                    ELSE
                        right
                    END
            WHERE
                treeId = :tId AND
                right > :v
        ";
        $sql = $this->_inflectQuery($sql);
        $this->_db->query($sql, array('v' => $v, 'tId' => $n[$this->_treeId]));

        $columns = array(
            $this->_left    => $left,
            $this->_right    => $right,
            $this->_level    => $n[$this->_level]
        );
        return $this->insert($columns);

    }

    /**
     * Перемещение узла к новому родителю
     *
     * @param int $id
     * @param int $newParentId
     * @return bool
     */
    public function replaceNode($id, $newParentId)
    {
        if ($id == $newParentId) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Node cannot be inserted to itself'
            ));
            return false;
        }

        $n = $this->getNodeInfo($id);
        $p = $this->getNodeInfo($newParentId);
        
        $holders = array(
            'L_n' => $n[$this->_left],
            'R_n' => $n[$this->_right],
            'V_n' => $n[$this->_level],
            'L_p' => $p[$this->_left],
            'R_p' => $p[$this->_right],
            'V_p' => $p[$this->_level],
            'tId' => $n[$this->_treeId]
        );

        if ($p[$this->_left] < $n[$this->_left] &&
        $p[$this->_right] > $n[$this->_right]) {
            // intersection
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_p - 1)
                        ELSE
                            level
                        END,
                    right =
                        CASE
                        WHEN right BETWEEN :R_n+1 AND :R_p-1 THEN
                            right - (:R_n -:L_n + 1)
                        ELSE
                            CASE
                            WHEN left BETWEEN :L_n AND :R_n THEN
                                right + (:R_p - :R_n - 1)
                            ELSE
                                right
                            END
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :R_n+1 AND :R_p-1 THEN
                            left - (:R_n - :L_n + 1)
                        ELSE
                            CASE
                            WHEN left BETWEEN :L_n AND :R_n THEN
                               left + (:R_p - :R_n - 1)
                            ELSE
                                left
                            END
                        END
                WHERE
                    treeId = :tId AND
                    (left BETWEEN :L_p+1 AND :R_p-1
                        OR
                    left BETWEEN :L_p+1 AND :R_p-1)
            ";
        }
        elseif ($n[$this->_left] > $p[$this->_right]) {
            // after
            $sql =  "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_p - 1)
                        ELSE
                            level
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :R_p AND (:L_n-1) THEN
                            left + (:R_n-:L_n+1)
                        ELSE
                            CASE
                            WHEN left BETWEEN :L_n AND :R_n THEN
                                left-(:L_n-:R_p)
                            ELSE
                                left
                            END
                        END,
                    right =
                        CASE
                        WHEN right BETWEEN :R_p AND :L_n THEN
                            right + (:R_n - :L_n +1)
                        ELSE
                            CASE
                            WHEN right BETWEEN :L_n AND :R_n THEN
                                right-(:L_n-:R_p)
                            ELSE
                                right
                            END
                        END
                WHERE
                    treeId = :tId AND
                    (left BETWEEN :L_p AND :R_n
                        OR
                    right BETWEEN :L_p AND :R_n)
            ";
        }
        elseif ($n[$this->_right] < $p[$this->_left]) {
            // before
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_p - 1)
                        ELSE
                            level
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :R_n AND :R_p THEN
                            left - (:R_n - :L_n + 1)
                        ELSE
                            CASE
                            WHEN left BETWEEN :L_n AND :R_n THEN
                                left+(:R_p-1-:R_n)
                            ELSE
                                left
                            END
                        END,
                    right =
                        CASE
                        WHEN right BETWEEN (:R_n+1) AND (:R_p-1) THEN
                            right-(:R_n-:L_n+1)
                        ELSE
                            CASE
                            WHEN right BETWEEN :L_n AND :R_n THEN
                                right+(:R_p-1-:R_n)
                            ELSE
                                right
                            END
                        END
                WHERE
                    treeId = :tId AND
                    (left BETWEEN :L_n AND :R_p
                        OR
                    right BETWEEN :L_n AND :R_p)
            ";
        } else {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Node cannot be placed here'
            ));
            return false;
        }
        
        $sql = $this->_inflectQuery($sql);
        $sql = str_replace( // @todo replace with normal sql bind
            array(':L_n', ':R_n', ':V_n', ':L_p', ':R_p', ':V_p', ':tId'),
            array_values($holders),
            $sql);
        // why this don't works? -> $this->_db->query($sql, $holders);
        $this->_db->query($sql);
        return true;
    }

    /**
     * Переместить узел перед указанным
     *
     * @param int $id
     * @param int $beforeId
     * @return bool
     */
    public function replaceBefore($id, $beforeId)
    {
        $n = $this->getNodeInfo($id);
        $b = $this->getNodeInfo($beforeId);

        if ($b[$this->_left] == 1) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Node cannot be placed before root'
            ));
            return false;
        }

        $holders = array(
            'L_n' => $n[$this->_left],
            'R_n' => $n[$this->_right],
            'V_n' => $n[$this->_level],
            'L_b' => $b[$this->_left],
            'R_b' => $b[$this->_right],
            'V_b' => $b[$this->_level],
            'tId' => $n[$this->_treeId]
        );

        if ($n[$this->_left] > $b[$this->_left] &&
        $n[$this->_right] < $b[$this->_right]) {
            // intersection
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_b)
                        ELSE
                            level
                        END,
                    right =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            right - (:L_n - :L_b)
                        WHEN right BETWEEN :L_b AND :L_n-1 THEN
                            right + (:R_n - :L_n + 1)
                        ELSE
                            right
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            left - (:L_n - :L_b)
                        WHEN left BETWEEN :L_b AND :L_n-1 THEN
                            left + (:R_n - :L_n + 1)
                        ELSE
                            left
                        END
                WHERE
                    treeId = :tId AND
                    left BETWEEN :L_b AND :R_b
            ";
        }
        elseif ($n[$this->_right] < $b[$this->_left]) {
            // before
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_b)
                        ELSE
                            level
                        END,
                    right =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            right + (:L_b - :R_n - 1)
                        WHEN right BETWEEN :R_n+1 AND :L_b-1 THEN
                            right - (:R_n - :L_n + 1)
                        ELSE
                            right
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            left + (:L_b - :R_n - 1)
                        WHEN left BETWEEN :R_n+1 AND :L_b-1 THEN
                            left - (:R_n - :L_n + 1)
                        ELSE
                            left
                        END
                WHERE
                    treeId = :tId AND
                    (left BETWEEN :L_n AND :L_b-1
                        OR
                    right BETWEEN :L_n AND :L_b-1)
            ";
        }
        elseif ($n[$this->_left] > $b[$this->_right]) {
            // after
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            level - (:V_n - :V_b)
                        ELSE
                            level
                        END,
                    right =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            right - (:L_n - :L_b)
                        WHEN right BETWEEN :L_b AND :L_n-1 THEN
                            right + (:R_n - :L_n + 1)
                        ELSE
                            right
                        END,
                    left =
                        CASE
                        WHEN left BETWEEN :L_n AND :R_n THEN
                            left - (:L_n - :L_b)
                        WHEN left BETWEEN :L_b AND :L_n-1 THEN
                            left + (:R_n - :L_n + 1)
                        ELSE
                            left
                        END
                WHERE
                    treeId = :tId AND
                    (left BETWEEN :L_b AND :R_n
                        OR
                    right BETWEEN :L_b AND :R_n)
            ";
        }
        else {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Node cannot be placed here'
            ));
            return false;
        }

        $sql = $this->_inflectQuery($sql);
        $sql = str_replace( // @todo replace with normal sql bind
            array(':L_n', ':R_n', ':V_n', ':L_b', ':R_b', ':V_b', ':tId'),
            array_values($holders),
            $sql);
        // why this don't works? -> $this->_db->query($sql, $holders);
        $this->_db->query($sql);
        return true;
    }

    /**
     * Удаление узла
     *
     * @param int $id
     * @param bool $removeChilds
     * @return bool
     */
    public function deleteNode($id, $removeChilds)
    {
        if (is_array($this->_primary)) {
            $pkIdentity = $this->_primary[(int)$this->_identity];
        } else {
            $pkIdentity = $this->_primary;
        }

        $n = $this->getNodeInfo($id);

        $holders = array(
            'L' => $n[$this->_left],
            'R' => $n[$this->_right],
            'tId' => $n[$this->_treeId]
        );

        if ($removeChilds) {
            // delete node and his children
            $sql = "DELETE FROM  " . $this->_prefix . 'catalog_category'
            . " WHERE $this->_treeId = :tId AND $this->_left BETWEEN :L AND :R";
            $this->_db->query($sql, $holders);

            // clear blank spaces in a tree
            $holders['D'] = $n[$this->_right] - $n[$this->_left] + 1;
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    left =
                        CASE
                        WHEN left > :L THEN
                            left - :D
                        ELSE
                            left
                        END,
                    right =
                        CASE
                        WHEN right > :L THEN
                            right - :D
                        ELSE
                            right
                        END
                WHERE
                    treeId = :tId AND
                    right > :R
            ";
            $sql = $this->_inflectQuery($sql);
            return $this->_db->query($sql, $holders);
        }
        else {
            // delete node structure
            $where = $this->_db->quoteInto("{$pkIdentity} = ?", $n[$pkIdentity]);
            $this->delete($where);

            // replace children to node parent
            $sql = "
                UPDATE " . $this->_prefix . 'catalog_category' . "
                SET
                    level =
                        CASE
                        WHEN left BETWEEN :L AND :R THEN
                            level-1
                        ELSE
                            level
                        END,
                  left =
                        CASE
                        WHEN left BETWEEN :L AND :R THEN
                            left - 1
                        WHEN left > :R THEN
                            left - 2
                        ELSE
                            left
                        END,
                    right =
                        CASE
                        WHEN right BETWEEN :L AND :R-1 THEN
                            right - 1
                        WHEN right >= :R THEN
                            right - 2
                        ELSE
                            right
                        END
                WHERE
                    treeId = :tId AND right > :L
            ";
            $sql = $this->_inflectQuery($sql);
            return $this->_db->query($sql, $holders);
        }
    }

    protected function _inflectQuery($sql)
    {
        $regexp = '/[^\:](left|right|level|treeId|primary|dataForeign)/';
        return preg_replace_callback($regexp, array($this, '_inflectCallback'), $sql);
    }

    protected function _inflectCallback($matches)
    {
        $column = "_{$matches[1]}";
        return str_replace($matches[1], $this->$column, $matches[0]);
    }
}