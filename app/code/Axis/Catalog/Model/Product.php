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
class Axis_Catalog_Model_Product extends Axis_Db_Table
{
    protected $_name = 'catalog_product';

    protected $_rowClass = 'Axis_Catalog_Model_Product_Row';

    protected $_selectClass = 'Axis_Catalog_Model_Product_Select';

    protected $_dependentTables = array(
        'Axis_Catalog_Model_Product_Description',
        'Axis_Catalog_Model_Product_Variation',
        'Axis_Catalog_Model_Product_Attribute',
        'Axis_Catalog_Model_Product_Stock',
        'Axis_Catalog_Model_Product_Image'
    );

    /**
     * Update or insert product.
     * Returns last saved product
     *
     * @param array $data
     * @param int $siteId
     * @return Axis_Catalog_Model_Product_Row
     */
    public function save($data)
    {
        foreach ($data as $id => $values) {
            $isExist = $this->select()
                ->where('sku = ?', $values['sku'])
                ->where('id <> ?', $id)
                ->fetchOne();
            if ($isExist) {
                throw new Axis_Exception(
                    Axis::translate('catalog')->__(
                        'Product sku must be unique value'
                    )
                );
            }

            $values['new_from'] = empty($values['new_from']) ?
                new Zend_Db_Expr('NULL') : $values['new_from'];
            $values['new_to'] = empty($values['new_to']) ?
                new Zend_Db_Expr('NULL') : $values['new_to'];
            $values['featured_from'] = empty($values['featured_from']) ?
                new Zend_Db_Expr('NULL') : $values['featured_from'];
            $values['featured_to'] = empty($values['featured_to']) ?
                new Zend_Db_Expr('NULL') : $values['featured_to'];
            $values['cost'] = empty($values['cost']) ?
                0 : $values['cost'];

            if (!$id || !$row = $this->find($id)->current()) {
                unset($id);
                $row = $this->createRow();
                $row->created_on = Axis_Date::now()->toSQLString();
                $row->modified_on = new Zend_Db_Expr('NULL');
            } else {
                $row->modified_on = Axis_Date::now()->toSQLString();
            }
            if (empty($values['weight'])) {
                $values['weight'] = 0;
            }
            $row->setFromArray($values);
            $row->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__('Data was saved successfully')
        );
        return $row;
    }

    /**
     * Retrieve product row by url
     *
     * @param string $url
     * @return Axis_Catalog_Model_Product_Row
     */
    public function getByUrl($url, $siteId = null)
    {
        if (null === $siteId) {
            $siteId = Axis::getSiteId();
        }

        return $this->select()
            ->setIntegrityCheck(true)
            ->from('catalog_product')
            ->join('catalog_hurl', 'cp.id = ch.key_id')
            ->where('ch.key_type = ?', 'p')
            ->where('ch.site_id = ?', $siteId)
            ->where('ch.key_word = ?', $url)
            ->fetchRow();
    }
}