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
class Axis_Catalog_Model_Product_Manufacturer extends Axis_Db_Table
{
    protected $_name = 'catalog_product_manufacturer';

    protected $_selectClass = 'Axis_Catalog_Model_Product_Manufacturer_Select';

    protected $_dependentTables = array(
        'Axis_Catalog_Model_Product_Manufacturer_Description'
    );

    /**
     * Retrieve list of manufacturers
     * @return array
     */
    public function getList()
    {
        return $this->select('*')
            ->joinInner(
                'catalog_product_manufacturer_description',
                'cpm.id = cpmd.manufacturer_id AND language_id = :languageId',
                '*'
            )
            ->joinInner('catalog_hurl',
                "ch.key_type = 'm' AND ch.key_id = cpm.id",
                array('url' => 'key_word')
            )
            ->where('ch.site_id = ?', Axis::getSiteId())
            ->order('cpmd.title ASC')
            ->bind(array('languageId' => Axis_Locale::getLanguageId()))
            ->fetchAll();
    }

    /**
     * Update or delete manufacturer row
     * Checks is recieved url has duplicate before save.
     * If it has - throws an exception
     *
     * @param array $data
     * <pre>
     * Array(
     * 	id, name, key_word,
     *  description => array(
     *  	langId => array()
     *  )
     * )
     * </pre>
     * @return int Manufacturer id
     * @throws Axis_Exception
     */
    public function save($data)
    {
        if (!isset($data['id'])
            || !$row = $this->find($data['id'])->current()) {

            unset($data['id']);
            $row = $this->createRow();
        }

        $url = trim($data['key_word']);
        if (empty($url)) {
            $url = $data['name'];
        }
//        $url = preg_replace('/[^a-zA-Z0-9]/', '-', $url);
        if (Axis::single('catalog/hurl')->hasDuplicate(
                $url,
                array_keys(Axis_Collect_Site::collect()),
                $row->id
            )) {

            throw new Axis_Exception(
                Axis::translate('core')->__('Column %s should be unique', 'url')
            );
        }

        $data['image'] = empty($data['image']) ? '' : '/' . trim($data['image'], '/');
        $row->setFromArray($data)->save();

        // description
        if (isset($data['description'])) {
            $mManufactureDescription =  Axis::model('catalog/product_manufacturer_description');
            foreach (Axis_Collect_Language::collect() as $languageId => $languangeName) {
                $mManufactureDescription->getRow($row->id, $languageId)
                    ->setFromArray($data['description'][$languageId])
                    ->save();
            }
        }

        // url
        $mHurl = Axis::model('catalog/hurl');
        foreach (Axis_Collect_Site::collect() as $siteId => $siteName) {
            $mHurl->save(array(
                'site_id'  => $siteId,
                'key_id'   => $row->id,
                'key_type' => 'm',
                'key_word' => $url
            ));
        }

        return $row->id;
    }

    public function deleteByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $success = (bool) $this->delete(
            Axis::db()->quoteInto('id IN(?)', $ids)
        );
        if (!$success) {
            return false;
        }
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Manufacturer was deleted successfully'
            )
        );
        Axis::single('catalog/hurl')->delete(
            Axis::db()->quoteInto("key_type = 'm' AND key_id IN (?)", $ids)
        );
        return $success;
    }
}