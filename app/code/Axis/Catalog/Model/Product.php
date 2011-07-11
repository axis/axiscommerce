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
     * @return Axis_Catalog_Model_Product_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);

        //before save
        $isExist = (bool) $this->select()
            ->where('sku = ?', $row->sku)
            ->where('id <> ?', (int)$row->id)
            ->fetchOne();

        if ($isExist) {
            throw new Axis_Exception(
                Axis::translate('catalog')->__(
                    'Product sku must be unique value'
                )
            );
        }

        if (empty($row->new_from)) {
            $row->new_from = new Zend_Db_Expr('NULL');
        }
        if (empty($row->new_to)) {
            $row->new_to = new Zend_Db_Expr('NULL');
        }
        if (empty($row->featured_from)) {
            $row->featured_from = new Zend_Db_Expr('NULL');
        }
        if (empty($row->featured_to)) {
            $row->featured_to = new Zend_Db_Expr('NULL');
        }
        if (empty($row->weight)) {
            $row->weight = 0;
        }
        if (empty($row->cost)) {
            $row->cost = 0;
        }
        $row->modified_on = Axis_Date::now()->toSQLString();
        if (empty($row->created_on)) {
            $row->created_on = $row->modified_on;
        }

        $row->save();

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