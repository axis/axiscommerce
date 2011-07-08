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
class Axis_Admin_ModuleController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Modules');
        $this->render();
    }

    public function getListAction()
    {
        $this->_helper->layout->disableLayout();

        $result = array();
        $i = 0;

        $modelModule = Axis::model('core/module');
        $codes = $modelModule->getListFromFilesystem();
        foreach ($codes as $i => $code) {
            $module = $modelModule->getByCode($code);

            $result[$i]                     = $module->toArray();
            $result[$i]['version']          = $module->getVersion();
            $result[$i]['hide_install']     = $module->isInstalled();
            $result[$i]['hide_uninstall']   = !$module->isInstalled() || !$module->hasUninstall();
            $result[$i]['hide_upgrade']     = !$module->hasUpgrade();

            $upgrades = $module->getAvailableUpgrades();
            if (count($upgrades)) {
                if ($module->hasUpgrade()) {
                    $result[$i]['upgrade_tooltip'] = Axis::translate('admin')->__(
                        'Apply upgrades %s', implode(', ', $upgrades)
                    );
                }
                if (!$module->isInstalled()) {
                    $upgrade = $upgrades[count($upgrades) - 1];
                    $result[$i]['install_tooltip'] = Axis::translate('admin')->__(
                        'Install %s', $upgrade
                    );
                }
            }
        }

        // apply grid filter
        $result = $this->_filter($result);
        $count = count($result);

        if ($count) {
            // apply sorting
            usort($result, array($this, '_cmp'));
            // apply pagination
            $limit = $this->_getParam('limit', 25);
            $start = $this->_getParam('start', 0);
            $result = array_chunk($result, $limit);
            $result = $result[$start/$limit];
        }

        return $this->_helper->json->sendSuccess(array(
            'data'  => $result,
            'count' => $count
        ));
    }

    public function installAction()
    {
        $this->_helper->layout->disableLayout();

        $mModule = Axis::model('core/module');
        if (!$this->_hasParam('code')) {
            foreach ($mModule->getListFromFilesystem() as $code) {
                $module = $mModule->getByCode($code);
                $module->install();
            }
        } else {
            $module = $mModule->getByCode($this->_getParam('code'));
            $module->install();
        }
        return $this->_helper->json->sendSuccess();
    }

    public function uninstallAction()
    {
        $this->_helper->layout->disableLayout();

        $module = Axis::single('core/module')->getByCode($this->_getParam('code'));
        $module->uninstall();
        return $this->_helper->json->sendSuccess();
    }

    public function upgradeAction()
    {
        $this->_helper->layout->disableLayout();

        $mModule = Axis::model('core/module');
        if (!$this->_hasParam('code')) {
            foreach ($mModule->fetchAll() as $module) {
                $module->upgradeAll();
            }
        } else {
            $module = $mModule->getByCode($this->_getParam('code'));
            $module->upgradeAll();
        }
        return $this->_helper->json->sendSuccess();
    }

    protected function _filter(array &$array = array())
    {
        foreach ($this->_getParam('filter', array()) as $filter) {
            foreach ($array as $i => $row) {
                if ('version' == $filter['field']) {
                    $v1 = $filter['operator'] == '>=' ? $filter['value'] : $row['version'];
                    $v2 = $filter['operator'] == '<=' ? $filter['value'] : $row['version'];
                    if (1 === version_compare($v1, $v2)) {
                        unset($array[$i]);
                    }
                } else {
                    if (false === stripos($row[$filter['field']], $filter['value'])) { // LIKE compare
                        unset($array[$i]);
                    }
                }
            }
        }
        return array_values($array);
    }

    protected function _cmp($a, $b)
    {
        $field  = $this->_getParam('sort', 'name');
        $dir    = $this->_getParam('dir', 'ASC');

        if ('version' === $field) {
            $result = version_compare($a['version'], $b['version']);
        } else {
            $result = strcmp($a[$field], $b[$field]);
        }

        if ('DESC' === $dir && 0 != $result) {
            return $result == -1 ? 1 : -1;
        }
        return $result;
    }
}