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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Model_Customer_Select extends Axis_Db_Table_Select
{
    /**
     * Adds product_name column to select
     *
     * @param integer $languageId [optional]
     * @return Axis_Tag_Model_Customer_Select
     */
    public function addProductDescription($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->joinLeft(
            'catalog_product_description',
            'tp.product_id = cpd.product_id AND cpd.language_id = ' . $languageId,
            array('product_name' => 'name')
        );
    }

    /**
     * Adds customer_email and customer_id columns to select
     *
     * @return Axis_Tag_Model_Customer_Select
     */
    public function addCustomerData()
    {
        return $this->joinLeft(
            'account_customer',
            'tc.customer_id = ac.id',
            array(
                'customer_email'    => 'email',
                'customer_id'       => 'id'
            )
        );
    }
}