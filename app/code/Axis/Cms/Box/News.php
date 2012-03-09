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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Box_News extends Axis_Core_Box_Abstract
{
    protected $_title = 'News';
    protected $_class = 'box-cms-news';
    protected $_disableWrapper = true;

    protected $_newsCount     = 3;
    protected $_excerptLength = 300;
    protected $_categoryName  = 'News';

    /**
     * Fetch the category and pages
     */
    protected function _beforeRender()
    {
        $languageId = Axis_Locale::getLanguageId();
        $this->category = Axis::model('cms/category')->select('*')
            ->joinLeft(
                'cms_category_content',
                'cc.id = ccc.cms_category_id',
                'link'
            )
            ->where('cc.name = ?', $this->getCategoryName())
            ->where('cc.is_active = 1')
            ->where('ccc.language_id = ?' , $languageId)
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->fetchRow();

        if (!$this->category) {
            return false;
        }

        $this->pages = Axis::model('cms/page')->select('id')
            ->distinct()
            ->joinLeft(
                'cms_page_content',
                'cpc.cms_page_id = cp.id',
                array('link', 'title', 'content')
            )
            ->joinLeft(
                array('cptc' => 'cms_page_category'),
                'cptc.cms_page_id = cp.id'
            )
            ->where('cptc.cms_category_id = ?', $this->category->id)
            ->where('cp.is_active = 1')
            ->where('cpc.language_id = ?' , $languageId)
            ->where('cpc.link IS NOT NULL')
            ->limit($this->getNewsCount())
            ->order('cp.id DESC')
            ->fetchAll();

        return $this->pages;
    }

    /**
     * Get excerpt for the content
     *
     * @return string
     */
    public function getExcerpt($text)
    {
        // insert static blocks and helpers
        $text = $this->filter($text);
        // strip tags
        $filter = new Zend_Filter_StripTags();
        $text = $filter->filter($text);

        $maxLength = $this->getExcerptLength();
        if (strlen($text) > $maxLength) {
            $i = $maxLength;
            while ($text[$i] != '.' && ($i > $maxLength - 50)) {
                $i--;
            }
            $text = substr($text, 0, $i + 1);
            if ($text[$i] != '.') {
                $text .= '...';
            }
        }

        return $text;
    }

    /**
     * Expand inline cms blocks and helpers into content.
     * {{static_about}} => content of cms_block 'about'
     *
     * @return string
     */
    public function filter($text)
    {
        //inserting blocks in content
        $matches = array();
        preg_match_all('/{{\w+}}/', $text, $matches);
        $i = 0;

        foreach ($matches[0] as $block) {
            $text = str_replace(
                $block, $this->_getReplaceContent($block), $text
            );
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        if (null === $this->category_name) {
            return $this->_categoryName;
        }
        return $this->category_name;
    }

    /**
     * @return integer
     */
    public function getNewsCount()
    {
        if (null === $this->news_count) {
            return $this->_newsCount;
        }
        return $this->news_count;
    }

    /**
     * @return integer
     */
    public function getExcerptLength()
    {
        if (null === $this->excerpt_length) {
            return $this->_excerptLength;
        }
        return $this->excerpt_length;
    }

    public function getConfigurationFields()
    {
        return array(
            'category_name' => array(
                'fieldLabel'   => Axis::translate('example_module')->__('Category Name'),
                'initialValue' => $this->_categoryName
            ),
            'news_count' => array(
                'fieldLabel'   => Axis::translate('example_module')->__('News Count'),
                'xtype'        => 'numberfield',
                'initialValue' => $this->_newsCount
            ),
            'excerpt_length' => array(
                'fieldLabel'   => Axis::translate('example_module')->__('Excerpt Length'),
                'xtype'        => 'numberfield',
                'initialValue' => $this->_excerptLength
            )
        );
    }

    private function _cleanTag($blockName)
    {
       return str_replace(array('{', '}'), '', $blockName);
    }

    private function _getReplaceContent($blockName)
    {
       $blockName = $this->_cleanTag($blockName);

       list($tagType, $tagKey) = explode('_', $blockName, 2);
       switch ($tagType) {
           case 'static':
               return Axis::single('cms/block')->getContentByName($tagKey);
               break;
       }

       return '';
    }
}