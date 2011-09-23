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
class Admin_ModuleController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Modules');
        $this->render();
    }

    public function listAction()
    {
        $data = array();
        $i = 0;

        $model = Axis::model('core/module');
        $codes = $model->getListFromFilesystem();
        foreach ($codes as $i => $code) {
            $module = $model->getByCode($code);

            $data[$i]                     = $module->toArray();
            $data[$i]['version']          = $module->getVersion();
            $data[$i]['hide_install']     = $module->isInstalled();
            $data[$i]['hide_uninstall']   = !$module->isInstalled() || !$module->hasUninstall();
            $data[$i]['hide_upgrade']     = !$module->hasUpgrade();

            $upgrades = $module->getAvailableUpgrades();
            if (count($upgrades)) {
                if ($module->hasUpgrade()) {
                    $data[$i]['upgrade_tooltip'] = Axis::translate('admin')->__(
                        'Apply upgrades %s', implode(', ', $upgrades)
                    );
                }
                if (!$module->isInstalled()) {
                    $upgrade = $upgrades[count($upgrades) - 1];
                    $data[$i]['install_tooltip'] = Axis::translate('admin')->__(
                        'Install %s', $upgrade
                    );
                }
            }
        }

        // apply grid filter
        $data = $this->_filter($data);
        $count = count($data);

        if ($count) {
            // apply sorting
            usort($data, array($this, '_cmp'));
            // apply pagination
            $limit = $this->_getParam('limit', 25);
            $start = $this->_getParam('start', 0);
            $data = array_chunk($data, $limit);
            $data = $data[$start/$limit];
        }

        return $this->_helper->json
            ->setData($data)
            ->setCount($count)
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $dataset    = Zend_Json::decode($this->_getParam('data'));
        $model      = Axis::model('core/module');

        foreach ($dataset as $code => $data) {
            $row = $model->getByCode($code);
            if (!$row->isInstalled()) {
                $row->install();
            } elseif ($row->getConfig('required')) {
                $data['is_active'] = 1;
            }
            $row->setFromArray($data);
            $row->save();
        }

        Axis_Core_Model_Cache::getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array('modules', 'config')
        );

        return $this->_helper->json->sendSuccess();
    }

    public function installAction()
    {
        $model = Axis::model('core/module');
        if (!$this->_hasParam('code')) {
            foreach ($model->getListFromFilesystem() as $code) {
                $module = $model->getByCode($code);
                $module->install();
            }
        } else {
            $module = $model->getByCode($this->_getParam('code'));
            $module->install();
        }

        Axis_Core_Model_Cache::getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array('modules', 'config')
        );

        return $this->_helper->json->sendSuccess();
    }

    public function uninstallAction()
    {
        $code = $this->_getParam('code');
        $module = Axis::single('core/module')->getByCode($code);
        $module->uninstall();

        Axis_Core_Model_Cache::getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array('modules', 'config')
        );

        return $this->_helper->json->sendSuccess();
    }

    public function upgradeAction()
    {
        $model = Axis::model('core/module');
        if (!$this->_hasParam('code')) {
            foreach ($model->fetchAll() as $module) {
                $module->upgradeAll();
            }
        } else {
            $module = $model->getByCode($this->_getParam('code'));
            $module->upgradeAll();
        }

        Axis_Core_Model_Cache::getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array('modules', 'config')
        );

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
