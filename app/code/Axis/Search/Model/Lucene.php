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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
//@todo extends axis_collection (and split)
class Axis_Search_Model_Lucene implements IteratorAggregate, Countable
{
    /**
     *
     * @var string
     */
    protected $_path;

    /**
     *
     * @var Zend_Search_Lucene
     */
    protected $_index;

    /**
     *
     * @var Zend_Log
     */
    protected $_log = null;

    /**
     *
     * @var Zend_Search_Lucene_Search_Query
     */
    protected $_query;

    /**
     *
     * @var boolean
     */
    protected $_isLoaded = false;

    /**
     *
     * @var array
     */
    protected $_collection = array();

    /**
     *
     * @var string
     */
    protected $_localeFilter = null;

    /**
     *
     * @var int
     */
    protected $_siteIdFilter = null;

    /**
     *
     */
    public function __construct()
    {
        /*
        $mySimilarity = new Axis_Similarity();
        Zend_Search_Lucene_Search_Similarity::setDefault($mySimilarity);
        */
        $this->setLog()
            ->setPath()
            ->setEncoding()
            ->setAnalyzer()
        ;

        if (@preg_match('/\pL/u', 'a') != 1) {
            $this->getLog()->err("PCRE unicode support is turned off.\n");
        }
    }

    /**
     *
     * @param search $path
     * @return \Axis_Search_Model_Lucene
     */
    public function setPath($path = '')
    {
        $this->_path = AXIS_ROOT . '/var/index' . $path;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     *
     * @return Zend_Search_Lucene
     * @throws Axis_Exception
     */
    public function getIndex()
    {
        if (null === $this->_index) {
            $path = $this->getPath();
            $logger = $this->getLog();
            try {
                $index = Zend_Search_Lucene::open($path);
                $logger->info("Open index: $path");
            } catch (Zend_Search_Lucene_Exception $e) {
                Axis::message()->addNotice($e->getMessage());
                $logger->info($e->getMessage());
                try {
                    $index = Zend_Search_Lucene::create($path);
                    $logger->info("Create index : $path");
                } catch(Zend_Search_Lucene_Exception $e) {
                    Axis::message()->addError($e->getMessage());
                    $logger->err("Failed opening or creating index in $path");
                    $logger->err($e->getMessage());
                }
            }
            $this->_index = $index;
        }
        return $this->_index;
    }

    /**
     *
     * @return \Axis_Search_Model_Lucene
     */
    public function removeIndex()
    {
        $path = $this->getPath();
        foreach (scandir($path) as $file) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            unlink($path . '/' . $file);
        }
        $this->getLog()->info("Remove index: $path");
        return $this;
    }

    /**
     *
     * @param string $encoding
     * @return \Axis_Search_Model_Lucene
     */
    public function setEncoding($encoding = 'utf-8')
    {
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding($encoding);
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getEncoding()
    {
        return Zend_Search_Lucene_Search_QueryParser::getDefaultEncoding();
    }

    /**
     *
     * @param Zend_Search_Lucene_Analysis_Analyzer $analyzer
     * @return \Axis_Search_Model_Lucene
     */
    public function setAnalyzer(Zend_Search_Lucene_Analysis_Analyzer $analyzer = null)
    {
        if (null === $analyzer) {

            // add filter by words
            $words = array('a', 'an', 'at', 'the', 'and', 'or', 'is', 'am');
            $filter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords($words);

            $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive();
            $analyzer->addFilter($filter);
        }
        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);

        return $this;
    }

    /**
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     *
     * @param Zend_Log $logFile
     * @return \Axis_Search_Model_Lucene
     */
    public function setLog($logFile = null)
    {
        if (empty($logFile)) {
            $stream = new Zend_Log_Writer_Mock();
        } else {
            try {
                $stream = new Zend_Log_Writer_Stream(AXIS_ROOT . $logFile);
            } catch (Zend_Log_Exception $e) {
                $stream = new Zend_Log_Writer_Mock();
            }
        }

        $this->_log = new Zend_Log($stream);
        return $this;
    }

   /**
    *
    * @param Axis_Catalog_Model_Product_Row $row
    */
    public function addProductDocument(Axis_Catalog_Model_Product_Row $row)
    {
        $colums = array('name',  'value',               'type');
        $values = array(
            array('type',        'product',             'UnIndexed'),
            array('name',        $row->name,            'Text'),
            array('contents',    $row->description,     'Text'),
            array('url',         $row->key_word,        'Text'),
            array('sku',         $row->sku,             'Text'),
            array('image',       $row->image_thumbnail, 'UnIndexed'),
            array('image_title', $row->image_title,     'UnIndexed'),
            array('site_id',     $row->site_id,         'UnIndexed'),
            array('locale',      $row->locale,          'UnIndexed'),
        );

        $fields = array();
        foreach ($values as $_values) {
            $fields[] = array_combine($colums, $_values);
        }

        $this->_addDocument($fields);
    }

