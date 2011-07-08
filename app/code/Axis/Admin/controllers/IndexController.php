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
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('Axis_Core')->__('Home');
        $siteId = $this->_getParam('siteId', 0);
        $this->view->siteId = $siteId;
        $date = Axis_Date::now()
            ->setHour(0)->setMinute(0)->setSecond(0)
            ->toPhpString('Y-m-d H:i:s');

        $currency = Axis::single('locale/currency')->getCurrency(
            Axis::config()->locale->main->currency
        );

        $modelOrder = Axis::single('sales/order');

        $selectOrderTotal = $modelOrder->select('SUM(order_total)')
                ->addSiteFilter($siteId);
        $orderTotal = (float) $selectOrderTotal->fetchOne();
        $this->view->orderTotal = $currency->toCurrency($orderTotal);
        
        $count = $modelOrder->select()
            ->addSiteFilter($siteId)
            ->count();
        $this->view->orderAverage = $currency->toCurrency(
            $count ? $orderTotal/$count : 0
        );

        $this->view->orderTotalToday = $currency->toCurrency(
            (float) $selectOrderTotal->where('date_purchased_on > ? ', $date)
                ->fetchOne()
        );

        $this->view->orderCountToday = $modelOrder->select()
            ->addSiteFilter($siteId)
            ->where('date_purchased_on > ?', $date)
            ->count('DISTINCT customer_email');

        $this->view->customerCount = Axis::single('account/customer')
            ->select()
            ->addSiteFilter($siteId)
            ->count();

        $this->view->visitorToday = Axis::single('log/url')
            ->select()
            ->where('visit_at > ?', $date)
            ->addSiteFilter($siteId)
            ->count('DISTINCT visitor_id');

        $this->view->pageviewsToday = Axis::single('log/url')
            ->select()
            ->where('visit_at > ? ', $date)
            ->addSiteFilter($siteId)
            ->count();

        $this->view->sites = Axis_Collect_Site::collect();
        $this->view->date = $date;
        $this->render();
    }

    public function dashBoardChartAction()
    {
        $this->_helper->layout->disableLayout();
        $type        = $this->_getParam('type', 'order');
        $period      = $this->_getParam('period', 'day');
        $periodIndex = $this->_getParam('periodIndex', 1);
        
        $startTime = $this->_getStartDate($period, $periodIndex - 1);
        $endTime   = $this->_getEndDate($startTime, $period);
        $startDate = $startTime->toPhpString('Y-m-d H:i:s');
        $endDate   = $endTime->toPhpString('Y-m-d H:i:s');

        $select = false;
        switch (strtolower($period)) {
            case 'hour' :
                $_period = 16;
                break;
            case 'week' :
            case 'month':
                $_period = 10;
                break;
            case 'year' :
                $_period = 7;
                break;
            case 'day':
            default:
                $_period = 13;
        }
        $suffix = substr('0000-00-00 00:00:00', $_period);
        
        switch ($type) {
            case 'amount':
                $select = Axis::single('sales/order')
                    ->select(array(
                        'date_purchased_on', 
                        'order_total'
                    ))
                    ->group('date_purchased_on')
                    ->order('date_purchased_on')
                    ->where('date_purchased_on >= ?', $startDate)
                    ->where('date_purchased_on < ?', $endDate)
                    ;
                break;
            case 'visit':
                $select = Axis::single('log/url')->select(array(
                        'time' => "CONCAT(LEFT(visit_at, {$_period}), '{$suffix}')", 
                        'hit'=> 'COUNT(DISTINCT visitor_id)'
                    ))
                    ->group('time')
                    ->order('time')
                    ->where('visit_at >= ?', $startDate)
                    ->where('visit_at < ?', $endDate)
                    ;
                break;
            case 'customer':
                $select = Axis::single('account/customer')
                    ->select(array('created_at' ,'COUNT(*) as hit'))
                    ->group('created_at')
                    ->order('created_at')
                    ->where('created_at >= ?', $startDate)
                    ->where('created_at < ?', $endDate)
                    ;
                break;
            case 'rate':
                $select = Axis::single('log/url')->select(array(
                        'time' => "CONCAT(LEFT(visit_at, {$_period}), '{$suffix}')", 
                        'hit'=> 'COUNT(DISTINCT visitor_id)'
                    ))
                    ->group('time')
                    ->order('time')
                    ->where('visit_at >= ?', $startDate)
                    ->where('visit_at < ?', $endDate)
                    ;
                if ($siteId = $this->_getParam('siteId', false)) {
                    $select->where('site_id = ?', $siteId);
                }        
                $realDataVisitor = $select->fetchPairs();
                $visits = $this->_prepareDataArray(
                    $realDataVisitor, $startTime, $endTime, $period
                );
                ///////
                $select = Axis::single('sales/order')
                    ->select(array(
                        'date_purchased_on', 
                        'COUNT(DISTINCT customer_email)'
                    ))
                    ->group('date_purchased_on')
                    ->order('date_purchased_on')
                    ->where('date_purchased_on >= ?', $startDate)
                    ->where('date_purchased_on < ?', $endDate)
                ;
                if ($siteId = $this->_getParam('siteId', false)) {
                    $select->where('site_id = ?', $siteId);
                }        
                $realDataOrder = $select->fetchPairs();
                $data = $this->_prepareDataArray(
                    $realDataOrder, $startTime, $endTime, $period
                );
                
                foreach ($data as $id => &$item) {
                    if ($item['time'] !== $visits[$id]['time']) {
                        continue;
                    }
                    $item['value'] = $item['value'] && $visits[$id]['value'] ?
                        round(($item['value'] * 100) / $visits[$id]['value'], 2) : 0;
                }
                return $this->_helper->json
                    ->setData($data)
                    ->sendSuccess();
                break;
            case 'order':
            default:
                $select = Axis::single('sales/order')
                    ->select(array(
                        'date_purchased_on', 
                        'COUNT(*)'
                    ))
                    ->group('date_purchased_on')
                    ->order('date_purchased_on')
                    ->where('date_purchased_on >= ?', $startDate)
                    ->where('date_purchased_on < ?', $endDate)
                ;
        }

        if ($siteId = $this->_getParam('siteId', false)) {
            $select->where('site_id = ?', $siteId);
        }
        
        if ($select) {
            $realData = $select->fetchPairs();
        }
        $data = $this->_prepareDataArray(
            $realData, $startTime, $endTime, $period
        );
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    protected function _prepareDataArray(
        array $data, Axis_Date $start, Axis_Date $end, $type = 'day')
    {
        $result = array();
        $start = Axis_Date::timestamp($start);
        $endTimestamp = $end->getTimestamp();
        switch (strtolower($type)) {
            case 'hour' :
                $start->subMinute(1);
                while ($start->addMinute(1)->getTimestamp() < $endTimestamp) {
                    $result[$start->getTimestamp()] = 0;
                }
                break;
            case 'week' :
                $start->subDay(1);
                while ($start->addDay(1)->getTimestamp() < $endTimestamp) {
                    $result[$start->getTimestamp()] = 0;
                }
                break;
            case 'month':
                $start->subDay(1);
                while ($start->addDay(1)->getTimestamp() < $endTimestamp) {
                    $result[$start->getTimestamp()] = 0;
                }
                break;
            case 'year' :
                $start->subMonth(1);
                while ($start->addMonth(1)->getTimestamp() < $endTimestamp) {
                    $result[$start->getTimestamp()] = 0;
                }
                break;
            case 'day':
            default:
                $start->subHour(1);
                while ($start->addHour(1)->getTimestamp() < $endTimestamp) {
                    $result[$start->getTimestamp()] = 0;
                }
        }

        $timestamps = array_keys($result);
        asort($timestamps);
        foreach ($data as $time => $value) {
            $unixTime = strtotime($time);
            $id = null;
            foreach ($timestamps as $timestamp) {
                if ($timestamp > $unixTime) {
                    break;
                }
                $id = $timestamp;
            }
            if ($id && isset($result[$id])) {
                $result[$id] += $value;
            }
        }

        $date = Axis_Date::now();
        $data = array();
        foreach ($result as $time => $value) {
            $data[] = array(
                'time' => $date->setTimestamp($time)->toPhpString('Y-m-d H:i:s'),
                'value' => $value //+ rand(1, 5)
            );
        }

        return $data;
    }

    protected function _getEndDate($time, $type = 'hour')
    {
//        $t = clone $time;
        $t = Axis_Date::timestamp($time);
        switch (strtolower($type)) {
            case 'hour' :
                $t->addHour(1);
                break;
            case 'week' :
                $t->addDay(7);
                break;
            case 'month':
                $t->addMonth(1);
                break;
            case 'year' :
                $t->addYear(1);
                break;
            case 'day':
            default:
                $t->addDay(1);
        }
        return $t;
    }

    protected function _getStartDate($type, $period = 0, $time = null)
    {
        if (null === $time) {
            $time = Axis_Date::now();
        }
        $time->setMinute(0)->setSecond(0)->setMilliSecond(0);
        $period  = $period > 0 ? $period : 0;
        switch (strtolower($type)) {
            case 'hour':
                $time->subHour($period);
                break;
            case 'week':
                $time->subDay(date('w') + $period * 7)
                    ->setHour(0);
                break;
            case 'month':
                $time->subMonth($period)
                    ->setDay(1)
                    ->setHour(0);
                break;
            case 'year':
                $time->subYear($period)
                    ->setMonth(1)
                    ->setDay(1)
                    ->setHour(0);
                break;
            case 'day':
            default:
                $time->subDay($period)
                    ->setHour(0);
        }
        return $time;
    }

    public function changeSiteAction()
    {
        $siteId = $this->_getParam('siteId');
        $ctrlName = $this->_getParam('ctrlName');
        $this->_redirect("$ctrlName/index/siteId/$siteId");
    }

    public function infoAction()
    {
        $this->_helper->layout->disableLayout();
        phpinfo();
    }
}