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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Highlighter_Default extends Zend_Search_Lucene_Search_Highlighter_Default
{
    /**
     * List of colors for text highlighting
     *
     * @var array
     */
    protected $_highlightStyle  = array('highlight2', 'highlight1');
    
    /**
     * Highlight specified words
     *
     * @param string|array $words  Words to highlight. They could be organized using the array or string.
     */
    public function highlight($words)
    {
    	$style = $this->_highlightStyle[$this->_currentColorIndex];
    	$this->_currentColorIndex = ($this->_currentColorIndex + 1) % count($this->_highlightStyle);

        $this->_doc->highlightExtended($words, array($this, 'applyColour'), array($style));
    }

    /**
     *
     * @param string $stringToHighlight
     * @param string $style
     * @return string
     */
    public function applyColour($stringToHighlight, $style)
    {
        return '<span class="highlight ' . $style . '">' . $stringToHighlight . '</span>';
    }    

}