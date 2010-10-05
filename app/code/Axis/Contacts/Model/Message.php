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
 * @package     Axis_Contacts
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Model_Message extends Axis_Db_Table
{
    /**
     * The default table name 
     */
    protected $_name = 'contacts_message';
    protected $_selectClass = 'Axis_Contacts_Model_Message_Select';
    
    public function getList($departmentId = false, $params = array())
    {
        $select = $this->getAdapter()->select();
        $select->from(array('c' => $this->_prefix . 'contacts_message'))
            ->join(array('d' => $this->_prefix . 'contacts_department'), 
                'd.id = c.department_id',
                array('department_name' => 'name'));
        
        if (!empty($params['sort']))
            $select->order($params['sort'] . ' ' . $params['dir']);
        if (!empty($params['limit'])) {
            $select->limit($params['limit'], $params['start']);
        }
        if ($departmentId) {
            $select->where('department_id = ?', $departmentId);
        }
        if (isset($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                switch ($filter['data']['type']) {
                    case 'numeric': 
                    case 'date':
                        $condition = $filter['data']['comparison'] == 'eq' ? '=' : 
                            ($filter['data']['comparison'] == 'noteq' ? '<>' :
                            ($filter['data']['comparison'] == 'lt' ? '<' : '>'));
                        $select->where("c.{$filter['field']} $condition ?", $filter['data']['value']);
                        break;
                    case 'list':
                        $select->where($this->getAdapter()->quoteInto(
                            "c.$filter[field] IN (?)", explode(',', $filter['data']['value'])
                        ));
                        break;
                    default:
                        $select->where("c.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                        break;
                }
            }
        }
        return $select->query()->fetchAll();
    }
    
    /**
     * Adds message to database and send it to department email
     * 
     * @param string $from
     * @param string $subject
     * @param string $message
     * @param int $departmentId
     * @param string $extraInfo (fieldName: fieldValue,)
     * @return Axis_Contacts_Model_Message Provides fluent interface
     */
    public function add($from, $subject, $message, $departmentId, $extraInfo)
    {
        $this->insert(array(
            'email'         => $from,
            'subject'       => $subject,
            'message'       => $message,
            'custom_info'   => $extraInfo,
            'department_id' => $departmentId,
            'site_id'       => Axis::getSiteId(),
            'created_at'    => Axis_Date::now()->toSQLString()
        ));
        
        $department = Axis::single('contacts/department')
            ->find($departmentId)
            ->current();
        
        if ($department) {
            $to = $department->email;
        } else {
            $to = Axis_Collect_MailBoxes::getName(
                Axis::config()->contact->main->email
            );
        }
        
        $mail = new Axis_Mail();
        $mail->setConfig(array(
            'event'   => 'contact_us',
            'subject' => $subject,
            'data'    => array('text' => $message, 'custom_info' => $extraInfo),
            'to'      => $to,
            'from'    => $from 
        ));
        @$mail->send();
        
        return $this;
    }
}

