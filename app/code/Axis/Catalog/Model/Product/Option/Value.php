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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Model_Product_Option_Value extends Axis_Db_Table
{
    protected $_name = 'catalog_product_option_value';
    protected $_dependentTables = array('Axis_Catalog_Model_Product_Option_Value_Text');
    protected $_referenceMap    = array(
        'Option' => array(
            'columns'           => 'option_id',
            'refTableClass'     => 'Axis_Catalog_Model_Product_Option',
            'refColumns'        => 'id'
        )
    );

    /**
     * Add new or Update existing option value
     *
     * @param array $data
     * <pre>
     *  array(
     *      valueId => array(
     *          id,
     *          valueset_id,
     *          sort_order,
     *          name_langId
     *      )
     *  )
     * </pre>
     * @return bool
     */
    public function save(array $data)
    {
        $modelValueText = Axis::single('catalog/product_option_value_text');
        $languageIds = array_keys(Axis_Collect_Language::collect());

        foreach ($data as $values) {
            if (isset($values['id'])) {// update
                $this->update(array(
                    'sort_order' => $values['sort_order']
                ), 'id = ' . intval($values['id']));
                foreach ($languageIds as $langId) {
                    if (!$row = $modelValueText->find(
                            intval($values['id']),
                            intval($langId)
                        )->current()) {

                        $row = $modelValueText->createRow(array(
                            'option_value_id' => intval($values['id']),
                            'language_id' => intval($langId),
                            'name' => $values['name_' . $langId]
                        ));
                    } else {
                        $row->setFromArray(array(
                            'name' => $values['name_' . $langId]
                        ));
                    }
                    $row->save();
                }
            } else { // insert
                $values['id'] = $this->insert(array(
                    'valueset_id' => $values['valueset_id'],
                    'sort_order' => $values['sort_order'])
                );
                foreach ($languageIds as $langId) {
                    $modelValueText->insert(array(
                        'option_value_id' => $values['id'],
                        'language_id' => $langId,
                        'name'        => $values['name_' . $langId]
                    ));
                }
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return true;
    }

    public function getByText($valueText, $valuesetId)
    {
        return $this->select('*')
            ->join('catalog_product_option_value_text',
                'cpovt.option_value_id = cpov.id')
            ->where('cpov.valueset_id = ?', $valuesetId)
            ->where('cpovt.name = ?', $valueText)
            ->fetchRow3();
    }
}