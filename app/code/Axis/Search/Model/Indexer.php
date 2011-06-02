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
class Axis_Search_Model_Indexer
{
    /**
     *
     * @var string
     */
    protected $_encoding = 'utf-8';
    
    /**
     *
     * @var Zend_Log 
     */
    protected $_log = null;
    
    public function __construct() 
    {
        $this->_log()->info('Starting up');
        if (@preg_match('/\pL/u', 'a') != 1) {
            $this->_log()->err("PCRE unicode support is turned off.\n");
        }
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding(
            $this->_encoding
        );
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
        );
    }
    
    /**
     *
     * @return Zend_Log 
     */
    protected function _log() 
    {
        if (!$this->_log instanceof Zend_Log) {
            $this->_log = new Zend_Log(new Zend_Log_Writer_Stream(
                AXIS_ROOT . '/var/logs/indexing.log'
            ));
        }
        return $this->_log;
    }
    
    public function getIndexPath($path = '')
    {
        return AXIS_ROOT . '/var/index/' . $path;
    }
    
    /**
     *
     * @param string $path
     * @return Zend_Search_Lucene_Interface 
     */
    public function createIndex($path) 
    {
        try {
            $index = Zend_Search_Lucene::create($path);
            $this->_log()->info("Created new index in $path");
        } catch(Zend_Search_Lucene_Exception $e) {
            $this->_log()->err("Failed opening or creating index in $path");
            $this->_log()->err($e->getMessage());
        }
        return $index;
    }

    /**
     *
     * @param string $path
     * @return Zend_Search_Lucene_Interface 
     */
    public function getIndex($path) 
    {
        try {
            $index = Zend_Search_Lucene::open($path);
            $this->_log()->info("Opened existing index in $path");
        } catch (Zend_Search_Lucene_Exception $e) {
            $index = $this->createIndex($path);
        }
        return $index;
    }
    
    /**
     *
     * @param type $type
     * @param type $name
     * @param type $content
     * @param type $url
     * @param type $imagePath
     * @param type $imageTitle
     * @return Zend_Search_Lucene_Document 
     */
    public function getDocument(
        $type, $name, $content, $url, $imagePath = null, $imageTitle = null)
    {
        $document = new Zend_Search_Lucene_Document();
        $document->addField(Zend_Search_Lucene_Field::UnIndexed(
            'type', $type, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::Text(
            'name', $name, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::Text(
            'contents', $content, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::UnIndexed(
            'url', $url, $this->_encoding
        ));
        if (null !== $imagePath || $type == 'product') {
            $document->addField(Zend_Search_Lucene_Field::UnIndexed(
                'image', $imagePath, $this->_encoding
            ));
            $document->addField(Zend_Search_Lucene_Field::UnIndexed(
                'image_title', $imageTitle, $this->_encoding
            ));
        }
        return $document;
    }

    public function _make() 
    {
        $sites = Axis::model('core/site')->select('id')
            ->fetchCol();
        $languages = Axis::model('locale/language')->select(array('id', 'locale'))
            ->fetchPairs();
        
        $modelProduct = Axis::model('catalog/product');
        $modelCmsPageContent = Axis::model('cms/page_content');
        foreach ($sites as $_siteId) {

            foreach ($languages as $_languageId => $_locale) {
                
                $path = $this->getIndexPath(
                    $_siteId .  '/' . $_locale
                );
//                if (!is_dir($path)) {
//                    mkdir($path, 0777, true);
//                }
                $index = $this->getIndex($path);
                
                $rowset = $modelProduct->select(array('id', 'sku'))
                    ->distinct()
                    ->join('catalog_product_description', 
                        'cpd.product_id = cp.id', 
                        array('name', 'description')
                    )->joinLeft('catalog_product_image', 
                        'cpi.id = cp.image_thumbnail',
                        array('image_thumbnail' => 'path')
                    )->joinLeft('catalog_product_image_title', 
                        'cpit.image_id = cpi.id',
                        array('image_title' => 'title')
                    )->join('catalog_product_category', 'cp.id = cpc.product_id')
                    ->join('catalog_category', 'cc.id = cpc.category_id')
                    ->joinLeft('catalog_hurl', 
                        "ch.key_id = cp.id AND ch.key_type='p'", 
                        'key_word'
                    )
                    ->where('cc.site_id = ?', $_siteId)
                    ->where('cpd.language_id = ?', $_languageId)
                    ->fetchRowset()
                ;
                foreach ($rowset as $_product) {
//                    $index->find('name:')
                    $index->addDocument(
                        $this->getDocument(
                            'product',
                            $_product->name,
                            $_product->description,
                            $_product->key_word,
                            $_product->image_thumbnail,
                            $_product->image_title
                    ));

                    $this->_log()->info('Added document ' . $_product->key_word);
                }
                
                $rowset = $modelCmsPageContent->select('*')
                    ->join('cms_page_category', 'cpc2.cms_page_id = cpc.cms_page_id')
                    ->join('cms_category', 'cc.id = cpc2.cms_category_id')
                    ->where('cpc.language_id = ?', $_languageId)
                    ->where('cc.site_id = ?', $_siteId)
                    ->fetchRowset();
                
                foreach ($rowset as $_row) {
                    $index->addDocument(
                        $this->getDocument(
                            'page',
                            $_row->title,
                            $_row->getContent(),
                            $_row->link
                    ));

                    $this->_log()->info('Added document ' . $_row['link']);
                }
                
                $this->_log()->info("Optimizing index...");
                $index->optimize();
                $index->commit();
                $this->_log()->info("Done. Index now contains " . $index->numDocs() . " documents");
            }
        }
        $this->_log()->info("Index Maker shutting down");
        return true;
    } 
    
    public function removeAllIndexesFromFilesystem() 
    {
        function rrmdir($dir) {
            if (!is_dir($dir)) {
                return;
            }
            $files = scandir($dir);

            foreach ($files as $file) {
                if ("." == $file || ".." == $file) {
                    continue;
                }
                $file = $dir . '/' . $file;
                if (is_dir($file)) {
                    rrmdir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
        
        $sites = Axis::model('core/site')->select('id')->fetchCol();
        
        foreach ($sites as $siteId) {
            rrmdir($this->getIndexPath() . $siteId);
        }
    }
}