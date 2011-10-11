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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Module_Row extends Axis_Db_Table_Row
{
    protected $_config;

    /**
     * Return absolute path to module folder
     *
     * @return string
     */
    public function getPath()
    {
        list($namespace, $module) = explode('_', $this->code, 2);
        return Axis::config()->system->path . "/app/code/{$namespace}/{$module}";
    }

    /**
     * Retrieve the config of module
     *
     * @param string $key [optional]
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($this->_config === null) {
            $configFile = $this->getPath() . '/etc/config.php';
            require($configFile);
            $this->_config = current($config);
        }
        if ($key !== null) {
            return isset($this->_config[$key]) ? $this->_config[$key] : null;
        }
        return $this->_config;
    }

    public function getVersion()
    {
        $version1 = $this->version;
        $version2 = $this->getConfig('version');
        return ($this->compareByVersion($version1, $version2)) ? $version1 : $version2;
    }

    /**
     * Check, is module has install file
     *
     * @return bool
     */
    public function hasInstall()
    {
        return count($this->getAllUpgrades()) > 0;
    }

    /**
     * Checks, is module is installed.
     * Made a query to DB everytime when calling.
     * Used during module installation
     *
     * @return bool
     */
    public function isInstalled()
    {
        return $this->id;
    }

    /**
     * Check, is module has uninstall file
     *
     * @return bool
     */
    public function hasUninstall()
    {
        return $this->hasInstall() && !$this->getConfig('required');
    }

    /**
     * Check, is module has upgrade from current version
     *
     * @return bool
     */
    public function hasUpgrade()
    {
        if (!$this->isInstalled()) {
            return false;
        }

        $upgrades = $this->getAllUpgrades();
        foreach ($upgrades as $upgrade) {
            if (!$this->isUpgradeApplied($upgrade)) {
                return true;
            }
        }
        return false;
    }

    public function isUpgradeApplied($version)
    {
        if (!$this->isInstalled()) {
            return false;
        }
        $migrations = $this->findDependentRowset('Axis_Core_Model_Module_Upgrade');
        foreach ($migrations as $migration) {
            if ($migration->version == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     * This function was copied from php.net site
     */
    public function compareByVersion($a, $b)
    {
        $a = explode(".", rtrim($a, ".0")); //Split version into pieces and remove trailing .0
        $b = explode(".", rtrim($b, ".0")); //Split version into pieces and remove trailing .0
        foreach ($a as $depth => $aVal) { //Iterate over each piece of A
            if (isset($b[$depth])) { //If B matches A to this depth, compare the values
                if ($aVal > $b[$depth]) return 1; //Return A > B
                else if ($aVal < $b[$depth]) return -1; //Return B > A
                //An equal result is inconclusive at this point
            }
            else { //If B does not match A to this depth, then A comes after B in sort order
                return 1; //so return A > B
            }
        }
        //At this point, we know that to the depth that A and B extend to, they are equivalent.
        //Either the loop ended because A is shorter than B, or both are equal.
        return (count($a) < count($b)) ? -1 : 0;
    }

    /**
     * Retrieve the array of available upgrades
     *
     * @return array
     */
    public function getAllUpgrades()
    {
        $sqlPath = $this->getPath() . '/sql/';

        try {
            $sqlDir = new DirectoryIterator($sqlPath);
        } catch (Exception $e) {
            return array();
        }

        $upgrades = array();
        foreach ($sqlDir as $sqlFile) {
            $sqlFile = $sqlFile->getFilename();
            if (false === strstr($sqlFile, '.php') ||
                strpos($sqlFile, 'upgrade') !== false ||
                strpos($sqlFile, 'install') !== false ||
                strpos($sqlFile, 'uninstall') !== false) {
                continue;
            }
            $upgrades[] = substr($sqlFile, 0, -4);
        }
        usort($upgrades, array($this, 'compareByVersion'));
        return $upgrades;
    }

    public function getAvailableUpgrades()
    {
        $upgrades = array();
        foreach ($this->getAllUpgrades() as $upgrade) {
            if ($this->isUpgradeApplied($upgrade)) {
                continue;
            }
            $upgrades[] = $upgrade;
        }
        return $upgrades;
    }

    private function _getUpgradeClassName($version)
    {
        $version = ucwords(preg_replace("/\W+/", " ", $version));
        $version = str_replace(' ', '_', $version);
        return $this->code . '_Upgrade_' . $version;
    }

    public function getUpgradeObject($version)
    {
        require_once $this->getPath() . "/sql/{$version}.php";
        $className = $this->_getUpgradeClassName($version);
        return new $className();
    }

    public function getLastUpgrade()
    {
        if (!$this->isInstalled()) {
            return false;
        }
        return Axis::model('core/module_upgrade')->select()
            ->where('module_id = ?', $this->id)
            ->order('id desc')
            ->fetchRow();
    }

    public function install()
    {
        return $this->upgradeAll();
    }

    public function uninstall()
    {
        $this->downgradeAll();
    }

    public function upgradeAll()
    {
        if (!$this->isInstalled()) {
            $this->installDependencies();
        }
        $upgrades = $this->getAllUpgrades();
        foreach ($upgrades as $upgrade) {
            if (!$this->isUpgradeApplied($upgrade)) {
                $upgrade = $this->getUpgradeObject($upgrade);
                $this->upgrade($upgrade);
            }
        }
    }

    public function downgradeAll()
    {
        while (($upgradeRow = $this->getLastUpgrade())) {
            $upgrade = $this->getUpgradeObject($upgradeRow->version);
            $this->downgrade($upgrade);
        }
    }

    public function installDependencies()
    {
        // install dependecies
        $config = $this->getConfig();
        if (!isset($config['depends'])) {
            return true;
        }
        foreach ($config['depends'] as $code => $version) {
            $module = $this->getTable()->getByCode($code);
            if (!$module) {
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        "Module '%s' is required by '%s', but can't be installed",
                        $code, $this->code
                ));
            }

            $module->upgradeAll();
            if ($this->compareByVersion($module->version, $version) < 0) {
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        "Module '%s', (version %s) is required by '%s', but can't be upgraded",
                        $code, $version, $this->code
                ));
            }
        }
    }

    /**
     * Apply upgrade
     * @param Axis_Core_Model_Migration_Abstract $upgrade
     */
    public function upgrade($upgrade)
    {
        $installer = Axis::single('install/installer');
        $installer->startSetup();
        $upgrade->up();
        $installer->endSetup();

        if (!$this->isInstalled()) {
            // save row
            $this->is_active = 1;
            $this->save();
        }

        // add upgrade record
        $data = array(
            'module_id' => $this->id,
            'version' => $upgrade->getVersion()
        );
        $row = Axis::model('core/module_upgrade')->createRow($data);
        $row->save();

        $updateVersion = $this->compareByVersion($upgrade->getVersion(), $this->getVersion());
        if ($updateVersion) {
            $this->version = $upgrade->getVersion();
            $this->save();
        }
    }

    /**
     * Uninstall upgrade
     * @param Axis_Core_Model_Migration_Abstract $upgrade
     */
    public function downgrade($upgrade)
    {
        if (!$this->isInstalled()) {
            return false;
        }
        $installer = Axis::single('install/installer');
        $installer->startSetup();
        $upgrade->down();
        $installer->endSetup();

        // delete upgrade record
        $upgradeModel = Axis::model('core/module_upgrade');
        $where = array(
            'module_id = ?' => $this->id,
            'version = ?' => $upgrade->getVersion()
        );
        $upgradeModel->delete($where);

        $lastMigration = $this->getLastUpgrade();
        if ($lastMigration) {
            $this->version = $lastMigration->version;
            $this->save();
        } else {
            $this->delete();
        }
    }
}