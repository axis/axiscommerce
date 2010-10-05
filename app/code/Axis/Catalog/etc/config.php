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
 * @license     GNU Public License V3.0
 */

$config = array(
    'Axis_Catalog' => array(
        'package' => 'Axis_Catalog',
        'name' => 'Catalog',
        'version' => '0.2.3',
        'required' => 1,
        'events' => array(
            'catalog_product_update_stock' => array(
                'notify' => array(
                    'type' => 'model',
                    'model' => 'catalog/observer',
                    'method' => 'notifyStockUpdate'
                )
            ),
            'catalog_product_update_quantity' => array(
                'notify' => array(
                    'type' => 'model',
                    'model' => 'catalog/observer',
                    'method' => 'notifyQuantityUpdate'
                )
            ),
            'locale_language_delete' => array(
                'clear_property_values' => array(
                    'type' => 'model',
                    'model' => 'catalog/product_attribute_value',
                    'method' => 'removeByLanguage'
                )
            )
        )
    )
);