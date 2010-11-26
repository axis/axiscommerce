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
 * @subpackage  Axis_Cms_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Category extends Axis_Db_Table 
{
    protected $_name = 'cms_category';
    protected $_rowClass = 'Axis_Cms_Model_Category_Row';
    
    public function getCategoryIdByLink($link)
    {
        return Axis::single('cms/category_content')
            ->select('cms_category_id')
            ->joinInner(
                'cms_category',
                'cc.id = ccc.cms_category_id'
            )
            ->where('ccc.link = ?', $link)
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->fetchOne();
    }
    
    public function getActiveCategory()
    {
        return $this->select(array('id', 'parent_id'))
            ->join('cms_category_content',
                  'ccc.cms_category_id = cc.id',
                  array('link', 'title')
            )->where('cc.is_active = 1')
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->where('ccc.language_id = ?', Axis_Locale::getLanguageId())
            ->where('ccc.link is not null')
            ->fetchAll();
    }

    /**
     *
     * @param int $pageId
     * @return array
     */
    public function getIdsByPage($pageId)
    {
        return Axis::single('cms/page_category')
            ->select('cms_category_id')
            ->where('cms_page_id = ? ', $pageId)
            ->fetchCol();
    }
    
    private function _recurse(&$arr, $id, &$res = array()) 
    {
        if (isset($arr[$id]) && (null !== $arr[$id])) {
            $res[$id] = $arr[$id];
        } else { 
            return $res;
        }       
        if (null !== $arr[$id]['parent_id'] ) {
            $this->_recurse($arr, $arr[$id]['parent_id'], $res);
        } 
        return $res;
    }
    
    public function getParentCategory($id, $isPage = false)
    {
        $all = $this->select(array('id', 'parent_id'))
            ->joinInner('cms_category_content',
                'ccc.cms_category_id = cc.id',
                array('link', 'title', 'meta_title', 'meta_description', 'meta_keyword'))
            ->where('cc.is_active = 1')
            ->where('cc.site_id = ?', Axis::getSiteId())
            ->where('ccc.language_id = ?', Axis_Locale::getLanguageId())
            ->where('ccc.link IS NOT NULL')
            ->fetchAssoc();
        
        if ($isPage) {
            $id = current($this->getIdsByPage($id));
        }
        $result = array();
        $this->_recurse($all, $id, $result);
        return array_reverse($result);
    }
    
    /**
     *  return parent category for some site and lang
     * 
     */
    public function getRootCategories()
    {
        return $this->select('id')
           ->joinInner('cms_category_content',
                'ccc.cms_category_id = cc.id',
                array('link', 'title', 'description')
           )
           ->where('cc.is_active = 1')
           ->where('cc.site_id = ?', Axis::getSiteId())
           ->where('ccc.language_id = ?', Axis_Locale::getLanguageId())
           ->where('cc.parent_id IS NULL')
//           ->where('ccc.link IS NOT NULL')
           ->fetchAll();
    } 
}