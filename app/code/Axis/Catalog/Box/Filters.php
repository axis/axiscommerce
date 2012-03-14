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
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Box_Filters extends Axis_Core_Box_Abstract
{
    protected $_title = 'Filters';
    protected $_class = 'box-filter';
    public $hurl;

    protected function _beforeRender()
    {
        $this->hurl = Axis_HumanUri::getInstance();

        $filterSet = array();
        $filters = $this->_getActiveFilters();

        $filterSet['category']  = $this->_getCategoryFilters($filters);
        $filterSet['price']     = $this->_getPriceFilters($filters);
        if (!$this->hurl->hasParam('manufacturer')) {
            $filterSet['manufacturer'] = $this->_getManufacturerFilters($filters);
        }

        // Attribute filters
        $filterSet['attributes'] = $this->_getAttributeFilters($filters);

        if ((count($filters) && !$this->hurl->hasParam('cat'))
            || (1 < count($filters) && $this->hurl->hasParam('cat'))) { // we don't show categories in filters

            $this->filters = $filterSet;
            return true;
        }

        foreach($filterSet as $filter) {
            if (count($filter)) {
                $this->filters = $filterSet;
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve array of applied filters
     * @return array
     */
    protected function _getActiveFilters()
    {
        $filters = array();
        if ($this->hurl->hasParam('cat')) {
            $filters['category_ids'] = $this->hurl->getParamValue('cat');
        }

        // Filter by manufacturer
        if ($this->hurl->hasParam('manufacturer')) {
            $filters['manufacturer_ids'] = $this->hurl->getParamValue('manufacturer');
        }

        // by price
        if ($this->hurl->hasParam('price')) {
            $price = explode(',', $this->hurl->getParam('price'));
            $filters['price'] = array(
                'from'  => $price[0],
                'to'    => !empty($price[1]) ? $price[1] : null
            );
        }

        // by attributes
        if ($this->hurl->hasParam('attributes')) {
            $filters['attributes'] = $this->hurl->getAttributeIds();
        }

        return $filters;
    }

    /**
     * @param  array $filters
     * @return mixed
     */
    protected function _getCategoryFilters(array $filters)
    {
        $categoryIds    = array();
        $mCategory      = Axis::model('catalog/category');
        if (!empty($filters['category_ids'])) {
            $categoryIds = $filters['category_ids'];
            unset($filters['category_ids']);
        } else {
            $categoryIds = array($mCategory->getRoot()->id);
        }

        $select = Axis::single('catalog/product')->select(array(
                'cnt' => 'COUNT(DISTINCT cp.id)'
            ))
            ->joinInner('catalog_product_category', 'cp.id = cpc.product_id')
            ->joinInner('catalog_category', 'cpc.category_id = cc.id')
            ->joinInner('catalog_category_description', 'ccd.category_id = cc.id', '*')
            ->joinInner('catalog_hurl', 'ch.key_id = cc.id AND ch.key_type = "c"', 'key_word')
            ->addCommonFilters($filters)
            ->addFilterByAvailability()
            ->group('cc.id')
            ->order('cc.lft')
            ->where('ccd.language_id = ?', Axis_Locale::getLanguageId())
            ->where('ch.site_id = ?', Axis::getSiteId());

        $categories = $mCategory->find($categoryIds);
        $where = array();
        foreach ($categories as $category) {
            $where[] = "(cc.lft > {$category->lft} AND cc.rgt < {$category->rgt})";
        }
        $select->where(implode(' OR ', $where))
            ->where('cc.lvl = ?', $categories->rewind()->current()->lvl + 1);

        $categories = $select->fetchAll();

        if (!count($categories)) {
            return null;
        }

        return $categories;
    }

    /**
     * @param  array $filters
     * @return mixed
     */
    protected function _getManufacturerFilters(array $filters)
    {
        $select = Axis::single('catalog/product')->select(array(
                'cnt' => 'COUNT(DISTINCT cp.id)'
            ))
            ->joinCategory()
            ->addFilterByAvailability()
            ->addCommonFilters($filters)
            ->addManufacturer()
            ->group('cpm.id')
            ->order('cpmd.title')
            ->where('cp.manufacturer_id IS NOT NULL');

        $manufacturers = $select->fetchAll();

        if (!count($manufacturers)) {
            return null;
        }

        return $manufacturers;
    }

    /**
     * Возвращает список фильтрова для атрибутов
     *
     * @return mixed
     */
    protected function _getAttributeFilters(array $filters)
    {
        $languageId = Axis_Locale::getLanguageId();
        $select = Axis::single('catalog/product')->select(array(
                'cnt' => 'COUNT(DISTINCT cp.id)'
            ))
            ->joinCategory()
            ->addCommonFilters($filters)
            ->addFilterByAvailability()
            ->joinInner(
                'catalog_product_attribute',
                'cp.id = cpa.product_id',
                array(
                    'option_id',
                    'option_value_id'
                )
            )
            ->joinInner('catalog_product_option', 'cpa.option_id = cpo.id')
            ->joinInner(
                'catalog_product_option_text',
                'cpa.option_id = cpot.option_id',
                array(
                    'option_name' => 'name'
                )
            )
            ->joinInner(
                'catalog_product_option_value',
                'cpa.option_value_id = cpov.id'
            )
            ->joinInner(
                'catalog_product_option_value_text',
                'cpov.id = cpovt.option_value_id',
                array(
                    'value_name' => 'name'
                )
            )
            ->where('cpo.filterable = 1')
            ->where('cpot.language_id = ?', $languageId)
            ->where('cpovt.language_id = ?', $languageId)
            ->order(array(
                'cpo.sort_order ASC',
                'cpot.name ASC',
                'cpov.sort_order ASC',
                'cpovt.name ASC'
            ))
            ->group(array('cpa.option_id', 'cpa.option_value_id'));

        if (isset($filters['attributes']) && count($filters['attributes'])) {
            $select->where('cpa.option_id NOT IN (?)', array_keys($filters['attributes']));
        }

        $attributes = $select->fetchAll();
        if (!count($attributes)) {
            return null;
        }

        $filters = array();
        foreach ($attributes as $attribute) {
            if (!isset($filters[$attribute['option_id']])) {
                $filters[$attribute['option_id']] = array();
            }

            $filters[$attribute['option_id']][] = $attribute;
        }
        return $filters;
    }

    /**
     * Return price filters
     *
     * @return mixed
     */
    protected function _getPriceFilters(array $filters)
    {
        $row = Axis::single('catalog/product')->select(
               array('cnt' => 'COUNT(cp.price)')
            )
            ->joinPriceIndex()
            ->columns(array(
                'price_max' => 'MAX(cppi.final_max_price)',
                'price_min' => 'MIN(cppi.final_min_price)'
            ))
            ->joinCategory()
            ->addFilterByAvailability()
            ->addCommonFilters($filters)
            ->fetchRow();

        if (!$row->cnt) {
            return null;
        }

        $currency = Axis::single('locale/currency');
        $row->price_max = $currency->to($row->price_max);
        $row->price_min = $row->price_min > 0 ? $currency->to($row->price_min) : 1;
        $rate = $currency->getData(null, 'rate');

        //Return rounded number, example: 80->10, 120->100, 895->100, 1024->1000
        $roundTo = pow(10, strlen((string) floor($row->price_max - $row->price_min)) - 1);
        $priceGroups = Axis::single('catalog/product')->select(array(
                'cnt'         => 'COUNT(DISTINCT cp.id)',
                'price_group' => new Zend_Db_Expr("floor(cppi.final_min_price * $rate / $roundTo) * $roundTo")
            ))
            ->joinPriceIndex()
            ->joinCategory()
            ->addFilterByAvailability()
            ->addCommonFilters($filters)
            ->group('price_group')
            ->order('cppi.final_min_price')
            ->fetchAll();

        if (count($priceGroups) < 2) {
            return null;
        }

        return array(
            'roundTo' => $roundTo,
            'groups'  => $priceGroups
        );
    }

    protected function _getCacheKeyInfo()
    {
        $this->hurl = Axis_HumanUri::getInstance();
        $filters    = $this->_getActiveFilters();
        return array(
            serialize($filters)
        );
    }
}
