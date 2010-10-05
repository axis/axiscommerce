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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Page_Row extends Axis_Db_Table_Row 
{
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

    /**
     *
     * @return string | array
     */
    public function getContent()
    {
        $languageId = Axis_Locale::getLanguageId();
        
        $columns = array(
            'title',  'content', 'meta_keyword', 'meta_description', 'meta_title'
        );
        $content = Axis::model('cms/page_content')
            ->select($columns)
            ->joinLeft('cms_page', 'cp.id = cpc.cms_page_id', array('layout'))
            ->where('cpc.language_id = ?', $languageId)
            ->where('cpc.cms_page_id = ?', $this->id)
            ->fetchRow()
        ;
        
        //inserting blocks in content
        $matches = array();
        preg_match_all('/{{\w+}}/', $content['content'], $matches);
        $i = 0;
        
        foreach ($matches[0] as $block) {
           $content['content'] = str_replace(
               $block, $this->_getReplaceContent($block), $content['content']
           );
        }
        
        return $content;
    }
    
    public function getComments()
    {
        return Axis::model('cms/page_comment')->select()
            ->where('cpc.cms_page_id = ?', $this->id)
            ->where('cpc.status = 1') //is approved
//            ->where('cpc.status <> 2') //not blocked
            ->order('cpc.created_on DESC')
            ->fetchAll()
            ;
   }
}