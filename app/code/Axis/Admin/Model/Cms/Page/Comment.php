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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Cms_Page_Comment extends Axis_Cms_Model_Page_Comment 
{
    
    private function _setFilters($select, $filterTree)
    {
        if (sizeof($filterTree)) {
            switch ($filterTree['type']) {
                case 'node':
                    if ($filterTree['data'] == 'lost') {
                        $where = "cp.id <> ALL (
                                    SELECT DISTINCT cms_page_id
                                    FROM " . $this->_prefix . "cms_page_category 
                                 )";
                        
                        $select->where($where);
                    }
                    break;
                case 'site':
                    $select->where("cc.site_id = ?", $filterTree['data']);
                    break;
                case 'category':
                    $select->where("cptc.cms_category_id = ?", $filterTree['data']);
                    break;
                case 'page':
                    $select->where("cp.id = ?", $filterTree['data']);
                    break;            
            }
        }
        return $select; 
    }
    
    public function getCount($filterGrid, $filterTree)
    {
        $select = $this->getAdapter()->select();
        
        $select->from(array('cpc' => $this->_prefix . 'cms_page_comment'),
                      new Zend_Db_Expr('COUNT(DISTINCT(cpc.id))'))
               ->joinLeft(array('cp' => $this->_prefix . 'cms_page'),
                     ('cp.id = cpc.cms_page_id'), array())
               ->joinLeft(array('cptc' => $this->_prefix . 'cms_page_category'),
                     ('cp.id = cptc.cms_page_id'), array())
               ->joinLeft(array('cc' => $this->_prefix . 'cms_category'),
                     ('cptc.cms_category_id = cc.id'), array());
        
        //filters from tree
        $this->_setFilters($select, $filterTree);

        //filters from grid
        if (sizeof($filterGrid)) {
            foreach ($filterGrid as $filter) {
                switch ($filter['data']['type']) {
                    case 'numeric': case 'date':
                        $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                                    ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        $select->where("cpc.$filter[field] $condition ?", $filter['data']['value']);
                        break;
                    case 'list':
                        $select->where($this->getAdapter()->quoteInto("cpc.$filter[field] IN (?)", explode(',', $filter['data']['value'])));
                        break;
                    default:
                        if ($filter['field'] == 'page_name') {
                            $select->where("cp.name LIKE ?", $filter['data']['value'] . "%");
                        } else if ($filter['field'] == 'category') {
                            $select->where("cc.name LIKE ?", $filter['data']['value'] . "%");
                        } else {
                            $select->where("cpc.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        }
                        break;
                }
            }
        }
        
        return $this->getAdapter()->fetchOne($select->__toString());
    }
    
    public function getComments($filterGrid, $filterTree, $pagingParams)
    {
        $select = $this->getAdapter()->select();
        
        $select->from(array('cpc' => $this->_prefix . 'cms_page_comment'),
                      array('id', 'author', 'created_on', 'content', 'email',
                              'modified_on', 'status'))
               ->group('cpc.id')
               ->joinLeft(array('cp' => $this->_prefix . 'cms_page'),
                     ('cp.id = cpc.cms_page_id'), array('page_name' => 'name'))

               ->joinLeft(array('cptc' => $this->_prefix . 'cms_page_category'),
                     ('cp.id = cptc.cms_page_id'), array())
               ->joinLeft(array('cc' => $this->_prefix . 'cms_category'),
                     ('cptc.cms_category_id = cc.id'), 
                     array('category_name' => new Zend_Db_Expr('group_concat(`cc`.`name` separator \', \')')));
        
        //filters from tree
        $this->_setFilters($select, $filterTree);

        //filters from grid
        if (sizeof($filterGrid)) {
	        foreach ($filterGrid as $filter) {
	            switch ($filter['data']['type']) {
	                case 'numeric': case 'date':
	                    $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
	                                ($filter['data']['comparison'] == 'lt' ? '<' : '>');
	                    $select->where("cpc.$filter[field] $condition ?", $filter['data']['value']);
	                    break;
	                case 'list':
	                    $select->where($this->getAdapter()->quoteInto("cpc.$filter[field] IN (?)", explode(',', $filter['data']['value'])));
	                    break;
	                default:
	                    if ($filter['field'] == 'page_name') {
	                        $select->where("cp.name LIKE ?", $filter['data']['value'] . "%");
	                    } else if ($filter['field'] == 'category') {
	                        $select->where("cc.name LIKE ?", $filter['data']['value'] . "%");
	                    } else {
	                        $select->where("cpc.$filter[field] LIKE ?", $filter['data']['value'] . "%");
	                    }
	                    break;
	            }
	        }
        }
        
        if (!empty($pagingParams['sort'])) {
            $select->order($pagingParams['sort'] . ' ' . $pagingParams['dir']);
        }
        if (!empty($pagingParams['limit'])) {
            $select->limit($pagingParams['limit'], $pagingParams['start']);
        }
        
        return $this->getAdapter()->fetchAll($select->__toString());
    }

    /**
     * @static
     * @return const array
     */
    public static function getStatuses()
    {
        return array(
            '0' => Axis::translate('core')->__('Pending'),
            '1' => Axis::translate('core')->__('Approved'),
            '2' => Axis::translate('core')->__('Disapproved')
        );
    }
}