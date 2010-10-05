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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Box_Greeting extends Axis_Admin_Box_Abstract 
{
    protected $_title = '';
    protected $_disableWrapper = true;
    
    public function init()
    {
        $date = new Axis_Date();
        $todayDate = $date->toString();
        $date = $date->addDay(-1)->toString('YYYY-MM-dd');
        
        $mailCount = Axis::single('contacts/message')
            ->select()
            ->where('created_at > ?', $date)
            ->count();
        

        $orderTotal = Axis::single('sales/order')
            ->getTotal("date_purchased_on > '{$date}'");

        $orderTotal = Axis::single('locale/currency')
            ->getCurrency(Axis::config()->locale->main->currency)
            ->toCurrency($orderTotal ? $orderTotal : 0);

        $orderCount = Axis::single('sales/order')
            ->select()
            ->where('date_purchased_on > ?', $date )
            ->count();
        
        $userId = Zend_Auth::getInstance()->getIdentity();
        
        $this->updateData(array(
            'todayDate'  => $todayDate,
            'mailCount'  => $mailCount,
            'orderTotal' => $orderTotal,
            'orderCount' => $orderCount,
            'userInfo'   => Axis::single('admin/user')->find($userId)->current()
        ));
    }   
}