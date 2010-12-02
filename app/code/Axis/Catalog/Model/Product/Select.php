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
class Axis_Catalog_Model_Product_Select extends Axis_Db_Table_Select
{
    /**
     * Calls addDescription,  addUrl, addStock, addAttributes, images methods
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addCommonFields()
    {
        return $this->addDescription()
            ->addUrl()
            ->addStock()
            ->addVariations()
            ->addAttributes();
    }

    /**
     * @param integer $languageId [optional]
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addDescription($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->joinLeft('catalog_product_description',
            'cp.id = cpd.product_id AND cpd.language_id = ' . $languageId,
            '*'
        );
    }

    public function addImages($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->joinLeft('catalog_product_image',
                'cp.id = cpi.product_id',
                array(
                    'image_id'          => 'id',
                    'image_path'        => 'path',
                    'image_sort_order'  => 'sort_order'
                )
            )
            ->joinLeft('catalog_product_image_title',
                'cpi.id = cpit.image_id AND cpit.language_id = ' . $languageId,
                array(
                    'image_title' => 'title'
                )
            );
    }

    /**
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addUrl()
    {
        return $this->joinLeft('catalog_hurl',
            "cp.id = ch.key_id AND ch.key_type = 'p'",
            array(
                'product_id' => 'ch.key_id',
                'ch.key_word'
            ));
    }

    /**
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addStock()
    {
        return $this->joinLeft('catalog_product_stock',
            'cp.id = cps.product_id',
            '*'
        );
    }

    /**
     * Add variation data to select object
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addVariations()
    {
        return $this->joinLeft('catalog_product_variation',
            'cp.id = cpv.product_id OR cpv.product_id IS NULL',
            array(
                'variation_id'          => 'id',
                'variation_price'       => 'price',
                'variation_price_type'  => 'price_type',
                'variation_sku'         => 'sku',
                'variation_quantity'    => 'quantity',
                'variation_weight'      => 'weight',
                'variation_weight_type' => 'weight_type'
            )
        );
    }

    /**
     * Add attributes data to select object
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addAttributes()
    {
        return $this->joinLeft('catalog_product_attribute',
            'cp.id = cpa.product_id AND (cpa.variation_id = 0 OR cpa.variation_id = cpv.id)',
            array(
                'option_id',
                'option_value_id',
                'attribute_price'       => 'price',
                'attribute_price_type'  => 'price_type',
                'attribute_weight'      => 'weight',
                'attribute_weight_type' => 'weight_type',
                'modifier',
                'variation_id',
                'attribute_id'          => 'id'
            )
        );
    }

    public function addManufacturer($languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->joinLeft('catalog_product_manufacturer',
                'cp.manufacturer_id = cpm.id',
                array(
                    'manufacturer_id'    => 'id',
                    'manufacturer_name'  => 'name',
                    'manufacturer_image' => 'image'
                ))
            ->joinLeft('catalog_product_manufacturer_title',
                'cpm.id = cpmt.manufacturer_id AND cpmt.language_id = ' . $languageId,
                array(
                    'manufacturer_title' => 'title'
                ))
            ->joinLeft('catalog_hurl',
                "cpm.id = ch.key_id AND ch.key_type = 'm' AND ch.site_id = " . Axis::getSiteId(),
                array(
                    'manufacturer_url' => 'key_word'
                ));
    }

    /**
     * Filter products by date_available, is_active fields and category status
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addFilterByAvailability()
    {
        $this->where(
                'cp.date_available IS NULL OR cp.date_available <= ?',
                Axis_Date::now()->toPhpString('Y-m-d')
            )
            ->where('cp.is_active = 1');

        if ($disabledCategories = Axis::single('catalog/category')->getDisabledIds()) {
            $this->where('cc.id NOT IN (?)', $disabledCategories);
        }

        return $this;
    }

    /**
     * Filter products by featured status
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addFilterByFeatured()
    {
        $today = Axis_Date::now()->toPhpString('Y-m-d');

        return $this->where('cp.featured_from IS NULL OR cp.featured_from <= ?', $today)
           ->where('cp.featured_to IS NULL OR cp.featured_to >= ?', $today)
           ->where('cp.featured_from IS NOT NULL OR cp.featured_to IS NOT NULL');
    }

    /**
     * Filter products by new status
     *
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addFilterByNew()
    {
        $today = Axis_Date::now()->toPhpString('Y-m-d');

        return $this->where('cp.new_from IS NULL OR cp.new_from <= ?', $today)
           ->where('cp.new_to IS NULL OR cp.new_to >= ?', $today)
           ->where('cp.new_from IS NOT NULL OR cp.new_to IS NOT NULL');
    }

    public function addFilterByUncategorized()
    {
        $mProductToCategory = Axis::single('catalog/product_category');
        $rootProducts = Axis::db()->quoteInto(
            'cp.id = ANY (?)',
            $mProductToCategory
                ->select('cpc.product_id')
                ->join('catalog_category', 'cc.id = cpc.category_id')
                ->where('cc.lvl = 0')
        );
        $notChildProducts = Axis::db()->quoteInto(
            'cp.id <> ALL (?)',
            $mProductToCategory
                ->select('cpc.product_id')
                ->join('catalog_category', 'cc.id = cpc.category_id')
                ->where('cc.lvl <> 0')
        );

        $this->where('cp.id <> ALL (?)', $mProductToCategory->select('cpc.product_id'))
            ->orWhere($rootProducts . ' AND ' . $notChildProducts);

        return $this;
    }

    /**
     * Apply set of filters to select
     *
     * @param array $filters
     * <pre>
     *  Accepted filters:
     *      site_ids            integer|array
     *      category_ids        integer|array
     *      manufacturer_ids    integer|array
     *      price               array(from => 0, to => 100)
     *      attributes          array(optionId => valueId, ...)
     * </pre>
     * @return Axis_Catalog_Model_Product_Select
     */
    public function addCommonFilters($filters)
    {
        $filters = array_merge(array(
            'site_ids'          => Axis::getSiteId(),
            'category_ids'      => null,
            'manufacturer_ids'  => null,
            'price'             => array(),
            'attributes'        => array()
        ), $filters);

        if ($filters['site_ids']) {
            if (is_array($filters['site_ids'])) {
                $this->where('cc.site_id IN (?)', $filters['site_ids']);
            } else {
                $this->where('cc.site_id = ?', $filters['site_ids']);
            }
        }

        if ($filters['category_ids']) {
            if (is_array($filters['category_ids'])) {
                $this->where('cc.id IN (?)', $filters['category_ids']);
            } else {
                $this->where('cc.id = ?', $filters['category_ids']);
            }
        }

        if ($filters['manufacturer_ids']) {
            if (is_array($filters['manufacturer_ids'])) {
                $this->where('cp.manufacturer_id IN (?)', $filters['manufacturer_ids']);
            } else {
                $this->where('cp.manufacturer_id = ?', $filters['manufacturer_ids']);
            }
        }

        if (isset($filters['price']['from'])) {
            $this->where('cp.price >= ?', $filters['price']['from']);
        }
        if (isset($filters['price']['to'])) {
             $this->where('cp.price <= ?', $filters['price']['to']);
        }

        if (count($filters['attributes'])) {
            $this->addFilterByAttributes($filters['attributes']);
        }

        return $this;
    }

