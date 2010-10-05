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
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_NSTree
{
    /**
     * Axises
     */
    const AXIS_SELF                     = 0x0000;
    const AXIS_DESCENDANT               = 0x0001;
    const AXIS_CHILD                    = 0x0002;
    const AXIS_ANCESTOR                 = 0x0003;
    const AXIS_PARENT                   = 0x0004;
    const AXIS_FOLLOWING_SIBLING        = 0x0005;
    const AXIS_PRECENDING_SIBLING       = 0x0006;
    const AXIS_LEAF                     = 0x0007;
    const AXIS_DESCENDANT_OR_SELF       = 0x0008;
    const AXIS_ANCESTOR_OR_SELF         = 0x0009;
    const AXIS_CHILD_OR_SELF            = 0x0010;

    static public $AXES = array(
        self::AXIS_SELF, self::AXIS_DESCENDANT, self::AXIS_CHILD,
        self::AXIS_ANCESTOR, self::AXIS_PARENT,
        self::AXIS_FOLLOWING_SIBLING, self::AXIS_PRECENDING_SIBLING,
        self::AXIS_DESCENDANT_OR_SELF, self::AXIS_ANCESTOR_OR_SELF,
        self::AXIS_LEAF, self::AXIS_CHILD_OR_SELF
    );

    /**
     * Tree types
     */
    const TYPE_SINGLE    = "single";
    const TYPE_DOUBLE    = "double";
    const TYPE_NETWORK   = "network";

    static public $TYPES = array(self::TYPE_SINGLE, self::TYPE_DOUBLE, self::TYPE_NETWORK);

    const BEFORE    = 1;
    const AFTER     = 2;
    const AT_BEGIN  = 3;
    const AT_END    = 4;

    /**
     * Default Zend_Db_Adapter object.
     *
     * @var Zend_Db_Adapter
     */
    static protected $_defaultDb;

    /**
     * Zend_Db_Adapter object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Names of structure columns
     *
     * @var string
     */
    protected
        $_primary,
        $_left,
        $_right,
        $_level,
        $_dataPrimary,
        $_dataForeign;

    protected $_systemColumns = array();

    /**
     * Names of tables
     *
     * @var string
     */
    protected
        $_tableName,
        $_tableName2;


    /**
     * Tree type
     *
     * 3 types defined 'single', 'double', 'network'
     *
     *     single - tree structure and data are stored in ONE table
     *
     *  double - structure and data strored in SEPARATE tables
     *
     *  network - same as double, but one data record can be binded to several nodes
     *
     * @var string
     */
    protected $_type = self::TYPE_SINGLE;

    /**
     * Structure table.
     *
     * @var Axis_NSTree_Table
     */
    protected $_table;

    /**
     * Slave table
     *
     * @var Axis_NSTree_Table2
     */
    protected $_table2;

    /**
     * Boolean flag, that two tables are enabled
     *
     * @var bool
     */
    protected $_2tables = false;

    /**
     * Data table.
     * For 'single' tree it is $_table,
     * For 'double' and 'network' trees it's $_table2
     *
     * @var Zend_Db_Table
     */
    protected $_dataTable;


    public function __construct($config)
    {
        if (! empty($config['db'])) {
            // convenience variable
            $db = $config['db'];

            // use an object from the registry?
            if (is_string($db)) {
                $db = Zend_Registry::get($db);
            }

            // save the connection
            $this->_db = $db;
        }

        $this->_primary = $config['primary'];
        $this->_left    = $config['left'];
        $this->_right   = $config['right'];
        $this->_level   = $config['level'];
        $this->_systemColumns = array(
            $this->_primary => 'primary',
            $this->_left => 'left',
            $this->_right => 'right',
            $this->_level => 'level'
        );

        $this->_tableName = $config['table'];

        $this->_type    = $config['type'];
        $this->_2tables = in_array($config['type'],
            array(self::TYPE_DOUBLE , self::TYPE_NETWORK));
        if ($this->_2tables) {
            $this->_dataPrimary = $config['dataPrimary'];
            $this->_dataForeign = $config['dataForeign'];

            if ($this->_dataPrimary == $this->_primary) {
                throw new Axis_NSTree_Exception('Уникальные ключи в таблице структуры и в таблице данных должны иметь разные имена. Такое пока ограничение');
            }

            $this->_tableName2  = $config['table2'];
            $this->_systemColumns[$this->_dataForeign] = 'dataForeign';
        }

        $this->_setup();
    }


    protected function _setup()
    {
        // get the database adapter
        if (! $this->_db) {
            $this->_db = $this->_getDefaultDbAdapter();
        }

        if (! $this->_db instanceof Zend_Db_Adapter_Abstract) {
            throw new Zend_Db_Table_Exception('db object does not implement Zend_Db_Adapter_Abstract');
        }

        if (! in_array($this->_type, self::$TYPES)) {
            throw new Axis_NSTree_Exception ('задан неверный тип дерева');
        }

        $this->_table = new Axis_NSTree_Table(array(
            'name'        => $this->_tableName,
            'primary'     => $this->_primary,
            'left'        => $this->_left,
            'right'       => $this->_right,
            'level'       => $this->_level,
            'dataForeign' => $this->_dataForeign,
            'db'          => $this->_db
        ));

        if ($this->_2tables) {
            $this->_table2 = new Axis_NSTree_Table2(array(
                'name'    => $this->_tableName2,
                'primary' => $this->_dataPrimary,
                'db'      => $this->_db
            ));
        }

        $this->_dataTable = $this->_2tables ? $this->_table2 : $this->_table;
    }

    /**
     * Sets the default Zend_Db_Adapter for all Zend_Db_Table objects.
     *
     * @param Zend_Db_Adapter $db A Zend_Db_Adapter object.
     */
    static public final function setDefaultDbAdapter($db)
    {
        // make sure it's a Zend_Db_Adapter
        if (! $db instanceof Zend_Db_Adapter_Abstract) {
            throw new Axis_NSTree_Exception (
                'db object does not extend Zend_Db_Adapter_Abstract'
            );
        }
        self::$_defaultDb = $db;
    }

    /**
     * Gets the default Zend_Db_Adapter for all Zend_Db_Table objects.
     *
     */
    protected final function _getDefaultDbAdapter()
    {
        return self::$_defaultDb;
    }

    /**
     * Gets the Zend_Db_Adapter for this particular Axis_NSTree object.
     *
     * @return Zend_Db_Adapter
     */
    public final function getDbAdapter()
    {
        return $this->_db;
    }

    public final function getTable()
    {
        return $this->_table;
    }

    public final function getTable2()
    {
        if ($this->_table2) {
            return $this->_table2;
        } else {
            throw new Axis_NSTree_Exception('Not exists for this tree type.');
        }

    }

    /**
     * Clears tree.
     *
     * Important!!!
     *     this method will destroy all data in the tree
     *
     * @param array|Axis_NSTree_Node $data
     * @return int|Axis_NSTree_Node
     */
    public function clear($data = array())
    {
        // delete structure .... oops
        $this->_table->delete('');

        if ($this->_2tables) {
            // delete data ....oops
            $this->_table2->delete('');
        }

        // create root
        $columns = array(
            $this->_left     => 1,
            $this->_right    => 2,
            $this->_level    => 0
        );
        $id = $this->_table->insert($columns);

        // bind data
        return $this->_bindNode($id, $data);
    }

    /**
     * Get nodes selection
     *
     * $params
     *     depth int - depth of the selection. will be usefull for
     *                 AXIS_DESCENDANT, AXIS_ANCESTOR selects
     *
     *  slice1 int,
     *     slice2 int -select data slice, will be usefull for
     *                 AXIS_DESCENDANT, AXIS_ANCESTOR selects
     *
     * @param int $contextId
     * @param int $axis axis of the selection. see AXIS_* consts
     * @param array $params
     * @return Axis_NSTree_NodeSet
     * @throws Axis_NSTree_Exception
     */
    public function select($contextId, $axis=self::AXIS_SELF, $params=array())
    {
        $select = $this->_table->makeSelect($contextId, $axis, $params);
        if ($this->_2tables) {
            $info = $this->_table2->info();
            $cols = array();
            foreach (array_values($info['cols']) as $column) {
                $cols[] = new Zend_Db_Expr("d.$column AS $column");
            }
            $select->from(array("d" => $this->_tableName2), $cols);
            $select->where("s1.$this->_dataForeign = d.$this->_dataPrimary");
        }
        $fetched = $this->_db->fetchAll($select->__toString());

        $data = array();
        $structure = array();
        foreach ($fetched as $row) {
            $dataRow = $this->_2tables ?
                array_diff_key($row, $this->_systemColumns) : $row;
            $structRow = array();
            foreach ($this->_systemColumns as $name => $title) {
                $structRow[$title] = $row[$name];
            }

            $structure[] = $structRow;
            $data[] = $dataRow;
        }


        /**
         * Convert data
         */

        $pseudoRoot = new stdClass();
        $pseudoRoot->children = array();

        if (sizeof($structure)) {
            $levels = array();
            $levels[$structure[0]['level']-1] = $pseudoRoot;

            foreach ($structure as $i => $nodeStruct)
            {
                $node = new stdClass();
                $node->struct = $nodeStruct;
                $node->data = $data[$i];
                $node->children = array();

                $parent = $levels[$nodeStruct['level']-1];
                $parent->children[] = $node;

                $levels[$nodeStruct['level']] = $node;
            }
        }

        return new Axis_NSTree_Nodeset(array(
            'db'    => $this->_db,
            'table' => $this->_dataTable,
            'nodes' => $pseudoRoot->children
        ));
    }


    /**
     * Appends the child node to existed node, identified by $parentId
     * $pos points a position
     *     AT_END - at the end of children list
     *  AT_BEGIN - at the beginig of children list
     *
     * @param int $parentId
     * @param array|Axis_NSTree_Node $data
     * @return Axis_NSTree_Node
     */
    public function appendChild($parentId, $data, $pos = self::AT_END)
    {
        $newId = $this->_table->allocChild($parentId, $pos);
        return $this->_bindNode($newId, $data);
    }

    /**
     * Inserts the sibling node for existing node? identified by $refId.
     * Parameter $pos points a position of insertion
     *     BEFORE - before ref node
     *     AFTER - after ref node
     *
     * Inserting a node before root will throw exception
     *
     * @param int $id
     * @param array|Axis_NSTree_Node $data
     * @return Axis_NSTree_Node
     * @throws Axis_NSTree_Exception
     */
    public function insertSibling($refId, $data, $pos = self::BEFORE)
    {
        $newId = $this->_table->allocSibling($refId, $pos);
        return $this->_bindNode($newId, $data);
    }

    /**
     * Binds data to node structure
     *
     * @param int $id
     * @param array|Axis_NSTree_Node $data
     * @return Axis_NSTree_Node
     */
    protected function _bindNode($id, $data)
    {
        if ($data instanceof Axis_NSTree_Node) {
            $this->_bindNode($id, $data->toArray());
        }

        if (is_array($data) && count($data)) {
            if ($this->_2tables) {
                $dataId = $this->_table2->insert($data);
            } else {
                $dataColumns = array_diff_key($data, $this->_systemColumns);
                $this->_table->update($dataColumns,
                $this->_db->quoteInto("$this->_primary = ?", $id));
            }
        }
        elseif (is_numeric($data) && $this->_type == self::TYPE_NETWORK) {
            $dataId = $data;
        }

        // update data Id
        if (isset($dataId)) {
            $set = array($this->_dataForeign => $dataId);
            $this->_table->update($set,
                $this->_db->quoteInto($this->_primary . ' = ?', $id));
        }

        return $this->select($id)->current();
    }

    /**
     * Replace node to new parent
     *
     * @param int $id
     * @param int $newParentId
     * @return bool true on success
     * @throws Axis_NSTree_Exception
     */
    public function replaceNode($id, $newParentId)
    {
        $this->_table->replaceNode($id, $newParentId);
    }

    /**
     * Replace node before node identified by $beforeId
     *
     * @param int $id
     * @param int $beforeId
     * @return bool
     * @throws Axis_NSTree_Exception
     */
    public function replaceBefore($id, $beforeId)
    {
        $this->_table->replaceBefore($id, $beforeId);
    }

    /**
     * Delete nodes
     *
     * @param int $refId
     *
     * @param bool $deleteChildren if false descendant nodes of $refId
     *                                will be replaced to it's parent node
     *
     * @param bool $deleteData  if true for 'double' tree data will be deleted.
     *
     * @return bool
     *
     */
    public function deleteNode($refId, $deleteChildren = true, $deleteData = true)
    {
        if ($deleteData && $this->_type != self::TYPE_SINGLE) {

            $node = $this->getNode($refId);

            if (self::TYPE_DOUBLE == $this->_type) {
                // $sql not used :( @todo ?
                $sql = $this->_deleteNodeData($node, $deleteChildren);

            }
            else {
                if ($deleteChildren && $node->hasChildren()) {

                    $nodeset = $this->select($refId, self::AXIS_DESCENDANT_OR_SELF);
                    $this->_deleteChildrenData($nodeset, $node);

                } else {

                    if ( !$this->_hasSymlinks($node) ) {
                        $this->_deleteNodeData($node, $deleteChildren);
                    }

                }
            }
        }

        return $this->_table->deleteNode($refId, $deleteChildren);
    }

    /**
     * Удаляет данные узла для деревьев DOUBLE и NETWORK
     *
     * @param Axis_NSTree_Node $node узел дерева, у которого нужно удалить данные
     * @param bool $deleteChildren
     * @return Zend_Db_Statement_Interface
     */
    private function _deleteNodeData($node, $deleteChildren)
    {
        $struct = $node->getStructure();

        $sql = "
            DELETE FROM {$this->_tableName2}
            WHERE {$this->_dataPrimary} IN (
                SELECT {$this->_dataForeign}
                FROM {$this->_tableName}
                WHERE %s
            )
        ";

        if (self::TYPE_DOUBLE == $this->_type && $deleteChildren) {

            $bind = array(
                'L' => $struct['left'],
                'R' => $struct['right']
            );

            $sql = sprintf($sql, "{$this->_left} BETWEEN :L AND :R");

        } else {

            $bind = array(
                'I' => $struct['primary']
            );

            $sql = sprintf($sql, "{$this->_primary} = :I");
        }

        return $this->_db->query($sql, $bind);
    }

    /**
     * Проверяет ссылки на данные каждого узла в ветке
     * и удаляет узел, если на данные нет других ссылок
     *
     * @param Axis_NSTree_Nodeset $nodeset
     */
    private function _deleteChildrenData($nodeset, $root)
    {
        while (($node = $nodeset->current())) {

            if ( !$this->_hasSymlinks($node) ) {
                $this->_deleteNodeData($node, $root);
            }

            if ($nodeset->hasChildren()) {
                $this->_deleteChildrenData($nodeset->getChildren(), $root);
            }

            $nodeset->next();
        }
    }

    /**
     * Проверяет, имеются ли другие узлы, ссылающиеся на данные
     * предоставленного узла. Имеет смысл только для деревьев NETWORK
     *
     * @param Axis_NSTree_Node $node
     * @param Axis_NSTree_Node $root если задан узел root,
     *                                  то ссылки внутри этого узла будут игнорироваться
     * @return bool
     */
    private function _hasSymlinks($node, $root = null)
    {
        $struct = $node->getStructure();

        $where = $this->_table->getAdapter()->quoteInto("{$this->_dataForeign} = ?", $struct['dataForeign']);
        if (null !== $root) {
            $rootStruct = $root->getStructure();
            $where .= $this->_table->getAdapter()->quoteInto(" AND ({$this->_left} NOT BETWEEN ? AND ?)", $rootStruct['left'], $rootStruct['right']);
        }
        $rowset = $this->_table->fetchAll($where);

        return count($rowset) > 1;
    }


    /**
     * Creates new node
     *
     * @return Axis_NSTree_Node
     */
    public function createNode()
    {
        $info = $this->_dataTable->info();
        $keys = array_values($info['cols']);
        $vals = array_fill(0, count($keys), null);

        $node = new Axis_NSTree_Node(array(
            'db'     => $this->_db,
            'table'  => $this->_dataTable,
            'struct' => null,
            'data'   => array_combine($keys, $vals)
        ));

        return $node;
    }

    /**
     * Gets the tree root node
     *
     * @return Axis_NSTree_Node
     */
    public function getRoot()
    {
        return $this->getNode(null);
    }

    /**
     * Gets node by identy key
     *
     * @param int $id
     * @return Axis_NSTree_Node
     * @throws Axis_NSTree_Exception  Node not found
     *            Zend_Db_Exception          Data base errors
     */
    public function getNode($id)
    {
        $nodeset = $this->select($id, Axis_NSTree::AXIS_SELF);
        if (!$nodeset->count()) {
            throw new Axis_NSTree_Exception('Node not found');
        }
        return $nodeset->current();
    }

    /**
     * Gets node path from root
     *
     * @param int $id
     * @return Axis_NSTree_Nodeset
     */
    public function getNodePath($id)
    {
        return $this->select($id, Axis_NSTree::AXIS_ANCESTOR_OR_SELF);
    }

    /**
     * Gets parent node. For root node it will returns NULL
     *
     * @param int $id
     * @return Axis_NSTree_Node
     */
    public function getParentNode($id)
    {
        $nodeset = $this->select($id, Axis_NSTree::AXIS_PARENT);
        return $nodeset->current();
    }

    /**
     * Get child nodes
     *
     * @param int $id
     * @return Axis_NSTree_Nodeset
     */
    public function getChildNodes($id)
    {
        return $this->select($id, Axis_NSTree::AXIS_CHILD);
    }

    /**
     * Gets previous sibling node
     *
     * @param int $id
     * @return Axis_NSTree_Node
     */
    public function getNextSibling($id)
    {
        $nodeset = $this->select($id, Axis_NSTree::AXIS_FOLLOWING_SIBLING);
        return $nodeset->current();
    }

    /**
     * Gets next sibling node
     *
     * @param int $id
     * @return Axis_NSTree_Node
     */
    public function getPrevSibling($id)
    {
        $nodeset = $this->select($id, Axis_NSTree::AXIS_PRECENDING_SIBLING);
        return $nodeset->current();
    }
}
