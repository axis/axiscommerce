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
class Axis_Catalog_Model_Product_Manufacturer extends Axis_Db_Table implements Axis_Collect_Interface
{
    protected $_name = 'catalog_product_manufacturer';

    protected $_rowClass = 'Axis_Catalog_Model_Product_Manufacturer_Row';
    
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
    public function save(array $data)
    {
        $row = $this->getRow($data);

        //$row->setUrl();
        //before save
        $url = trim($data['key_word']);
        if (empty($url)) {
            $url = $data['name'];
        }
//        $url = preg_replace('/[^a-zA-Z0-9]/', '-', $url);
        if (Axis::single('catalog/hurl')->hasDuplicate(
                $url,
                array_keys(Axis_Core_Model_Site::collect()),
                (int)$row->id
            )) {

            throw new Axis_Exception(
                Axis::translate('core')->__('Column %s should be unique', 'url')
            );
        }
        $row->image = empty($row->image) ? '' : '/' . trim($row->image, '/');
        //end before save
        
        $row->save();

        //after save
        //add relation site
        $model = Axis::model('catalog/hurl');
        foreach (Axis_Core_Model_Site::collect() as $siteId => $siteName) {
            $model->save(array(
                'site_id'  => $siteId,
                'key_id'   => $row->id,
                'key_type' => 'm',
                'key_word' => $url
            ));
        }
        //end after save
        
        return $row;
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
    
    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        return Axis::single('catalog/product_manufacturer')
                ->select(array('id', 'name'))
                ->fetchPairs();
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        return Axis::single('catalog/prooduct_manufacturer')->getNameById($id);
    }
}