    /**
     *
     * @param Axis_Cms_Model_Page_Content_Row $row
     */
    public function addPageDocument(Axis_Cms_Model_Page_Content_Row $row)
    {
        $colums = array('name',  'value',            'type');
        $values = array(
            array('type',        'page',             'UnIndexed'),
            array('name',        $row->title,        'Text'),
            array('contents',    $row->getContent(), 'Text'),
            array('url',         $row->link,         'Text'),
            array('site_id',     $row->site_id,      'UnIndexed'),
            array('locale',      $row->locale,       'UnIndexed'),
        );

        $fields = array();
        foreach ($values as $_values) {
            $fields[] = array_combine($colums, $_values);
        }

        $this->_addDocument($fields);
    }

    /**
     *
     * @param array $fields
     */
    protected function _addDocument($fields)
    {
        $document = new Zend_Search_Lucene_Document();
        $encoding = $this->getEncoding();
        foreach ($fields as $field) {
            $document->addField(Zend_Search_Lucene_Field::$field['type'](
                $field['name'], $field['value'], $encoding
            ));
        }
        $this->getIndex()->addDocument($document);
    }

    /**
     *
     * @param Zend_Search_Lucene_Search_Query $query
     * @return \Axis_Search_Model_Lucene
     */
    public function setQuery(Zend_Search_Lucene_Search_Query $query)
    {
        $this->_query = $query;
        return $this;
    }

    /**
     *
     * @return mixed Zend_Search_Lucene_Search_Query|null
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     *
     * @param string $rawQuery
     * @return \Axis_Search_Model_Lucene
     */
    public function addQuery($rawQuery)
    {
        $encoding = $this->getEncoding();
        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse(
            $rawQuery, $encoding
        );

        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->addSubquery($userQuery);

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize(
            $rawQuery, $encoding
        );
        if (2 > count($tokens)) {
            $term = new Zend_Search_Lucene_Index_Term($rawQuery, 'name');
            $fuzzy = new Zend_Search_Lucene_Search_Query_Fuzzy($term, 0.4);
            $query->addSubquery($fuzzy);
        }
        $this->setQuery($query);

        return $this;
    }

    /**
     *
     * @param int $siteId
     * @return \Axis_Search_Model_Lucene
     */
    public function addSiteFilter($siteId)
    {
        $this->_siteIdFilter = $siteId;
        return $this;
    }

    /**
     *
     * @param string $locale
     * @return \Axis_Search_Model_Lucene
     */
    public function addLocaleFilter($locale)
    {
        $this->_localeFilter = $locale;
        return $this;
    }

    /**
     *
     * @param string $text
     * @return string
     */
    protected function _highlight($text)
    {
        //@todo remove "@" http://framework.zend.com/issues/browse/ZF-6574
        $query = $this->getQuery();
        $highlighter = Axis::single('search/highlighter_default');
        $encoding = $this->getEncoding();
        return null === $query ? $text : @$query->htmlFragmentHighlightMatches(
            $text, $encoding, $highlighter
        );
    }

    protected function _loadCollection()
    {
        $query = $this->getQuery();
        $collection = array();
        $index = $this->getIndex();
        foreach ($index->find($query) as $hit) {
            $document = $index->getDocument($hit->id);
            //filters
            if ($this->_localeFilter !== $document->locale) {
                continue;
            }

            if ($this->_siteIdFilter !== $document->site_id) {
                continue;
            }

            $_row = array(
                'type'           => $document->type,
                'nameHighlight'  => $this->_highlight($document->name),
                'name'           =>  $document->name,
                'contents'       => $this->_highlight($document->contents),
                'urlHighlight'   => $this->_highlight(urlencode($document->url)),
                'url'            => urlencode($document->url)
            );
            if (in_array($document->type, array('product'))) {
                $_row['image'] = $document->image;
                $_row['image_title'] = $document->image_title;
            }

            $collection[] = $_row;
        }
        return $collection;
    }

    protected function _load()
    {
        if (!$this->_isLoaded) {
            $this->_collection = $this->_loadCollection();
            $this->_isLoaded = true;
        }
    }

    /**
     * Implementation of IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $this->_load();
        return new ArrayIterator($this->_collection);
    }

    /**
     * Retireve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->_load();
        return count($this->_collection);
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        $this->_load();
        return $this->_collection;
    }
}