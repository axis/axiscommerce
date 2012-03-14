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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Box_Cloud extends Axis_Core_Box_Abstract
{
    protected $_title = 'Tags';
    protected $_class = 'box-tag';
    protected $_url = 'tag';

    private $_countTags = 30;
    private $_lastCount = null;

    protected function _construct()
    {
        $this->setData('cache_lifetime', 86400);
    }

    protected function _getTagsCount()
    {
        if ($this->hasData('count')) {
            $this->_countTags = $this->getData('count');
        }
        return $this->_countTags;
    }

    protected function _beforeRender()
    {
        $count = $this->_getTagsCount();

        if ($this->hasTags()) {
            if ($count === $this->_lastCount) {
                return true;
            }
            if ($count < $this->_lastCount) {
                $this->_lastCount = $count;
                $this->tags = array_slice($this->tags, 0, $count);
                return true;
            }
        }
        $this->_lastCount = $count;
        $tags = Axis::single('tag/customer')->getAllWithWeight($count);
        if (!count($tags)) {
            return false;
        }
        $this->tags = $tags;
    }

    protected function _getCacheKeyInfo()
    {
        return array(
            $this->_getTagsCount()
        );
    }
}
