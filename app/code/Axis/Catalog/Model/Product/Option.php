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
 * @subpackage  Axis_Catalog_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Option extends Axis_Db_Table
{
    const TYPE_SELECT   = 0;
    const TYPE_STRING   = 1;
    const TYPE_RADIO    = 2;
    const TYPE_CHECKBOX = 3;
    const TYPE_TEXTAREA = 4;
    const TYPE_FILE     = 5;

    protected $_name = 'catalog_product_option';

    protected $_dependentTables = array(
        'Axis_Catalog_Model_Product_Option_Text',
        'Axis_Catalog_Model_Product_Option_Value',
        'Axis_Catalog_Model_Product_Attribute'
    );

    protected $_referenceMap = array(
        'ValueSet' => array(
            'columns'       => 'valueset_id',
            'refTableClass' => 'Axis_Catalog_Model_Product_Option_ValueSet',
            'refColumns'    => 'id'
        )
    );

    protected $_selectClass = 'Axis_Catalog_Model_Product_Option_Select';

    protected $_rowClass = 'Axis_Catalog_Model_Product_Option_Row';

    /**
     * Get options array by language
     *
     * @param array $params
     * @return array
     */
    public function getList($params)
    {
        $select = $this->select('*');

        if (!empty($params['languageId'])) {
            $select->joinLeft('catalog_product_option_text',
                "cpot.option_id = cpo.id AND language_id = {$params['languageId']}",
                array('name', 'description')
            );

        } else {
            $select->joinLeft('catalog_product_option_text',
                'cpot.option_id = cpo.id',
                array('name', 'description')
            );
        }

        if (!empty($params['sort']) && !empty($params['dir'])) {
            $select->order($params['sort'] . ' ' . $params['dir']);
        }
        if (!empty($params['limit'])) {
            $select->limit(
                $params['limit'], empty($params['start']) ? null : $params['start']
            );
        }
        return $select->fetchAll();
    }

    /**
     *
     * @static
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::TYPE_SELECT   => 'select',
            self::TYPE_STRING   => 'string',
            self::TYPE_RADIO    => 'radio',
            self::TYPE_CHECKBOX => 'checkbox',
            self::TYPE_TEXTAREA => 'textarea',
            self::TYPE_FILE     => 'file'
        );
    }

    /**
     *  @param  array ('key' => 'value', 'key1' => 'value1')
     *  @param  int
     *  @return array
     */
    public function getAttributesByKeyword(array $keywords, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        $query = $this->select(array('option_id' => 'id'))
            ->joinInner(
                'catalog_product_option_text',
                'cpot.option_id = cpo.id',
                array('option_name' => 'name')
            )
            ->joinInner(
                'catalog_product_option_value',
                'cpov.valueset_id = cpo.valueset_id',
                array('value_id' => 'id')
            )
            ->joinInner(
                'catalog_product_option_value_text',
                'cpovt.option_value_id = cpov.id',
                array('value_name' => 'name')
            )
            ->where('cpot.language_id = ?', $languageId)
            ->where('cpot.name IN(?)', array_keys($keywords))
            ->where('cpovt.language_id = ?', $languageId)
            ->where('cpovt.name IN(?)', array_unique(array_values($keywords)))
            ->query()
            ;

        $attributes = array();
        while ($row = $query->fetch()) {
            if (!isset($keywords[$row['option_name']])
                || $keywords[$row['option_name']] != $row['value_name']) {

                continue;
            }
            $attributes[$row['option_id']] = array(
                'value'       => $row['value_id'],
                'value_id'    => $row['value_id'],
                'seo'         => $row['option_name'] . '=' . $row['value_name'],
                'option_name' => $row['option_name'],
                'option_id'   => $row['option_id'],
                'value_name'  => $row['value_name']
            );
        }

        return $attributes;
    }

    /**
     * Update or insert product otions
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        if (!isset($data['id']) || !$row = $this->find($data['id'])->current()) {
            $row = $this->createRow();
        }
        unset($data['id']);
        $options = array(
            'comparable', 'filterable', 'searchable', 'languagable', 'visible'
        );
        foreach ($options as $option) {
            $data[$option] = (int)isset($data[$option]);
        }

        $row->setFromArray($data);
        if (empty($row->valueset_id)) {
            $row->valueset_id = new Zend_Db_Expr('NULL');
        }
        $row->save();

        return $row;
    }


    protected function _getRelations($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return $this->select(array('valueset_id' => 'id'))
            ->join('catalog_product_option_text',
                "cpot.option_id = cpo.id AND cpot.language_id = $languageId",
                array('valueset_name' => 'name'))
            ->join('catalog_product_option_value',
                'cpo.id = cpov.valueset_id'
            )
            ->join('catalog_product_option_value_text',
                "cpovt.option_value_id = cpov.id AND cpovt.language_id = $languageId",
                array('option_name' => 'name', 'option_id' => 'option_value_id')
            )
            ->fetchAll();
    }

    /**
     *
     * @param int $languageId
     * @return array
     */
    public function getValueSets($languageId = null)
    {
        $valusets = array();

        foreach ($this->_getRelations($languageId) as $item) {
            $valusets[$item['valueset_id']]['name'] = $item['valueset_name'];
            $valusets[$item['valueset_id']]['option'][$item['option_id']] = $item['option_name'];

        }
        return $valusets;
    }

    /**
     *
     * @param int $languageId
     * @return array
     */
    public function getOptions($languageId = null)
    {
        $options = array();
        foreach ($this->_getRelations($languageId) as $item) {
            $options[$item['option_id']] = array(
                'name'     => $item['option_name'],
                'valueset' => array(
                    'id'    => $item['valueset_id'],
                    'name'  => $item['valueset_name']
                )
            );
        }
        return $options;
    }
}