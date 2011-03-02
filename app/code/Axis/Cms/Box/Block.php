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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Box_Block extends Axis_Core_Box_Abstract
{
    protected $_title = 'Cms block';
    protected $_class = 'box-static-block';
    protected $_disableWrapper = true;
    
    protected function _beforeRender()
    {
        if (!$this->hasStaticBlock()) {
            return false;
        }
        
        $content = Axis::single('cms/block')->getContentByName(
            $this->getStaticBlock()
        );
        
        if (!$content) {
            return false;
        }
        
        $matches = array();
        preg_match_all('/{{.+}}/U', $content, $matches);
        foreach ($matches[0] as $block) {
           $content = str_replace(
               $block, $this->_getReplaceContent($block), $content
           );
        }

        $this->setData('content', $content);
        return true;
    }

    /**
     *
     * @param string $blockName
     * @return string
     */
    private function _getReplaceContent($blockName)
    {
       $blockName = str_replace(array('{', '}'), '', $blockName);
       
       list($tagType, $tagKey) = explode('_', $blockName, 2);
       
        switch ($tagType) {
            case 'helper':
                list($helper, $params) = explode('(', $tagKey, 2);
                $params = trim(str_replace(')', '', $params), "\"'");
                if ('t' === $helper) {
                    return Axis::translate('cms')->__(
                        $params
                    );
                }
                return call_user_func(array($this->getView(), $helper), $params);
                break;
        }
       
       return '';
    }
}