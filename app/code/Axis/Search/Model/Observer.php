<?php
/**
 * Axis
 *
 * @copyright Copyright 2008-2010 Axis
 * @license GNU Public License V3.0
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
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Observer
{
    public function updateSearchIndexOnProductSave(array $data)
    {
        $product = $data['product'];
        $indexer = Axis::model('search/indexer');
        $rowset = Axis::model('catalog/product')->select(array('id', 'sku'))
            ->join('catalog_product_description', 
                'cpd.product_id = cp.id', 
                array('name', 'description')
            )->joinRight('locale_language',
                'll.id = cpd.language_id',
                'locale'
            )->joinLeft('catalog_product_image', 
                'cpi.id = cp.image_thumbnail',
                array('image_thumbnail' => 'path')
            )->joinLeft('catalog_product_image_title', 
                'cpit.image_id = cpi.id',
                array('image_title' => 'title')
            )->join('catalog_product_category', 'cp.id = cpc.product_id')
            ->join('catalog_category', 
                'cc.id = cpc.category_id', 
                'site_id'
            )->joinLeft('catalog_hurl', 
                "ch.key_id = cp.id AND ch.key_type='p'", 
                'key_word'
            )
            ->order('cc.site_id')
            ->order('cpd.language_id')
            ->group(array('cc.site_id', 'cpd.language_id', 'cp.id'))
            ->where('cp.id = ?', $product->id)
            ->fetchRowset()
            ;
        
        $index  = $path = null;
  
        foreach ($rowset as $_row) {
            $_path = $indexer->getIndexPath(
                $_row->site_id  . '/' . $_row->locale
            );
            //next index
            if ($path !== $_path) {
                //save prev index
                if ($index) {
                    $index->optimize();
                    $index->commit();
                }
                $path = $_path;
                $index = $indexer->getIndex($path);
            }
            $hits = $index->find("name:$_row->name");
//            $index->delete(reset($hits));
            foreach ($hits as $hit) {
                if ($hit->name === $_row->name) {
                    $index->delete($hit);
                }
            }
            $index->addDocument(
                $indexer->getDocument(
                    'product',
                    $_row->name,
                    $_row->description,
                    $_row->key_word,
                    $_row->image_thumbnail,
                    $_row->image_title
            ));
        }
        if ($index) {
            $index->optimize();
            $index->commit();
        }
    }
}
