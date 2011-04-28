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
 * @copyright   Copyright 2008-2010 Axis
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

        $order = Axis::single('sales/order');

        $this->view->orderTotal = $currency->toCurrency(
            $order->getTotal($this->db->quoteInto('site_id = ?', $siteId))
        );

        $this->view->orderTotalToday = $currency->toCurrency(
            $order->getTotal(array(
                $this->db->quoteInto('date_purchased_on > ? ', $date),
                $this->db->quoteInto('site_id = ?', $siteId)
            ))
        );

        $this->view->orderCountToday = $order->select()
            ->where('date_purchased_on > ?', $date)
            ->addSiteFilter($siteId)
            ->count('DISTINCT customer_email');

        $count = $order->select()
            ->addSiteFilter($siteId)
            ->count();
        $this->view->orderAverage = $currency->toCurrency(
            $count ? $order->getTotal($this->db->quoteInto('site_id = ?', $siteId))/$count : 0
        );

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

        if ($siteId = $this->_getParam('siteId', false)) {
            $where['siteId'] = Axis::db()->quoteInto('site_id = ?', $siteId);
        }

        $startTime = $this->_getStartDate($period, $periodIndex - 1);
        $endTime   = $this->_getEndDate($startTime, $period);
        $startDate = $startTime->toPhpString('Y-m-d H:i:s');
        $endDate   = $endTime->toPhpString('Y-m-d H:i:s');

        switch ($type) {
            case "amount":
                $where['datagt'] = "date_purchased_on >= '{$startDate}'";
                $where['datalt'] = "date_purchased_on < '{$endDate}'";
                $realData = Axis::single('sales/order')->getAmountsList($where);
                break;
            case "visit":
                $where['datagt'] = "visit_at >= '{$startDate}'";
                $where['datalt'] = "visit_at < '{$endDate}'";
                $realData = Axis::single('log/url')->getCountList($where, $period);
                break;
            case "customer":
                $where['datagt'] = "created_at >= '{$startDate}'";
                $where['datalt'] = "created_at < '{$endDate}'";
                $realData = Axis::single('account/customer')->getCountList($where);
                break;
            case "rate":
                $where['datagt'] = "visit_at >= '{$startDate}'";
                $where['datalt'] = "visit_at < '{$endDate}'";
                $realDataVisitor = Axis::single('log/url')
                    ->getCountList($where, $period);
                $where['datagt'] = "date_purchased_on >= '{$startDate}'";
                $where['datalt'] = "date_purchased_on < '{$endDate}'";
                $realDataOrder = Axis::single('sales/order')
                    ->getCountList($where, true);

                $data = $this->_prepareDataArray(
                    $realDataOrder, $startTime, $endTime, $period
                );

                $visits = $this->_prepareDataArray(
                    $realDataVisitor, $startTime, $endTime, $period
                );

                foreach ($data as $id => &$item) {
                    if ($item['time'] !== $visits[$id]['time']) {
                        continue;
                    }
                    $item['value'] = $item['value'] && $visits[$id]['value'] ?
                        round(($item['value'] * 100) / $visits[$id]['value'], 2) : 0;
                }
                return $this->_helper->json->setData($data)->sendSuccess();
                break;
            case "order":
            default:
                $where['datagt'] = "date_purchased_on >= '{$startDate}'";
                $where['datalt'] = "date_purchased_on < '{$endDate}'";
                $realData = Axis::single('sales/order')->getCountList($where);
        }

        $data = $this->_prepareDataArray(
            $realData, $startTime, $endTime, $period
        );
        return $this->_helper->json->setData($data)->sendSuccess();
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