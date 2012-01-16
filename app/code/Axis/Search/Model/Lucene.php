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
class Axis_Search_Model_Lucene
{
    /**
     *
     * @var Zend_Search_Lucene
     */
    protected $_index;

    /**
     *
     * @var string
     */
    protected $_encoding = 'utf-8';

    /**
     * Construct, create index
     *
     * @param string $indexPath[optional]
     * @param string $encoding[optional]
     * @throws Axis_Exception
     */
    public function __construct(array $params)
    {
        $encoding = $this->_encoding;
        $indexPath = array_shift($params);
        if (count($params)) {
            $encoding = array_shift($params);
        }
        if (null === $indexPath) {
            $site = Axis::getSite()->id;
            $locale = Axis::single('locale/language')
                ->find(Axis_Locale::getLanguageId())
                ->current()
                ->locale;

            $indexPath = Axis::config()->system->path
                . '/var/index/'
                . $site . '/'
                . $locale;
        }

        if (!is_readable($indexPath)) {
            throw new Axis_Exception(
                Axis::translate('search')->__(
                    'Please, update search indexes, to enable search functionality'
            ));
        }

        /*
        $mySimilarity = new Axis_Similarity();
        Zend_Search_Lucene_Search_Similarity::setDefault($mySimilarity);
        */

        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding($encoding);

        // add filter by words
        $stopWords = array('a', 'an', 'at', 'the', 'and', 'or', 'is', 'am');
        $stopWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords($stopWords);

        $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive();
        $analyzer->addFilter($stopWordsFilter);

        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);

        $this->_index = Zend_Search_Lucene::open($indexPath);
        $this->_encoding = $encoding;
    }

    /**
     * @return Zend_Search_Lucene
     */
    public function index()
    {
        return $this->_index;
    }

    /**
     *
     * @return Zend_Search_Lucene_Search_Query $query
     * @param string $queryString
     */
    public function createFuzzyQuery($queryString)
    {
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding($this->_encoding);
        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse(
            $queryString, $this->_encoding
        );

        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->addSubquery($userQuery);

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize(
            $queryString, $this->_encoding
        );
        if (2 > count($tokens)) {
            $term = new Zend_Search_Lucene_Index_Term($queryString, 'name');
            $fuzzy = new Zend_Search_Lucene_Search_Query_Fuzzy($term, 0.4);
            $query->addSubquery($fuzzy);
        }
        return $query;
    }

    /**
     *
     * @return array
     * @param string | Zend_Search_Lucene_Search_Query_Boolean $fuzzyQuery
     */
    public function findFuzzy($fuzzyQuery)
    {
        if (!is_string($fuzzyQuery)) {
            $fuzzyQuery = $fuzzyQuery->__toString();
        }
        $result = array();
        foreach ($this->_index->find($fuzzyQuery) as $hit) {
            $result[] = $hit->id;
        }
        return $result;
    }

    /**
     *
     * @param int $id
     * @param Zend_Search_Lucene_Search_Query $query
     * @return array
     */
    public function getDocumentData($id, $query = null)
    {
        $highlighter = Axis::single('search/highlighter_default');

        $doc = $this->_index->getDocument($id);

        $result = array(
            'type' => $doc->type,
            //@todo remove "@" http://framework.zend.com/issues/browse/ZF-6574
            'nameHighlight' =>  null === $query ? $doc->name :
                @$query->htmlFragmentHighlightMatches($doc->name, $this->_encoding, $highlighter),
            'name' =>  $doc->name,
            'contents' => null === $query ? $doc->contents :
                @$query->htmlFragmentHighlightMatches($doc->contents, $this->_encoding, $highlighter),
            'urlHighlight'  => null === $query ? $doc->url :
                @$query->htmlFragmentHighlightMatches($doc->url, $this->_encoding, $highlighter),
            'url'  => $doc->url
        );
        if (in_array($doc->type, array('product'))) {
            $result['image'] = $doc->image;
            $result['image_title'] = $doc->image_title;
        }
        return $result;
    }
}