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
class Axis_Search_Model_Log extends Axis_Db_Table
{
    protected $_name = 'search_log';

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function logging(array $data = array())
    {
    	$pdata = array(
    	   'num_results' => $data['num_results'],
    	   'created_at'  => Axis_Date::now()->toSQLString(),
    	   'visitor_id'  => Axis::single('log/visitor')->getVisitor()->id,
    	   'customer_id' => Axis::getCustomerId(),
    	   'site_id'     => Axis::getSiteId()
    	);
    	
        $query = Axis::single('search/log_query')->getQuery($data['query']);
        $query->hit++;
        $query->save();
        $pdata['query_id'] = $query->id;
        
        /* @todo analize search query */
        return parent::insert($pdata);
    }

    /**
     *
     * @param array $params
     * @return int
     */
    public function getCount($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(
            array('ls' => $this->_prefix . 'search_log'),
            new Zend_Db_Expr('COUNT(*)')
        );
        
        if (isset($params['where'])) {
        	if (!is_array($params['where']))
                $params['where'] = array($params['where']);
            foreach ($params['where'] as $where)
                $select->where($where);
        }
        if (!isset($params['filters']))
            return $this->getAdapter()->fetchOne($select);
            
        $filterGrid = $params['filters'];
        foreach ($filterGrid as $filter) {
            switch ($filter['data']['type']) {
                case 'numeric': 
                	
                	if (($filter['field'] == 'hit')) {
	                	$select->joinLeft(
	                	    array('q' => $this->_prefix . 'search_log_query'),
		                    'ls.query_id = q.id',
		                    array()
                        );
                	    $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                                ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        $select->where("q.hit $condition ?", $filter['data']['value']);
                	}
                	
                    if (($filter['field'] == 'customer_id')) {
	                   $select->where("ls.customer_id = ? ", $filter['data']['value']);
                    }
                	break;
                case 'date':
                    $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                                ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                    $select->where("ls.$filter[field] $condition ?", $filter['data']['value']);
                    break;
                default:
                    if (($filter['field'] == 'query')) {
                            $select->joinLeft(
                                array('q' => $this->_prefix . 'search_log_query'),
                                'ls.query_id = q.id',
                                array()
                            );
                            $select->where("q.query LIKE ?", $filter['data']['value'] . "%");
                    }
                    elseif(($filter['field'] == 'customer_email')) {
                            $select->joinLeft(array('c' => $this->_prefix . 'account_customer'),
		                                             'ls.customer_id = c.id',
		                                             array());
                            $select->where("c.email LIKE ?", $filter['data']['value'] . "%");
                    }
                    else {
                        $select->where("ls.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                    }
                    break;
            }
        }
        return $this->getAdapter()->fetchOne($select);
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function getList($params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('ls' => $this->_prefix . 'search_log'));
        if (isset($params['getCustomerEmail'])) {
            $select->joinLeft(array('c' => $this->_prefix . 'account_customer'),
				            'ls.customer_id = c.id',
				            array('customer_email' => 'email'));
            
        }
        if (isset($params['getQuery']))
                $select->joinLeft(array('q' => $this->_prefix . 'search_log_query'),
                                  'ls.query_id = q.id',
                                  array('query', 'hit'));
        if (isset($params['customerId'])) {
            $select->where('ls.customer_id = ?', $params['customerId']);
        }
        
        if (!empty($params['limit']))
            $select->limit($params['limit'], $params['start']);
        if (!empty($params['sort']))
            $select->order($params['sort'] . ' ' . $params['dir']);
        
            
        //@todo Axis_Db_Table_Filter::set($select, $params['filters']);
        if (isset($params['filters'])) {
            $filterGrid = $params['filters'];
            foreach ($filterGrid as $filter) {
                switch ($filter['data']['type']) {
                    case 'numeric':
                    	$condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                                         ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        if (($filter['field'] == 'hit')) {
                            if (!isset($params['getQuery'])) {
                                $select->joinLeft(
                                    array('q' => $this->_prefix . 'search_log_query'),
                                    'ls.query_id = q.id',
                                    'hit'
                                );
                            }
                            $select->where("q.hit $condition ? ", $filter['data']['value']);
                        }
                        elseif (($filter['field'] == 'customer_id')) {
                           
                            $select->where("ls.customer_id = ? ", $filter['data']['value']);
                        }
                        else 
                            $select->where("ls.$filter[field] $condition ? ", $filter['data']['value']);
                    	break;

                	case 'date':
                        $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                                         ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                        $select->where("ls.$filter[field] $condition ?", $filter['data']['value']);
                        break;
                    case 'list':
                        $select->where($this->getAdapter()->quoteInto("
                            ls.$filter[field] IN (?)", explode(',', $filter['data']['value'])));
                        break;
                    default:
                        if (($filter['field'] == 'query')) {
                            if (!isset($params['getQuery'])) {
                                $select->joinLeft(
                                    array('q' => $this->_prefix . 'search_log_query'),
                                    'ls.query_id = q.id',
                                    'query'
                                );
                            }
                            $select->where("q.query LIKE ?", $filter['data']['value'] . "%");
                        }elseif(($filter['field'] == 'customer_email')) {
                        	if (!isset($params['getCustomerEmail'])){
                                $select->joinLeft(array('c' => $this->_prefix . 'account_customer'),
                                    'ls.customer_id = c.id',
                                    array());
                        	 }
                            $select->where("c.email LIKE ?", $filter['data']['value'] . "%");
                        }
                        else {
                            $select->where("ls.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        }
                        break;
                }
            }
        }
        return $select->query()->fetchAll();
    }
}