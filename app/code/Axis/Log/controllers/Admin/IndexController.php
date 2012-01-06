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
 * @package     Axis_Log
 * @subpackage  Axis_Log_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Log
 * @subpackage  Axis_Log_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Log_Admin_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Pageviews');
        $this->render();
    }

    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $start  = $this->_getParam('start', 0);
        $limit  = $this->_getParam('limit', 25);
        $sort   = $this->_getParam('sort', 'id');
        $dir    = $this->_getParam('dir', 'DESC');

        $select = Axis::single('log/url_info')
            ->select(array(
                'url',
                'hit'      => 'COUNT(*)',
                'referrer' => 'GROUP_CONCAT(DISTINCT refer SEPARATOR " ")'
            ))
            ->calcFoundRows()
            ->joinLeft('log_url',
               'lu.url_id = lui.id',
               array('date' => 'LEFT(visit_at, 10)')
            )
            ->addFilters($filter)
            ->group('date')
            ->group('lui.url')
            ->where('LEFT(visit_at, 10) IS NOT NULL')
            ->limit($limit, $start)
            ->order($sort . ' ' . $dir);

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }
}
