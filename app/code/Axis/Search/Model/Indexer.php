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
    public function getIndex($path) 
    {
        try {
            $index = Zend_Search_Lucene::open($path);
            $this->_log()->info("Opened existing index in $path");
        } catch (Zend_Search_Lucene_Exception $e) {
            try {
                $index = Zend_Search_Lucene::create($path);
                $this->_log()->info("Created new index in $path");
            } catch(Zend_Search_Lucene_Exception $e) {
                $this->_log()->err("Failed opening or creating index in $path");
                $this->_log()->err($e->getMessage());
            }
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
    public function getDocument(Axis_Db_Table_Row $row)
//        $type, $name, $content, $url, $imagePath = null, $imageTitle = null)
    {
        $document = new Zend_Search_Lucene_Document();
        
        if ($row instanceof Axis_Cms_Model_Page_Content_Row) {
            $type    = 'page';
            $name    = $row->title;
            $content = $row->getContent();
            $url     = $row->link;
        } else {
            $type       = 'product';
            $name       = $row->name;
            $content    = $row->description;
            $url        = $row->key_word;
            $imagePath  = $row->image_thumbnail;
            $imageTitle = $row->image_title;
            
            $document->addField(Zend_Search_Lucene_Field::UnIndexed(
                'image', $imagePath, $this->_encoding
            ));
            $document->addField(Zend_Search_Lucene_Field::UnIndexed(
                'image_title', $imageTitle, $this->_encoding
            ));
        } 
        
        $document->addField(Zend_Search_Lucene_Field::UnIndexed(
            'type', $type, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::Text(
            'name', $name, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::Text(
            'contents', $content, $this->_encoding
        ));
        $document->addField(Zend_Search_Lucene_Field::Text(
            'url', $url, $this->_encoding
        ));
        
        return $document;
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