    public function addFilterByAttributes($attributes)
    {
        $joinedAttrs = array();
        $i = 0;
        foreach ($attributes as $optionId => $valueId) {
            $this->join(array("pac$i" => 'catalog_product_attribute'),
                "pac$i.product_id = cp.id")
            ->where("pac$i.option_id = ?", $optionId)
            ->where("pac$i.option_value_id = ?", $valueId);
            if ($i > 0) {
                $where = "IF (pac$i.variation_id=0, 1, "
                            . "IF (0 < " . implode('+', $joinedAttrs) . ", pac$i.variation_id IN (" . implode(',', $joinedAttrs) . "), 1)"
                        . ")";
                $this->where($where);
            }
            $joinedAttrs[] = 'pac' . $i . '.variation_id';
            ++$i;
        }
        return $this;
    }

    public function joinCategory()
    {
        return $this->joinLeft('catalog_product_category', 'cp.id = cpc.product_id')
            ->joinLeft('catalog_category', 'cpc.category_id = cc.id');
    }

    /**
     * Returns array of products, formatted in acceptible by template form
     * Add images data to each product, fill discount prices
     *
     * @param array $orderedIds [optional] Use in case if you want to order
     *  products in the way, that is not possible to provide through query
     * @return array
     */
    public function fetchProducts(array $orderedIds = array())
    {
        $result = array();
        foreach ($this->fetchAll() as $product) {
            if (!isset($result[$product['id']])) {
                $result[$product['id']] = array();
                $result[$product['id']]['is_saleable'] = $product['in_stock'] && $product['quantity'] > 0;
                foreach ($product as $key => $value) {
                    if (in_array($key, array(
                            'variation_id',
                            'modifier',
                            'option_id',
                            'option_value_id'
                        ))
                        || strpos($key, 'variation') === 0
                        || strpos($key, 'attribute') === 0) {

                        continue;
                    }
                    $result[$product['id']][$key] = $value;
                }
            }

            if (!isset($result[$product['id']]['variation'][$product['variation_id']])) {
                $sku = $product['variation_id'] ? $product['variation_sku'] : $product['sku'];
                $quantity = $product['variation_id'] ? $product['variation_quantity'] : $product['quantity'];

                $result[$product['id']]['variation'][$product['variation_id']] = array(
                    'id'            => $product['variation_id'],
                    'price_amount'  => $product['variation_price'],
                    'price_type'    => $product['variation_price_type'],
                    'sku'           => $sku,
                    'quantity'      => $quantity,
                    'weight_amount' => $product['variation_weight'],
                    'weight_type'   => $product['variation_weight_type'],
                );

                if (!$result[$product['id']]['is_saleable']
                    && $result[$product['id']]['in_stock']) {

                    $result[$product['id']]['is_saleable'] = $quantity > 0;
                }
            }
            if ($product['variation_id']) {
                $result[$product['id']]['variation']
                        [$product['variation_id']]['option']
                            [$product['option_id']]
                                = $product['option_value_id'];
            }

            if ($product['modifier']
                && !isset($result[$product['id']]['modifier'][$product['attribute_id']])) {

                $result[$product['id']]['modifier']
                    [$product['attribute_id']] = array(
                        'option_id'         => $product['option_id'],
                        'option_value_id'   => $product['option_value_id'],
                        'price_amount'      => $product['attribute_price'],
                        'price_type'        => $product['attribute_price_type'],
                        'weight_amount'     => $product['attribute_weight'],
                        'weight_type'       => $product['attribute_weight_type'],
                    );
            }
        }

        $products = array();
        if (count($orderedIds)) {
            foreach ($orderedIds as $id) {
                if (!isset($result[$id])) {
                    continue;
                }
                $products[$id] = $result[$id];
            }
        } else {
            $products = $result;
        }

        if (!count($products)) {
            return array();
        }

        $images = Axis::single('catalog/product_image')->getList(array_keys($products));
        foreach ($images as $productId => $productImages) {
            $products[$productId]['images'] = $productImages;
        }

        Axis::single('discount/discount')->fillDiscount($products);

        $productObj = new Axis_Object(array(
            'products' => $products
        ));
        Axis::dispatch('catalog_product_array_fetch', $productObj);

        return $productObj->getProducts();
    }

    /**
     * Retrieve product list with count of products (CALC_FOUND_ROWS)
     *
     * @return array
     */
    public function fetchList()
    {
        $this->distinct()->calcFoundRows();

        if (!$ids = $this->fetchCol()) {
            return array(
                'count' => 0,
                'data'  => array()
            );
        }

        $count = $this->foundRows();

        $products = $this->reset()
            ->from('catalog_product', '*')
            ->addCommonFields()
            ->where('cp.id IN (?)', $ids)
            ->fetchProducts($ids);

        return array(
            'count' => $count,
            'data'  => $products
        );
    }
}